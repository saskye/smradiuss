# SQL config database support
# Copyright (C) 2007-2011, AllWorldIT
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

package smradius::modules::system::mod_config_sql;

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
	Name => "SQL Config Database",
	Init => \&init,

	# User database
	Config_get => \&getConfig,
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
	$config->{'get_config_realm_id_query'} = '
		SELECT
			ID
		FROM
			@TP@realms
		WHERE
			Name = ?
	';

	$config->{'get_config_realm_attributes_query'} = '
		SELECT
			Name,
			Operator,
			Value
		FROM
			@TP@realm_attributes
		WHERE
			RealmID = ?
	';

	$config->{'get_config_accesslist_query'} = '
		SELECT
			@TP@clients.AccessList,
			@TP@clients.ID
		FROM
			@TP@clients,
			@TP@clients_to_realms
		WHERE
			@TP@clients.ID = @TP@clients_to_realms.ClientID
			AND @TP@clients_to_realms.RealmID = ?
	';

	$config->{'get_config_client_attributes_query'} = '
		SELECT
			Name,
			Operator,
			Value
		FROM
			@TP@client_attributes
		WHERE
			ClientID = ?
	';

	# Setup SQL queries
	if (defined($scfg->{'mod_config_sql'})) {
		# Pull in queries
		if (defined($scfg->{'mod_config_sql'}->{'get_config_realm_id_query'}) &&
				$scfg->{'mod_config_sql'}->{'get_config_realm_id_query'} ne "") {
			if (ref($scfg->{'mod_config_sql'}->{'get_config_realm_id_query'}) eq "ARRAY") {
				$config->{'get_config_realm_id_query'} = join(' ',@{$scfg->{'mod_config_sql'}->{'get_config_realm_id_query'}});
			} else {
				$config->{'get_config_realm_id_query'} = $scfg->{'mod_config_sql'}->{'get_config_realm_id_query'};
			}
		}
		if (defined($scfg->{'mod_config_sql'}->{'get_config_realm_attributes_query'}) &&
				$scfg->{'mod_config_sql'}->{'get_config_realm_attributes_query'} ne "") {
			if (ref($scfg->{'mod_config_sql'}->{'get_config_realm_attributes_query'}) eq "ARRAY") {
				$config->{'get_config_realm_attributes_query'} = join(' ',@{$scfg->{'mod_config_sql'}->{'get_config_realm_attributes_query'}});
			} else {
				$config->{'get_config_realm_attributes_query'} = $scfg->{'mod_config_sql'}->{'get_config_realm_attributes_query'};
			}
		}
		if (defined($scfg->{'mod_config_sql'}->{'get_config_accesslist_query'}) &&
				$scfg->{'mod_config_sql'}->{'get_config_accesslist_query'} ne "") {
			if (ref($scfg->{'mod_config_sql'}->{'get_config_accesslist_query'}) eq "ARRAY") {
				$config->{'get_config_accesslist_query'} = join(' ',@{$scfg->{'mod_config_sql'}->{'get_config_accesslist_query'}});
			} else {
				$config->{'get_config_accesslist_query'} = $scfg->{'mod_config_sql'}->{'get_config_accesslist_query'};
			}
		}
		if (defined($scfg->{'mod_config_sql'}->{'get_config_client_attributes_query'}) &&
				$scfg->{'mod_config_sql'}->{'get_config_client_attributes_query'} ne "") {
			if (ref($scfg->{'mod_config_sql'}->{'get_config_client_attributes_query'}) eq "ARRAY") {
				$config->{'get_config_client_attributes_query'} = join(' ',@{$scfg->{'mod_config_sql'}->{'get_config_client_attributes_query'}});
			} else {
				$config->{'get_config_client_attributes_query'} = $scfg->{'mod_config_sql'}->{'get_config_client_attributes_query'};
			}
		}
	}
}


