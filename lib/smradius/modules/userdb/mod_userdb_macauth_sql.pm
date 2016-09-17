# SQL user database support for mac authentication
# Copyright (C) 2007-2016, AllWorldIT
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along
# with this program; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

package smradius::modules::userdb::mod_userdb_macauth_sql;

use strict;
use warnings;

# Modules we need
use AWITPT::Cache;
use AWITPT::DB::DBLayer;
use AWITPT::Util;
use smradius::attributes;
use smradius::constants;
use smradius::logging;
use smradius::util;

# Exporter stuff
require Exporter;
our (@ISA,@EXPORT,@EXPORT_OK);
@ISA = qw(Exporter);
@EXPORT = qw(
);
@EXPORT_OK = qw(
);



# Plugin info
our $pluginInfo = {
	Name => "SQL User Database (MAC authentication)",
	Init => \&init,

	# User database
	User_find => \&find,
	User_get => \&get,
};

# Module config
my $config;

## @internal
# Initialize module
sub init
{
	my $server = shift;
	my $scfg = $server->{'inifile'};


	# Enable support for database
	if (!$server->{'smradius'}->{'database'}->{'enabled'}) {
		$server->log(LOG_NOTICE,"[MOD_USERDB_MACAUTH_SQL] Enabling database support.");
		$server->{'smradius'}->{'database'}->{'enabled'} = 1;
	}

	# Default configs...
	$config->{'userdb_macauth_find_query'} = '
		SELECT
			user_attributes.ID,
			user_attributes.Operator, user_attributes.Disabled,
			users.Username, users.Disabled AS UserDisabled
		FROM
			@TP@user_attributes, @TP@users
		WHERE
			user_attributes.Name = "Calling-Station-Id"
			AND user_attributes.Value = %{user.MACAddress}
			AND users.ID = user_attributes.UserID
	';

	# Setup SQL queries
	if (defined($scfg->{'mod_userdb_macauth_sql'})) {
		# Pull in queries
		if (defined($scfg->{'mod_userdb_macauth_sql'}->{'userdb_macauth_find_query'}) &&
				$scfg->{'mod_userdb_macauth_sql'}->{'userdb_macauth_find_query'} ne "") {
			if (ref($scfg->{'mod_userdb_macauth_sql'}->{'userdb_macauth_find_query'}) eq "ARRAY") {
				$config->{'userdb_macauth_find_query'} = join(' ', @{$scfg->{'mod_userdb_macauth_sql'}->{'userdb_macauth_find_query'}});
			} else {
				$config->{'userdb_macauth_find_query'} = $scfg->{'mod_userdb_macauth_sql'}->{'userdb_macauth_find_query'};
			}
		}
	}

}

## @find
# Try find a user
#
# @param server Server object
# @param user SMRadius user hash
# @li Username Username of the user we want
# @param packet Radius packet
#
# @return _UserDB_Data Hash of db query, this is stored in the $user->{'_UserDB_Data'} hash item
sub find
{
	my ($server,$user,$packet) = @_;


	# Only valid for authentication
	if ($packet->code ne "Access-Request") {
		return MOD_RES_SKIP;
	}

	# Check if this could be a MAC auth attempt
	if (!($user->{'Username'} =~ /^(?:[0-9a-fA-F]{2}[:-]){5}[0-9a-fA-F]{2}$/)) {
		return MOD_RES_SKIP;
	}
	# Standardize the MAC address
	my $macAddress;
	($macAddress = $user->{'Username'}) =~ s/-/:/g;

	$server->log(LOG_DEBUG,"[MOD_USERDB_MACAUTH_SQL] Possible MAC authentication '$macAddress'");

	# Build template
	my $template;
	foreach my $attr ($packet->attributes) {
		$template->{'request'}->{$attr} = $packet->rawattr($attr)
	}
	$template->{'user'} = $user;
	$template->{'user'}->{'MACAddress'} = $macAddress;

	# Replace template entries
	my @dbDoParams = templateReplace($config->{'userdb_macauth_find_query'},$template);

	my $sth = DBSelect(@dbDoParams);
	if (!$sth) {
		$server->log(LOG_ERR,"[MOD_USERDB_MACAUTH_SQL] Failed to find data for MAC address: ".AWITPT::DB::DBLayer::Error());
		return MOD_RES_SKIP;
	}

	# Check if we got a result, if we did not NACK
	my $rows = $sth->rows();
	if ($rows < 1) {
		$server->log(LOG_DEBUG,"[MOD_USERDB_MACAUTH_SQL] MAC address '".$user->{'Username'}."' not found in database");
		return MOD_RES_SKIP;
	}

	# Grab record data
	my $macDisabled;
	while (my $row = hashifyLCtoMC($sth->fetchrow_hashref(), qw(ID Operator Username Disabled UserDisabled))) {
		# We only support ||= attributes
		if ($row->{'Operator'} ne '||=') {
			$server->log(LOG_DEBUG,"[MOD_USERDB_MACAUTH_SQL] MAC authentication only supports operator '||=', but '".
					$row->{'Operator'}."' found for user '".$row->{'Username'}."'");
		}

		# Dont use disabled user
		if (!defined($macDisabled)) {
			$macDisabled = (isBoolean($row->{'Disabled'}) || isBoolean($row->{'UserDisabled'}));
		} else {
			# If MAC is disabled, just set ... worst case it can still be disabled
			if ($macDisabled) {
				$macDisabled = (!isBoolean($row->{'Disabled'}) && !isBoolean($row->{'UserDisabled'}));
			}
		}
	}
	if (defined($macDisabled) && $macDisabled) {
		$server->log(LOG_DEBUG,"[MOD_USERDB_MACAUTH_SQL] MAC address '".$user->{'Username'}."' is disabled");
		return MOD_RES_SKIP;
	}

	DBFreeRes($sth);

	return (MOD_RES_ACK,undef);
}


## @get
# Try to get a user
#
# @param server Server object
# @param user Server $user hash
# @param packet Radius packet
#
# @return User attributes hash
# @li Attributes Radius attribute hash
# @li VAttributes Radius vendor attribute hash
sub get
{
	my ($server,$user,$packet) = @_;

	# Build template
	my $template;
	foreach my $attr ($packet->attributes) {
		$template->{'request'}->{$attr} = $packet->rawattr($attr)
	}
	# Add in userdb data
	foreach my $item (keys %{$user->{'_UserDB_Data'}}) {
		$template->{'userdb'}->{$item} = $user->{'_UserDB_Data'}->{$item};
	}

	# Attributes to return
	my %attributes = ();
	my %vattributes = ();


	my $ret;
	$ret->{'Attributes'} = \%attributes;
	$ret->{'VAttributes'} = \%vattributes;

	return $ret;
}


1;
# vim: ts=4
