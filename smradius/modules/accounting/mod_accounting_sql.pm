# SQL accounting database
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

package smradius::modules::accounting::mod_accounting_sql;

use strict;
use warnings;

# Modules we need
use smradius::constants;
use awitpt::db::dblayer;
use smradius::logging;
use smradius::util;

use POSIX qw(ceil);
use DateTime;


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

	# Cleanup run by smadmin
	Cleanup => \&cleanup,

	# Accounting database
	Accounting_log => \&acct_log,
	Accounting_getUsage => \&getUsage
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
	$server->log(LOG_NOTICE,"[MOD_ACCOUNTING_SQL] Enabling database support");
	if (!$server->{'smradius'}->{'database'}->{'enabled'}) {
		$server->log(LOG_NOTICE,"[MOD_ACCOUNTING_SQL] Enabling database support.");
		$server->{'smradius'}->{'database'}->{'enabled'} = 1;
	}

	# Default configs...
	$config->{'accounting_start_query'} = '
		INSERT INTO
			@TP@accounting
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
			%{request.User-Name},
			%{request.Service-Type},
			%{request.Framed-Protocol},
			%{request.NAS-Port},
			%{request.NAS-Port-Type},
			%{request.Calling-Station-Id},
			%{request.Called-Station-Id},
			%{request.NAS-Port-Id},
			%{request.Acct-Session-Id},
			%{request.Framed-IP-Address},
			%{request.Acct-Authentic},
			%{request.Timestamp},
			%{request.Acct-Status-Type},
			%{request.NAS-Identifier},
			%{request.NAS-IP-Address},
			%{request.Acct-Delay-Time}
		)
	';

	$config->{'accounting_update_query'} = '
		UPDATE
			@TP@accounting
		SET
			AcctSessionTime = %{request.Acct-Session-Time},
			AcctInputOctets = %{request.Acct-Input-Octets},
			AcctInputGigawords = %{request.Acct-Input-Gigawords},
			AcctInputPackets = %{request.Acct-Input-Packets},
			AcctOutputOctets = %{request.Acct-Output-Octets},
			AcctOutputGigawords = %{request.Acct-Output-Gigawords},
			AcctOutputPackets = %{request.Acct-Output-Packets},
			AcctStatusType = %{request.Acct-Status-Type}
		WHERE
			Username = %{request.User-Name}
			AND AcctSessionID = %{request.Acct-Session-Id}
			AND NASIPAddress = %{request.NAS-IP-Address}
	';

	$config->{'accounting_stop_query'} = '
		UPDATE
			@TP@accounting
		SET
			AcctSessionTime = %{request.Acct-Session-Time},
			AcctInputOctets = %{request.Acct-Input-Octets},
			AcctInputGigawords = %{request.Acct-Input-Gigawords},
			AcctInputPackets = %{request.Acct-Input-Packets},
			AcctOutputOctets = %{request.Acct-Output-Octets},
			AcctOutputGigawords = %{request.Acct-Output-Gigawords},
			AcctOutputPackets = %{request.Acct-Output-Packets},
			AcctStatusType = %{request.Acct-Status-Type},
			AcctTerminateCause = %{request.Acct-Terminate-Cause}
		WHERE
			Username = %{request.User-Name}
			AND AcctSessionID = %{request.Acct-Session-Id}
			AND NASIPAddress = %{request.NAS-IP-Address}
	';

	$config->{'accounting_usage_query'} = '
		SELECT
			SUM(AcctInputOctets) AS InputOctets,
			SUM(AcctOutputOctets) AS OutputOctets,
			SUM(AcctInputGigawords) AS InputGigawords,
			SUM(AcctOutputGigawords) AS OutputGigawords,
			SUM(AcctSessionTime) AS SessionTime
		FROM
			@TP@accounting
		WHERE
			Username = %{request.User-Name}
	';

	# Setup SQL queries
	if (defined($scfg->{'mod_accounting_sql'})) {
		# Pull in queries
		if (defined($scfg->{'mod_accounting_sql'}->{'accounting_start_query'}) &&
				$scfg->{'mod_accounting_sql'}->{'accounting_start_query'} ne "") {
			if (ref($scfg->{'mod_accounting_sql'}->{'accounting_start_query'}) eq "ARRAY") {
				$config->{'accounting_start_query'} = join(' ',
						@{$scfg->{'mod_accounting_sql'}->{'accounting_start_query'}});
			} else {
				$config->{'accounting_start_query'} = $scfg->{'mod_accounting_sql'}->{'accounting_start_query'};
			}
		}
		if (defined($scfg->{'mod_accounting_sql'}->{'accounting_update_query'}) &&
				$scfg->{'mod_accounting_sql'}->{'accounting_update_query'} ne "") {
			if (ref($scfg->{'mod_accounting_sql'}->{'accounting_update_query'}) eq "ARRAY") {
				$config->{'accounting_update_query'} = join(' ',
						@{$scfg->{'mod_accounting_sql'}->{'accounting_update_query'}});
			} else {
				$config->{'accounting_update_query'} = $scfg->{'mod_accounting_sql'}->{'accounting_update_query'};
			}
		}
		if (defined($scfg->{'mod_accounting_sql'}->{'accounting_stop_query'}) &&
				$scfg->{'mod_accounting_sql'}->{'accounting_stop_query'} ne "") {
			if (ref($scfg->{'mod_accounting_sql'}->{'accounting_stop_query'}) eq "ARRAY") {
				$config->{'accounting_stop_query'} = join(' ',
						@{$scfg->{'mod_accounting_sql'}->{'accounting_stop_query'}});
			} else {
				$config->{'accounting_stop_query'} = $scfg->{'mod_accounting_sql'}->{'accounting_stop_query'};
			}
		}
		if (defined($scfg->{'mod_accounting_sql'}->{'accounting_usage_query'}) &&
				$scfg->{'mod_accounting_sql'}->{'accounting_usage_query'} ne "") {
			if (ref($scfg->{'mod_accounting_sql'}->{'accounting_usage_query'}) eq "ARRAY") {
				$config->{'accounting_usage_query'} = join(' ',
						@{$scfg->{'mod_accounting_sql'}->{'accounting_usage_query'}});
			} else {
				$config->{'accounting_usage_query'} = $scfg->{'mod_accounting_sql'}->{'accounting_usage_query'};
			}
		}
	}
}


