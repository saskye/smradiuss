# SQL accounting database
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

package mod_accounting_sql;

use strict;
use warnings;

# Modules we need
use smradius::constants;
use smradius::logging;
use smradius::util;

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
	Name => "SQL Accounting Database",
	Init => \&init,
	
	# Accounting database
	Accounting_log => \&acct_log,
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
	if (!$server->{'smradius'}->{'database'}->{'enable'}) {
		$server->log(LOG_NOTICE,"[MOD_ACCOUNTING_SQL] Enabling database support.");
		$server->{'smradius'}->{'database'}->{'enable'} = 1;
	}

	# Default configs...
	$config->{'accounting_start_query'} = "
		INSERT INTO accounting 
				(
					Username,
					ServiceType,
					FramedProtocol,
					NASPort,
					NASPortType,
					CallingStationID,
					CalledStationID,
					NASPortID,
					AcctSessionID,
					FramedIPAddress,
					AcctAuthentic,
					EventTimestamp,
					AcctStatusType,
					NASIdentifier,
					NASIPAddress,
					AcctDelayTime
				)
			VALUES
				(
					%{accounting.User-Name},
					%{accounting.Service-Type},
					%{accounting.Framed-Protocol},
					%{accounting.NAS-Port},
					%{accounting.NAS-Port-Type},
					%{accounting.Calling-Station-Id},
					%{accounting.Called-Station-Id},
					%{accounting.NAS-Port-Id},
					%{accounting.Acct-Session-Id},
					%{accounting.Framed-IP-Address},
					%{accounting.Acct-Authentic},
					%{accounting.Event-Timestamp},
					%{accounting.Acct-Status-Type},
					%{accounting.NAS-Identifier},
					%{accounting.NAS-IP-Address},
					%{accounting.Acct-Delay-Time}
				)
	";

	$config->{'accounting_update_query'} = "
		UPDATE accounting
			SET
					AcctSessionTime = %{accounting.Acct-Session-Time},
					AcctInputOctets = %{accounting.Acct-Input-Octets},
					AcctInputGigawords = %{accounting.Acct-Input-Gigawords},
					AcctInputPackets = %{accounting.Acct-Input-Packets},
					AcctOutputOctets = %{accounting.Acct-Output-Octets},
					AcctOutputGigawords = %{accounting.Acct-Output-Gigawords},
					AcctOutputPackets = %{accounting.Acct-Output-Packets},
					AcctStatusType = %{accounting.Acct-Status-Type}
			WHERE
					UserName = %{accounting.User-Name},
					AND AcctSessionID = %{accounting.Acct-Session-Id},
					AND NASIPAddress = %{accounting.NAS-IP-Address}
	";

	$config->{'accounting_stop_query'} = "
		UPDATE accounting
			SET
					AcctSessionTime = %{accounting.Acct-Session-Time},
					AcctInputOctets = %{accounting.Acct-Input-Octets},
					AcctInputGigawords = %{accounting.Acct-Input-Gigawords},
					AcctInputPackets = %{accounting.Acct-Input-Packets},
					AcctOutputOctets = %{accounting.Acct-Output-Octets},
					AcctOutputGigawords = %{accounting.Acct-Output-Gigawords},
					AcctOutputPackets = %{accounting.Acct-Output-Packets},
					AcctStatusType = %{accounting.Acct-Status-Type},
					AcctTerminateCause = %{accounting.Acct-Terminate-Cause}
			WHERE
					UserName = %{accounting.User-Name},
					AND AcctSessionID = %{accounting.Acct-Session-Id},
					AND NASIPAddress = %{accounting.NAS-IP-Address}
	";


	# Setup SQL queries
	if (defined($scfg->{'mod_accounting_sql'})) {
		# Pull in queries
		if (defined($scfg->{'mod_accounting_sql'}->{'accounting_start_query'}) &&
				$scfg->{'mod_accounting_sql'}->{'accounting_start_query'} ne "") {
			$config->{'accounting_start_query'} = $scfg->{'mod_accounting_sql'}->{'accounting_start_query'};
		}
		if (defined($scfg->{'mod_accounting_sql'}->{'accounting_update_query'}) &&
				$scfg->{'mod_accounting_sql'}->{'accounting_update_query'} ne "") {
			$config->{'accounting_update_query'} = $scfg->{'mod_accounting_sql'}->{'accounting_update_query'};
		}
		if (defined($scfg->{'mod_accounting_sql'}->{'accounting_stop_query'}) &&
				$scfg->{'mod_accounting_sql'}->{'accounting_stop_query'} ne "") {
			$config->{'accounting_stop_query'} = $scfg->{'mod_accounting_sql'}->{'accounting_stop_query'};
		}
	}
}



## @log
# Try find a user
#
# @param server Server object
# @param user User object
# @param packet Radius packet
#
# @return Result
sub acct_log
{
	my ($server,$user,$packet) = @_;


	# Build template
	my $template;
	foreach my $attr ($packet->attributes) {
		$template->{'accounting'}->{$attr} = $packet->attr($attr)
	}
	$template->{'user'} = $user;



	if ($packet->attr('Acct-Status-Type') eq "Start") {
		$server->log(LOG_DEBUG,"Start Packet: ".$packet->dump());
		print(STDERR Dumper(templateReplace($config->{'accounting_start_query'},$template)));

	} elsif ($packet->attr('Acct-Status-Type') eq "Alive") {
		$server->log(LOG_DEBUG,"Alive Packet: ".$packet->dump());
		print(STDERR Dumper(templateReplace($config->{'accounting_update_query'},$template)));

	} elsif ($packet->attr('Acct-Status-Type') eq "Stop") {
		$server->log(LOG_DEBUG,"Stop Packet: ".$packet->dump());
		print(STDERR Dumper(templateReplace($config->{'accounting_stop_query'},$template)));

	}


	return MOD_RES_ACK;
}


1;
