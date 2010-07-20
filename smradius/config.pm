# SMRadius config information
# Copyright (C) 2007-2010, AllWorldIT
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


## @class smradius::config
# Configuration handling class
package smradius::config;

use strict;
use warnings;

# Exporter stuff
require Exporter;
our (@ISA,@EXPORT);
@ISA = qw(Exporter);
@EXPORT = qw(
);


use smradius::logging;


# Our vars
my $config;


## @fn Init($server)
# Initialize this module with a server object
#
# @param server Server object we need to setup
sub Init
{
	my $server = shift;


	# Setup configuration
	$config = $server->{'inifile'};

	# Setup database config
	my $db;
	$db->{'DSN'} = $config->{'database'}{'dsn'};
	$db->{'Username'} = $config->{'database'}{'username'};
	$db->{'Password'} = $config->{'database'}{'password'};
	$db->{'enabled'} = 0;
	# Check we have all the config we need
	if (!defined($db->{'DSN'})) {
		$server->log(LOG_NOTICE,"smradius/config.pm: No 'DSN' defined in config file for 'database'");
	}
	$server->{'smradius'}{'database'} = $db;

	# Setup event timezone config
	if (defined($config->{'server'}{'event_timezone'})) {
		$server->{'smradius'}{'event_timezone'} = $config->{'server'}{'event_timezone'};
	} else {
		$server->{'smradius'}{'event_timezone'} = "GMT";
	}
		
	# Should we use the packet timestamp?
	if (defined($config->{'server'}{'use_packet_timestamp'})) {
		if ($config->{'server'}{'use_packet_timestamp'} =~ /^\s*(yes|true|1)\s*$/i) {
			$server->{'smradius'}{'use_packet_timestamp'} = 1;
		} elsif ($config->{'server'}{'use_packet_timestamp'} =~ /^\s*(no|false|0)\s*$/i) {
			$server->{'smradius'}{'use_packet_timestamp'} = 0;
		} else {
			$server->log(LOG_NOTICE,"smradius/config.pm: Value for 'use_packet_timestamp' is invalid");
		}
	} else {
		$server->{'smradius'}{'use_packet_timestamp'} = 0;
	}
		
	$server->log(LOG_NOTICE,"smradius/config.pm: Using ". ( $server->{'smradius'}{'use_packet_timestamp'} ? 'packet' : 'server' ) ." timestamp");
	$server->log(LOG_NOTICE,"smradius/config.pm: Using timezone '".$server->{'smradius'}{'event_timezone'}."'");
}


## @fn getConfig
# Get the config hash
#
# @return Hash ref of all our config items
sub getConfig
{
	return $config;
}



1;
# vim: ts=4
