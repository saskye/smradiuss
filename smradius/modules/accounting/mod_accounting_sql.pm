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
		INSERT DEFAULT
	";

	# Setup SQL queries
	if (defined($scfg->{'mod_accounting_sql'})) {
		# Pull in queries
		if (defined($scfg->{'mod_accounting_sql'}->{'accounting_start_query'}) &&
				$scfg->{'mod_accounting_sql'}->{'accounting_start_query'} ne "") {
			$config->{'accounting_start_query'} = $scfg->{'mod_accounting_sql'}->{'accounting_start_query'};
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

		use Data::Dumper;
		print(STDERR Dumper(templateReplace($config->{'accounting_start_query'},$template)));

	} elsif ($packet->attr('Acct-Status-Type') eq "Alive") {
		$server->log(LOG_DEBUG,"Alive Packet: ".$packet->dump());

	} elsif ($packet->attr('Acct-Status-Type') eq "Stop") {
		$server->log(LOG_DEBUG,"Stop Packet: ".$packet->dump());

	}


	return MOD_RES_ACK;
}


1;
