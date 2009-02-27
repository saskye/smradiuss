# SMRadius config information
# Copyright (C) 2008, AllWorldIT
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
