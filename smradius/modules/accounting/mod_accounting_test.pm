# Test accounting database
#
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

package mod_accounting_test;

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
	Name => "Test Accounting Database",
	Init => \&init,
	
	# Accounting database
	Accounting_log => \&acct_log,
};



## @internal
# Initialize module
sub init
{
	my $server = shift;
}



## @log
# Try find a user
#
# @param server Server object
# @param packet Radius packet
#
# @return Result
sub acct_log
{
	my ($server,$packet) = @_;


	$server->log(LOG_DEBUG,"Packet: ".$packet->dump());

	return MOD_RES_SKIP;
}


1;