## @getConfig
# Try to get a config
#
# @param server Server object
# @param user User
# @param packet Radius packet
#
# @return Result
sub getConfig
{
	my ($server,$user,$packet) = @_;

	# Default realm...
	my $realmName = '<DEFAULT>';
	my $realmID;

	# Get default realm ID
	my $sth = DBSelect($config->{'get_config_realm_id_query'},$realmName);
	if (!$sth) {
		$server->log(LOG_ERR,"Failed to get default realm ID: ".awitpt::db::dblayer::Error());
		return MOD_RES_NACK;
	}
	# Set realm ID
	my $row;
	if ($sth->rows == 1) {
		$row = hashifyLCtoMC($sth->fetchrow_hashref(),qw(ID));
		$realmID = $row->{'ID'};
	}
	DBFreeRes($sth);

	# Get default realm attributes
	if (defined($realmID)) {
		$sth = DBSelect($config->{'get_config_realm_attributes_query'},$realmID);
		if (!$sth) {
			$server->log(LOG_ERR,"Failed to get default realm config attributes: ".awitpt::db::dblayer::Error());
			return MOD_RES_NACK;
		}
		# Add any default realm attributes to config attributes
		while (my $row = $sth->fetchrow_hashref()) {
			processConfigAttribute($server,$user->{'ConfigAttributes'},hashifyLCtoMC($row,qw(Name Operator Value)));
		}
		DBFreeRes($sth);
	}

	# Extract realm from username
	if (defined($user->{'Username'}) && $user->{'Username'} =~ /^\S+@(\S+)$/) {
		$realmName = $1;

		$sth = DBSelect($config->{'get_config_realm_id_query'},$realmName);
		if (!$sth) {
			$server->log(LOG_ERR,"Failed to get user realm config attributes: ".awitpt::db::dblayer::Error());
			return MOD_RES_NACK;
		}
		# Fetch realm ID
		if ($sth->rows == 1) {
			$row = hashifyLCtoMC($sth->fetchrow_hashref(),qw(ID));
			$realmID = $row->{'ID'};
			DBFreeRes($sth);

			# User realm attributes
			$sth = DBSelect($config->{'get_config_realm_attributes_query'},$realmID);
			if (!$sth) {
				$server->log(LOG_ERR,"Failed to get user realm config attributes: ".awitpt::db::dblayer::Error());
				return MOD_RES_NACK;
			}
			# Add any realm attributes to config attributes
			while (my $row = $sth->fetchrow_hashref()) {
				processConfigAttribute($server,$user->{'ConfigAttributes'},hashifyLCtoMC($row,qw(Name Operator Value)));
			}
			DBFreeRes($sth);
		}
	}

	# Reject if there is no realm
	if (!defined($realmID)) {
		$server->log(LOG_DEBUG,"No realm configured, rejecting");
		return MOD_RES_NACK;
	}

	# Get client name
	my ($clientID,$res);
	$sth = DBSelect($config->{'get_config_accesslist_query'},$realmID);
	if (!$sth) {
		$server->log(LOG_ERR,"Failed to get config attributes: ".awitpt::db::dblayer::Error());
		return MOD_RES_NACK;
	}
	# Check if we know this client
	my @accessList;
	while (my $row = $sth->fetchrow_hashref()) {
		$res = hashifyLCtoMC($row,qw(AccessList ID));
		# Split off allowed sources, comma separated
		@accessList = ();
		@accessList = split(',',$res->{'AccessList'});
		# Loop with what we get and check if we have match
		foreach my $ip (@accessList) {
			if ($server->{'server'}{'peeraddr'} eq $ip) {
				$clientID = $res->{'ID'};
				last;
			}
		}
	}
	DBFreeRes($sth);
	if (!defined($clientID)) {
		$server->log(LOG_ERR,"Peer Address '".$server->{'server'}{'peeraddr'}."' not found in access list");
		return MOD_RES_NACK;
	}

	# Get client attributes
	if (defined($clientID)) {
		my $sth = DBSelect($config->{'get_config_client_attributes_query'},$clientID);
		if (!$sth) {
			$server->log(LOG_ERR,"Failed to get default config attributes: ".awitpt::db::dblayer::Error());
			return MOD_RES_NACK;
		}
		# Add to config attributes
		while (my $row = $sth->fetchrow_hashref()) {
			processConfigAttribute($server,$user->{'ConfigAttributes'},hashifyLCtoMC($row,qw(Name Operator Value)));
		}
		DBFreeRes($sth);
	}

	return MOD_RES_ACK;
}


1;
# vim: ts=4
