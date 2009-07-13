# SQL user database support
# Copyright (C) 2007-2009, AllWorldIT
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

package mod_userdb_sql;

use strict;
use warnings;

# Modules we need
use smradius::constants;
use smradius::logging;
use awitpt::db::dblayer;
use smradius::util;
use smradius::attributes;

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
	Name => "SQL User Database",
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
		$server->log(LOG_NOTICE,"[MOD_USERDB_SQL] Enabling database support.");
		$server->{'smradius'}->{'database'}->{'enabled'} = 1;
	}

	# Default configs...
	$config->{'userdb_find_query'} = '
		SELECT 
			ID 
		FROM 
			@TP@users 
		WHERE 
			UserName = %{requet.User-Name}
	';
	
	$config->{'userdb_get_group_attributes_query'} = '
		SELECT 
			group_attributes.Name, group_attributes.Operator, group_attributes.Value
		FROM 
			@TP@group_attributes, @TP@users_to_groups 
		WHERE 
			users_to_groups.UserID = %{userdb.id}
			AND group_attributes.GroupID = users_to_groups.GroupID
	';
	
	$config->{'userdb_get_user_attributes_query'} = '
		SELECT 
			Name, Operator, Value
		FROM 
			@TP@user_attributes 
		WHERE 
			UserID = %{userdb.ID}
	';
	

	# Setup SQL queries
	if (defined($scfg->{'mod_userdb_sql'})) {
		# Pull in queries
		if (defined($scfg->{'mod_userdb_sql'}->{'userdb_find_query'}) &&
				$scfg->{'mod_userdb_sql'}->{'userdb_find_query'} ne "") {
			if (ref($scfg->{'mod_userdb_sql'}->{'userdb_find_query'}) eq "ARRAY") {
				$config->{'userdb_find_query'} = join(' ', @{$scfg->{'mod_userdb_sql'}->{'userdb_find_query'}});
			} else {
				$config->{'userdb_find_query'} = $scfg->{'mod_userdb_sql'}->{'userdb_find_query'};
			}
		}

		if (defined($scfg->{'mod_userdb_sql'}->{'userdb_get_group_attributes_query'}) &&
				$scfg->{'mod_userdb_sql'}->{'userdb_get_group_attributes_query'} ne "") {
			if (ref($scfg->{'mod_userdb_sql'}->{'userdb_get_group_attributes_query'}) eq "ARRAY") {
				$config->{'userdb_get_group_attributes_query'} = join(' ', 
						@{$scfg->{'mod_userdb_sql'}->{'userdb_get_group_attributes_query'}});
			} else {
				$config->{'userdb_get_group_attributes_query'} = 
						$scfg->{'mod_userdb_sql'}->{'userdb_get_group_attributes_query'};
			}
		}

		if (defined($scfg->{'mod_userdb_sql'}->{'userdb_get_user_attributes_query'}) &&
				$scfg->{'mod_userdb_sql'}->{'userdb_get_user_attributes_query'} ne "") {
			if (ref($scfg->{'mod_userdb_sql'}->{'userdb_get_user_attributes_query'}) eq "ARRAY") {
				$config->{'userdb_get_user_attributes_query'} = join(' ',
						@{$scfg->{'mod_userdb_sql'}->{'userdb_get_user_attributes_query'}});
			} else {
					$config->{'userdb_get_user_attributes_query'} = 
						$scfg->{'mod_userdb_sql'}->{'userdb_get_user_attributes_query'};
			}
		}
	}
}

## @find
# Try find a user
#
# @param server Server object
# @param user User
# @param packet Radius packet
#
# @return Result
sub find
{
	my ($server,$user,$packet) = @_;

	# Build template
	my $template;
	foreach my $attr ($packet->attributes) {
		$template->{'request'}->{$attr} = $packet->rawattr($attr)
	}
	$template->{'user'} = $user;

	# Replace template entries
	my @dbDoParams = templateReplace($config->{'userdb_find_query'},$template);

	my $sth = DBSelect(@dbDoParams);
	if (!$sth) {
		$server->log(LOG_ERR,"[MOD_USERDB_SQL] Failed to find user data: ".awitpt::db::dblayer::Error());
		return MOD_RES_SKIP;
	}

	# Check if we got a result, if we did not NACK
	my $rows = $sth->rows();
	if ($rows > 1) {
		$server->log(LOG_ERR,"[MOD_USERDB_SQL] More than one result returned for user '".$user->{'Username'}."'");
		return MOD_RES_SKIP;
	} elsif ($rows < 1) {
		$server->log(LOG_DEBUG,"[MOD_USERDB_SQL] User '".$user->{'Username'}."' not found in database");
		return MOD_RES_SKIP;
	}

	# Grab record data
	my $row = $sth->fetchrow_hashref();

	DBFreeRes($sth);

	return (MOD_RES_ACK,$row);
}


## @get
# Try to get a user
#
# @param server Server object
# @param user User
# @param packet Radius packet
#
# @return Result
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
		$template->{'userdb'}->{$item} =  $user->{'_UserDB_Data'}->{$item};
	}

	# Attributes to return
	my %attributes = ();

	# Replace template entries
	my @dbDoParams = templateReplace($config->{'userdb_get_group_attributes_query'},$template);

	# Query database
	my $sth = DBSelect(@dbDoParams);
	if (!$sth) {
		$server->log(LOG_ERR,"Failed to get group attributes: ".awitpt::db::dblayer::Error());
		return -1;
	}
	
	# Loop with group attributes
	while (my $row = $sth->fetchrow_hashref()) {
		addAttribute($server,\%attributes,hashifyLCtoMC($row,qw(Name Operator Value)));
	}

	DBFreeRes($sth);



	# Replace template entries again
	@dbDoParams = templateReplace($config->{'userdb_get_user_attributes_query'},$template);
	# Query database
	$sth = DBSelect(@dbDoParams);
	if (!$sth) {
		$server->log(LOG_ERR,"Failed to get user attributes: ".awitpt::db::dblayer::Error());
		return -1;
	}
	
	# Loop with group attributes
	while (my $row = $sth->fetchrow_hashref()) {
		addAttribute($server,\%attributes,hashifyLCtoMC($row,qw(Name Operator Value)));
	}

	DBFreeRes($sth);

	return \%attributes;
}


1;
# vim: ts=4