# Function to get radius user data usage
sub getUsage
{
	my ($server,$user,$packet) = @_;

	# Build template
	my $template;
	foreach my $attr ($packet->attributes) {
		$template->{'request'}->{$attr} = $packet->rawattr($attr)
	}
	$template->{'user'} = $user;

	# Replace template entries
	my @dbDoParams = templateReplace($config->{'accounting_usage_query'},$template);

	# Fetch data
	my $sth = DBSelect(@dbDoParams);
	if (!$sth) {
		$server->log(LOG_ERR,"[MOD_ACCOUNTING_SQL] Database query failed: ".awitpt::db::dblayer::Error());
		return;
	}

	# Check rows
	if ($sth->rows != 1) {
		$server->log(LOG_ERR,"[MOD_ACCOUNTING_SQL] Database: No accounting data returned for user");
		return;
	}

	# Pull data
	my $usageData = $sth->fetchrow_hashref();

	DBFreeRes($sth);

	# FIXME, as its a custom query, check we have all the fields we need

	# Total up input
	my $totalData = 0; 
	if (defined($usageData->{'inputoctets'}) && $usageData->{'inputoctets'} > 0) {
		$totalData += $usageData->{'inputoctets'} / 1024 / 1024;
	}
	if (defined($usageData->{'inputgigawords'}) && $usageData->{'inputgigawords'} > 0) {
		$totalData += $usageData->{'inputgigawords'} * 4096;
	}
	# Add up output
	if (defined($usageData->{'outputoctets'}) && $usageData->{'outputoctets'} > 0) {
		$totalData += $usageData->{'outputoctets'} / 1024 / 1024;
	}
	if (defined($usageData->{'outputgigawords'}) && $usageData->{'outputgigawords'} > 0) {
		$totalData += $usageData->{'outputgigawords'} * 4096;
	}

	# Add up time
	my $totalTime = 0; 
	if (defined($usageData->{'sessiontime'}) && $usageData->{'sessiontime'} > 0) {
		$totalTime = $usageData->{'sessiontime'} / 60;
	}
	
	# Rounding up
	my %res;
	$res{'TotalDataUsage'} = ceil($totalData);
	$res{'TotalTimeUsage'} = ceil($totalTime);

	return \%res;
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
		$template->{'request'}->{$attr} = $packet->rawattr($attr)
	}
	# Fix event timestamp
	$template->{'request'}->{'Timestamp'} = $user->{'_Internal'}->{'Timestamp'};
	# Add user
	$template->{'user'} = $user;



	if ($packet->attr('Acct-Status-Type') eq "Start") {
		# Replace template entries
		my @dbDoParams = templateReplace($config->{'accounting_start_query'},$template);

		# Insert into database
		my $sth = DBDo(@dbDoParams);
		if (!$sth) {
			$server->log(LOG_ERR,"[MOD_ACCOUNTING_SQL] Failed to insert accounting START record: ".
					awitpt::db::dblayer::Error());
			return MOD_RES_NACK;
		}

	} elsif ($packet->attr('Acct-Status-Type') eq "Alive") {
		# Replace template entries
		my @dbDoParams = templateReplace($config->{'accounting_update_query'},$template);

		# Update database
		my $sth = DBDo(@dbDoParams);
		if (!$sth) {
			$server->log(LOG_ERR,"[MOD_ACCOUNTING_SQL] Failed to update accounting ALIVE record: ".
					awitpt::db::dblayer::Error());
			return MOD_RES_NACK;
		}

	} elsif ($packet->attr('Acct-Status-Type') eq "Stop") {
		# Replace template entries
		my @dbDoParams = templateReplace($config->{'accounting_stop_query'},$template);

		# Update database
		my $sth = DBDo(@dbDoParams);
		if (!$sth) {
			$server->log(LOG_ERR,"[MOD_ACCOUNTING_SQL] Failed to update accounting STOP record: ".awitpt::db::dblayer::Error());
			return MOD_RES_NACK;
		}
	}

	return MOD_RES_ACK;
}


