# SQL user database support
#
# Copyright (C) 2008-2009, AllWorldIT
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



## @internal
# Initialize module
sub init
{
	my $server = shift;
	my $config = $server->{'config'};


	# Enable support for database
	if (!$server->{'smradius'}->{'database'}->{'enable'}) {
		$server->log(LOG_NOTICE,"[MOD_USERDB_SQL] Enabling database support.");
		$server->{'smradius'}->{'database'}->{'enable'} = 1;
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


	# TODO: Query database and see if this user exists

	return MOD_RES_SKIP;
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

	my $userDetails;
	# TODO: Query user and get attributes, return in $userDetails hash

	return $userDetails;
}


1;
