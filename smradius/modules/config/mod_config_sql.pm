# SQL config database support
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

package mod_config_sql;

use strict;
use warnings;

# Modules we need
use smradius::constants;
use smradius::logging;
use smradius::dblayer;
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
	$config->{'get_config_query'} = '
		SELECT 
			Name, Operator, Value
		FROM 
			@TP@realm_attributes 
	';
	

	# Setup SQL queries
	if (defined($scfg->{'mod_config_sql'})) {
		# Pull in queries
		if (defined($scfg->{'mod_config_sql'}->{'get_config_query'}) &&
				$scfg->{'mod_config_sql'}->{'get_config_query'} ne "") {
			if (ref($scfg->{'mod_config_sql'}->{'get_config_query'}) eq "ARRAY") {
				$config->{'get_config_query'} = join(' ',@{$scfg->{'mod_config_sql'}->{'get_config_query'}});
			} else {
				$config->{'get_config_query'} = $scfg->{'mod_config_sql'}->{'get_config_query'};
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


	# Replace template entries
	my @dbDoParams = $config->{'get_config_query'};
	# Query database
	my $sth = DBSelect(@dbDoParams);
	if (!$sth) {
		$server->log(LOG_ERR,"Failed to get config attributes: ".smradius::dblayer::Error());
		return MOD_RES_NACK;
	}
	
	# Loop with user attributes
	while (my $row = $sth->fetchrow_hashref()) {
		processConfigAttribute($server,$user->{'ConfigAttributes'},hashifyLCtoMC($row,qw(Name Operator Value)));
	}

	DBFreeRes($sth);

	return MOD_RES_ACK;
}


1;
# vim: ts=4