# Add up totals function
sub cleanup
{
	my ($server) = @_;
	my ($prevYear,$prevMonth);

	# The datetime now..
	my $now = DateTime->now;

	# If this is a new year
	if ($now->month == 1) {
		$prevYear = $now->year - 1;
		$prevMonth = 12;
	} else {
		$prevYear = $now->year;
		$prevMonth = $now->month - 1;
	}

	# New datetime
	my $lastMonth = DateTime->new( year => $prevYear, month => $prevMonth, day => 1 );

	# Update totals for last month
	my $sth = DBSelect('
		SELECT
			Username,
			SUM(AcctSessionTime) as AcctSessionTime,
			SUM(AcctInputOctets) as AcctInputOctets,
			SUM(AcctInputGigawords) as AcctInputGigawords,
			SUM(AcctOutputOctets) as AcctOutputOctets,
			SUM(AcctOutputGigawords) as AcctOutputGigawords
		FROM
			@TP@accounting
		WHERE
			EventTimestamp > ?
		GROUP BY
			Username
		',
		$lastMonth->ymd
	);

	if (!$sth) {
		$server->log(LOG_ERR,"[MOD_ACCOUNTING_SQL] Cleanup => Failed to select accounting record: ".
				awitpt::db::dblayer::Error());
		return;
	}

	# Set blank array
	my @allRecords = ();

	my $i = 0;
	# Load items into array
	while (my $usageTotals = $sth->fetchrow_hashref()) {

		# Set array blank
		my @recordRow = ();

		# Set array items
		@recordRow = (
			$usageTotals->{'username'},
			$lastMonth->year."-".$lastMonth->month,
			$usageTotals->{'acctsessiontime'},
			$usageTotals->{'acctinputoctets'},
			$usageTotals->{'acctinputgigawords'},
			$usageTotals->{'acctoutputoctets'},
			$usageTotals->{'acctoutputgigawords'}
		);

		# Add record ontp @allRecords
		@{$allRecords[$i]} = @recordRow;

		# Increate array size
		$i++;
	}

	# Begin transaction
	DBBegin();

	my @dbDoParams = ();
	my $count = length(@allRecords);

	# Update totals for last month
	for ($i = 0; $i < $count; $i++) {
		@dbDoParams = ('
			INSERT INTO
				@TP@accounting_summary
			(
				Username,
				PeriodKey,
				AcctSessionTime,
				AcctInputOctets,
				AcctInputGigawords,
				AcctOutputOctets,
				AcctOutputGigawords
			)
			VALUES
				(?,?,?,?,?,?,?)
			',
			@{$allRecords[$i]}
		);

		if ($sth) {
			# Do query
			$sth = DBDo(@dbDoParams);
		}
	}

	# Rollback with error if failed
	if (!$sth) {
		DBRollback();
		$server->log(LOG_ERR,"[MOD_ACCOUNTING_SQL] Cleanup => Failed to insert accounting record: ".
				awitpt::db::dblayer::Error());
		return;
	}

	# Commit if succeeded
	DBCommit();
	$server->log(LOG_NOTICE,"[MOD_ACCOUNTING_SQL] Cleanup => Totals have been updated");
}


1;
# vim: ts=4
