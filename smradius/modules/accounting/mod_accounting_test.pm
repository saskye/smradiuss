# Test accounting database
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

package mod_accounting_test;

use strict;
use warnings;

# Modules we need
use smradius::constants;
use smradius::logging;
use smradius::util;


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
	my ($server,$user,$packet) = @_;


	my $string = "
NAS-Port-Type: %{accounting.NAS-Port-Type}
Acct-Input-String: %{accounting.Acct-Input-String}
Acct-Session-Id: %{accounting.Acct-Session-Id}
Acct-Output-Gigawords: %{accounting.Acct-Output-Gigawords}
Service-Type: %{accounting.Service-Type}
Called-Station-Id: %{accounting.Called-Station-Id}
Acct-Output-String: %{accounting.Acct-Output-String}
Acct-Authentic: %{accounting.Acct-Authentic}
Acct-Status-Type: %{accounting.Acct-Status-Type}
Acct-Output-Packets: %{accounting.Acct-Output-Packets}
NAS-IP-Address: %{accounting.NAS-IP-Address}
NAS-Port-Id: %{accounting.NAS-Port-Id}
Acct-Terminate-Cause: %{accounting.Acct-Terminate-Cause}
Acct-Session-Time: %{accounting.Acct-Session-Time}
Calling-Station-Id: %{accounting.Calling-Station-Id}
Framed-Protocol: %{accounting.Framed-Protocol}
User-Name: %{accounting.User-Name}
NAS-Identifier: %{accounting.NAS-Identifier}
Event-Timestamp: %{accounting.Event-Timestamp}
Acct-Input-Gigawords: %{accounting.Acct-Input-Gigawords}
Acct-Input-Packets: %{accounting.Acct-Input-Packets}
Framed-IP-Address: %{accounting.Framed-IP-Address}
NAS-Port: %{accounting.NAS-Port}
Acct-Delay-Time: %{accounting.Acct-Delay-Time}
";

	my $template;
	foreach my $attr ($packet->attributes) {
		$template->{'accounting'}->{$attr} = $packet->attr($attr)
	}
	$template->{'user'} = $user;

	if ($packet->attr('Acct-Status-Type') eq "Start") {
		$server->log(LOG_DEBUG,"Start Packet: ".$packet->dump());

	} elsif ($packet->attr('Acct-Status-Type') eq "Alive") {
		$server->log(LOG_DEBUG,"Alive Packet: ".$packet->dump());

	} elsif ($packet->attr('Acct-Status-Type') eq "Stop") {
		$server->log(LOG_DEBUG,"Stop Packet: ".$packet->dump());

	}


	return MOD_RES_ACK;
}


1;
# vim: ts=4
