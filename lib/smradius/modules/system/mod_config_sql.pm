# SQL config database support
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

package smradius::modules::system::mod_config_sql;

use strict;
use warnings;

# Modules we need
use smradius::constants;
use smradius::logging;
use AWITPT::DB::DBLayer;
use AWITPT::Cache;
use AWITPT::NetIP;
use AWITPT::Util;
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
	$server->log(LOG_DEBUG,"Processing DEFAULT realm attributes");
	my $sth = DBSelect($config->{'get_config_realm_id_query'},$realmName);
	if (!$sth) {
		$server->log(LOG_ERR,"Failed to get default realm ID: ".AWITPT::DB::DBLayer::Error());
		return MOD_RES_NACK;
	}
	# Set realm ID
	my $row;
	if ($sth->rows == 1) {
		$row = hashifyLCtoMC($sth->fetchrow_hashref(), qw(ID));
		$realmID = $row->{'ID'};
	}
	DBFreeRes($sth);

	# Get default realm attributes
	if (defined($realmID)) {
		$sth = DBSelect($config->{'get_config_realm_attributes_query'},$realmID);
		if (!$sth) {
			$server->log(LOG_ERR,"Failed to get default realm config attributes: ".AWITPT::DB::DBLayer::Error());
			return MOD_RES_NACK;
		}
		# Add any default realm attributes to config attributes
		while (my $row = $sth->fetchrow_hashref()) {
			processConfigAttribute($server,$user,hashifyLCtoMC($row, qw(Name Operator Value)));
		}
		DBFreeRes($sth);
	}

	# Extract realm from username
	if (defined($user->{'Username'}) && $user->{'Username'} =~ /^\S+@(\S+)$/) {
		$realmName = $1;

		$server->log(LOG_DEBUG,"Processing realm attributes for '$realmName'");

		$sth = DBSelect($config->{'get_config_realm_id_query'},$realmName);
		if (!$sth) {
			$server->log(LOG_ERR,"Failed to get user realm config attributes: ".AWITPT::DB::DBLayer::Error());
			return MOD_RES_NACK;
		}
		# Fetch realm ID
		if ($sth->rows == 1) {
			$row = hashifyLCtoMC($sth->fetchrow_hashref(), qw(ID));
			$realmID = $row->{'ID'};
			DBFreeRes($sth);

			# User realm attributes
			$sth = DBSelect($config->{'get_config_realm_attributes_query'},$realmID);
			if (!$sth) {
				$server->log(LOG_ERR,"Failed to get user realm config attributes: ".AWITPT::DB::DBLayer::Error());
				return MOD_RES_NACK;
			}
			# Add any realm attributes to config attributes
			while (my $row = $sth->fetchrow_hashref()) {
				processConfigAttribute($server,$user,hashifyLCtoMC($row, qw(Name Operator Value)));
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
	my $clientID;

	# Check Cache
	my $doCheck = 1;
	my ($cres,$val) = cacheGetComplexKeyPair('mod_config_sql',"access/".$server->{'server'}{'peeraddr'});
	if (defined($val)) {
		# Check if cache expired
		if ($user->{'_Internal'}->{'Timestamp-Unix'} - $val->{'timestamp'} < 60) {
			# Check if we were allowed access
			if (defined($val->{'allowed'})) {
				$clientID = $val->{'allowed'};
				$server->log(LOG_INFO,"(CACHED) Got client ID '$clientID' from cache, bypassing accesslist check");
				$doCheck = 0;
			} else {
				$server->log(LOG_INFO,"(CACHED) Peer Address '".$server->{'server'}{'peeraddr'}."' not found in access list");
			}
		}
	}
	# Do check
	if ($doCheck) {
		$server->log(LOG_DEBUG,"Processing access list for realm '$realmName'");

		$sth = DBSelect($config->{'get_config_accesslist_query'},$realmID);
		if (!$sth) {
			$server->log(LOG_ERR,"Failed to get config attributes: ".AWITPT::DB::DBLayer::Error());
			return MOD_RES_NACK;
		}

		# Grab peer address object
		my $peerAddrObj =  AWITPT::NetIP->new($server->{'server'}{'peeraddr'});
		# Check if we know this client
		my @accessList;
		while (my $row = $sth->fetchrow_hashref()) {
			my $res = hashifyLCtoMC($row, qw(AccessList ID));
			# Split off allowed sources, comma separated
			@accessList = ();
			@accessList = split(',',$res->{'AccessList'});
			# Loop with what we get and check if we have match
			foreach my $range (@accessList) {
				my $rangeObj = AWITPT::NetIP->new($range);
				# Check for match
				if ($peerAddrObj->is_within($rangeObj)) {
					$clientID = $res->{'ID'};
					$server->log(LOG_INFO,"(SETCACHE) Got client ID '$clientID' from DB");
					last;
				}
			}
		}
		DBFreeRes($sth);
		if (!defined($clientID)) {
			$server->log(LOG_NOTICE,"Peer Address '".$server->{'server'}{'peeraddr'}."' not found in access list");
			return MOD_RES_NACK;
		}
		# Setup cached data
		my %cacheData;
		$cacheData{'allowed'} = $clientID;
		$cacheData{'timestamp'} = $user->{'_Internal'}->{'Timestamp-Unix'};
		cacheStoreComplexKeyPair('mod_config_sql',"access/".$server->{'server'}{'peeraddr'},\%cacheData);
	}

	# Get client attributes
	$server->log(LOG_DEBUG,"Processing client attributes for '$clientID'");
	if (defined($clientID)) {
		my $sth = DBSelect($config->{'get_config_client_attributes_query'},$clientID);
		if (!$sth) {
			$server->log(LOG_ERR,"Failed to get default config attributes: ".AWITPT::DB::DBLayer::Error());
			return MOD_RES_NACK;
		}
		# Add to config attributes
		while (my $row = $sth->fetchrow_hashref()) {
			processConfigAttribute($server,$user,hashifyLCtoMC($row, qw(Name Operator Value)));
		}
		DBFreeRes($sth);
	}

	return MOD_RES_ACK;
}


1;
# vim: ts=4
