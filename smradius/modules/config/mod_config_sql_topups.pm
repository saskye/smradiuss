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

package mod_config_sql_topups;

use strict;
use warnings;

# Modules we need
use smradius::constants;
use smradius::logging;
use smradius::dblayer;
use smradius::util;
use smradius::attributes;
use Data::Dumper;

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
	Name => "SQL Topups Database",
	Init => \&init,
	
	# User database
	Config_get => \&getTopups
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
	$config->{'get_topups_query'} = '
		SELECT 
				@TP@topups.ValidFrom,
				@TP@topups.ValidTo,
				@TP@topups.Value
		FROM 
				@TP@topups,
				@TP@users
		WHERE
				@TP@topups.UserID = @TP@users.ID
		AND
				@TP@users.Username = ?
	';
	

	# Setup SQL queries
	if (defined($scfg->{'mod_topups_sql'})) {
		# Pull in queries
		if (defined($scfg->{'mod_topups_sql'}->{'get_config_query'}) &&
				$scfg->{'mod_topups_sql'}->{'get_config_query'} ne "") {
			if (ref($scfg->{'mod_topups_sql'}->{'get_config_query'}) eq "ARRAY") {
				$config->{'get_config_query'} = join(' ',@{$scfg->{'mod_config_sql'}->{'get_config_query'}});
			} else {
				$config->{'get_config_query'} = $scfg->{'mod_config_sql'}->{'get_config_query'};
			}
			
		}
	}
}


## @getTopups
# Try to get topup information
#
# @param server Server object
# @param user User
# @param packet Radius packet
#
# @return Result
sub getTopups
{
	my ($server,$user,$packet) = @_;

	# Set up dbDoParams
	my @dbDoParams = ($config->{'get_topups_query'},$packet->attr('User-Name'));

	# Query database
	my $sth = DBSelect(@dbDoParams);
	if (!$sth) {
		$server->log(LOG_ERR,"Failed to get topup information: ".smradius::dblayer::Error());
		return MOD_RES_NACK;
	}

	# Fetch items 
	my $topupTotal = 0;
	while (my $row = $sth->fetchrow_hashref()) {
		$topupTotal += $row->{'value'};
	}

	# Add to ConfigAttributes
	processConfigAttribute($server,$user->{'ConfigAttributes'},{ 'Name' => 'SMRadius-Capping-Traffic-Topup', 'Operator' => ':=', 'Value' => $topupTotal });

	DBFreeRes($sth);

	return MOD_RES_ACK;
}


1;
# vim: ts=4
