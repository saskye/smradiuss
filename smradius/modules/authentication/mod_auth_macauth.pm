# MAC Authentication
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

package smradius::modules::authentication::mod_auth_macauth;

use strict;
use warnings;

# Modules we need
use smradius::attributes;
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
	Name => "MAC Authentication",
	Init => \&init,
	
	# Authentication
	Authentication_try => \&authenticate,
};



## @internal
# Initialize module
sub init
{
	my $server = shift;
}



## @authenticate
# Try authenticate user
#
# @param server Server object
# @param user User hash
# @param packet Radius packet
#
# @return Result
sub authenticate
{
	my ($server,$user,$packet) = @_;


	# This is not a MAC authentication request
	if ($user->{'_UserDB'}->{'Name'} ne "SQL User Database (MAC authentication)") {
		return MOD_RES_SKIP;
	}

	$server->log(LOG_DEBUG,"[MOD_AUTH_MACAUTH] This is a MAC authentication request");

	return MOD_RES_ACK;
}


1;
# vim: ts=4
