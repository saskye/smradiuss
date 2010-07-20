# Test user database
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

package mod_userdb_test;

use strict;
use warnings;

# Modules we need
use smradius::constants;


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
	Name => "Test User Database",
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


	# Test username
	if ($user->{'Username'} eq "testuser") {
		return MOD_RES_ACK;
	}	


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

	# Attributes to return
	my $attributes = { 
		'ClearPassword' => 'doap',
		'Attributes' => [
			{
				'Name' => 'Framed-IP-Address',
				'Operator' => '=',
				'Value' => '192.168.0.233'
			},
			{
				'Name' => 'Session-Timeout',
				'Operator' => '=',
				'Value' => '60'
			},
			{
				'Name' => 'NAS-Port-Type',
				'Operator' => '==',
				'Value' => 'Ethernet'
			}

		]
	};
	my %vattributes = ();

	my $ret;
	$ret->{'Attributes'} = $attributes;
	$ret->{'VAttributes'} = \%vattributes;

	return $ret;

	return $userDetails;
}


1;
# vim: ts=4
