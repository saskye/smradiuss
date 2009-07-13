# Test user database
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

package smradius::smradius::modules::mod_config_test;

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
	Name => "Test Config Database",
	Init => \&init,
	
	# User database
	Config_get => \&configGet,
};



## @internal
# Initialize module
sub init
{
	my $server = shift;
}



## @configGet
# Try to get a config result
#
# @param server Server object
# @param user User
# @param packet Radius packet
#
# @return Result
sub configGet
{
	my ($server,$user,$packet) = @_;


	my $userConfig = { 
		'ConfigAttributes' => 	[
						{
							'Name' => 'SMRadius-Config-Secret',
							'Operator' => '==',
							'Value' => '12345'
						}

					]
			};


	return $userConfig;
}


1;
# vim: ts=4
