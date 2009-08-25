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
use Math::BigInt;


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
			AcctDelayTime,
			PeriodKey
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
			%{request.Acct-Delay-Time},
			%{query.PeriodKey}
		)
	';

	$config->{'accounting_update_get_records_query'} = '
		SELECT
			SUM(AcctInputOctets) AS InputOctets,
			SUM(AcctInputPackets) AS InputPackets,
			SUM(AcctOutputOctets) AS OutputOctets,
			SUM(AcctOutputPackets) AS OutputPackets,
			SUM(AcctInputGigawords) AS InputGigawords,
			SUM(AcctOutputGigawords) AS OutputGigawords,
			SUM(AcctSessionTime) AS SessionTime,
			PeriodKey
		FROM
			@TP@accounting
		WHERE
			Username = %{request.User-Name}
			AND AcctSessionID = %{request.Acct-Session-Id}
			AND NASIPAddress = %{request.NAS-IP-Address}
		GROUP BY
			PeriodKey
		ORDER BY
			ID ASC
	';

	$config->{'accounting_update_query'} = '
		UPDATE
			@TP@accounting
		SET
			AcctSessionTime = %{query.SessionTime},
			AcctInputOctets = %{query.InputOctets},
			AcctInputGigawords = %{query.InputGigawords},
			AcctInputPackets = %{query.InputPackets},
			AcctOutputOctets = %{query.OutputOctets},
			AcctOutputGigawords = %{query.OutputGigawords},
			AcctOutputPackets = %{query.OutputPackets},
			AcctStatusType = %{request.Acct-Status-Type}
		WHERE
			Username = %{request.User-Name}
			AND AcctSessionID = %{request.Acct-Session-Id}
			AND NASIPAddress = %{request.NAS-IP-Address}
			AND PeriodKey = %{query.PeriodKey}
	';

	$config->{'accounting_stop_query'} = '
		UPDATE
			@TP@accounting
		SET
			AcctSessionTime = %{query.SessionTime},
			AcctInputOctets = %{query.InputOctets},
			AcctInputGigawords = %{query.InputGigawords},
			AcctInputPackets = %{query.InputPackets},
			AcctOutputOctets = %{query.OutputOctets},
			AcctOutputGigawords = %{query.OutputGigawords},
			AcctOutputPackets = %{query.OutputPackets}
		WHERE
			Username = %{request.User-Name}
			AND AcctSessionID = %{request.Acct-Session-Id}
			AND NASIPAddress = %{request.NAS-IP-Address}
			AND PeriodKey = %{query.PeriodKey}
	';

	$config->{'accounting_stop_status_query'} = '
		UPDATE
			@TP@accounting
		SET
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
			AND PeriodKey = %{query.PeriodKey}
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
		if (defined($scfg->{'mod_accounting_sql'}->{'accounting_update_get_records_query'}) &&
				$scfg->{'mod_accounting_sql'}->{'accounting_update_get_records_query'} ne "") {
			if (ref($scfg->{'mod_accounting_sql'}->{'accounting_update_get_records_query'}) eq "ARRAY") {
				$config->{'accounting_update_get_records_query'} = join(' ',
						@{$scfg->{'mod_accounting_sql'}->{'accounting_update_get_records_query'}});
			} else {
				$config->{'accounting_update_get_records_query'} = $scfg->{'mod_accounting_sql'}->{'accounting_update_get_records_query'};
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
		if (defined($scfg->{'mod_accounting_sql'}->{'accounting_stop_status_query'}) &&
				$scfg->{'mod_accounting_sql'}->{'accounting_stop_status_query'} ne "") {
			if (ref($scfg->{'mod_accounting_sql'}->{'accounting_stop_status_query'}) eq "ARRAY") {
				$config->{'accounting_stop_status_query'} = join(' ',
						@{$scfg->{'mod_accounting_sql'}->{'accounting_stop_status_query'}});
			} else {
				$config->{'accounting_stop_status_query'} = $scfg->{'mod_accounting_sql'}->{'accounting_stop_status_query'};
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

	# Current PeriodKey
	my $now = DateTime->now;
	$template->{'query'}->{'PeriodKey'} = $now->strftime("%Y-%m");

	# Replace template entries
	my (@dbDoParams) = templateReplace($config->{'accounting_usage_query'},$template);

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
	my $usageData = hashifyLCtoMC(
		$sth->fetchrow_hashref(),
		qw(InputOctets OutputOctets InputGigawords OutputGigawords SessionTime)
	);

	DBFreeRes($sth);

	# FIXME, as its a custom query, check we have all the fields we need

	# Total up input
	my $totalData = 0; 
	if (defined($usageData->{'InputOctets'}) && $usageData->{'InputOctets'} > 0) {
		$totalData += $usageData->{'InputOctets'} / 1024 / 1024;
	}
	if (defined($usageData->{'InputGigawords'}) && $usageData->{'InputGigawords'} > 0) {
		$totalData += $usageData->{'InputGigawords'} * 4096;
	}
	# Add up output
	if (defined($usageData->{'OutputOctets'}) && $usageData->{'OutputOctets'} > 0) {
		$totalData += $usageData->{'OutputOctets'} / 1024 / 1024;
	}
	if (defined($usageData->{'OutputGigawords'}) && $usageData->{'OutputGigawords'} > 0) {
		$totalData += $usageData->{'OutputGigawords'} * 4096;
	}

	# Add up time
	my $totalTime = 0; 
	if (defined($usageData->{'SessionTime'}) && $usageData->{'SessionTime'} > 0) {
		$totalTime = $usageData->{'SessionTime'} / 60;
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
		$template->{'request'}->{$attr} = $packet->rawattr($attr);
	}
	# Fix event timestamp
	$template->{'request'}->{'Timestamp'} = $user->{'_Internal'}->{'Timestamp'};

	# Add user
	$template->{'user'} = $user;

	# Current PeriodKey
	my $now = DateTime->now;
	my $periodKey = $now->strftime("%Y-%m");

	# For our queries
	$template->{'query'}->{'PeriodKey'} = $periodKey;

	#
	# S T A R T   P A C K E T
	#

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

	#
	# U P D A T E   P A C K E T
	#

	} elsif ($packet->attr('Acct-Status-Type') eq "Alive") {
		# Replace template entries
		my @dbDoParams = templateReplace($config->{'accounting_update_get_records_query'},$template);

		# Fetch previous records of the same session
		my $sth = DBSelect(@dbDoParams);
		if (!$sth) {
			$server->log(LOG_ERR,"[MOD_ACCOUNTING_SQL] Database query failed: ".awitpt::db::dblayer::Error());
			return;
		}

		# Convert session total gigawords/octets into bytes
		my $totalInputBytes = Math::BigInt->new();
		$totalInputBytes->badd($template->{'request'}->{'Acct-Input-Gigawords'})->bmul(UINT_MAX);
		$totalInputBytes->badd($template->{'request'}->{'Acct-Input-Octets'});
		my $totalOutputBytes = Math::BigInt->new();
		$totalOutputBytes->badd($template->{'request'}->{'Acct-Output-Gigawords'})->bmul(UINT_MAX);
		$totalOutputBytes->badd($template->{'request'}->{'Acct-Output-Octets'});

		# Loop through previous records and subtract them from our session totals
		my $startNewPeriod = 0;
		$template->{'query'}->{'InputPackets'} = $template->{'request'}->{'Acct-Input-Packets'};
		$template->{'query'}->{'OutputPackets'} = $template->{'request'}->{'Acct-Output-Packets'};
		$template->{'query'}->{'SessionTime'} = $template->{'request'}->{'Acct-Session-Time'};
		while (my $sessionPart = $sth->fetchrow_hashref()) {
			$sessionPart = hashifyLCtoMC(
				$sessionPart,
				qw(InputOctets InputPackets OutputOctets OutputPackets InputGigawords OutputGigawords SessionTime PeriodKey)
			);

			# Convert this session usage to bytes
			my $sessionInputBytes = Math::BigInt->new();
			$sessionInputBytes->badd($sessionPart->{'InputGigawods'})->bmul(UINT_MAX);
			$sessionInputBytes->badd($sessionPart->{'InputOctets'});
			my $sessionOutputBytes = Math::BigInt->new();
			$sessionOutputBytes->badd($sessionPart->{'OutputGigawods'})->bmul(UINT_MAX);
			$sessionOutputBytes->badd($sessionPart->{'OutputOctets'});

			# Check if this record is from an earlier period
			$startNewPeriod = 0;
			if (defined($sessionPart->{'PeriodKey'}) && $sessionPart->{'PeriodKey'} ne $periodKey) {

				# Subtract from our total
				$totalInputBytes->bsub($sessionInputBytes);
				$totalOutputBytes->bsub($sessionOutputBytes);

				# Subtract other usage
				if (defined($sessionPart->{'InputPackets'}) && $sessionPart->{'InputPackets'} > 0) {
					$template->{'query'}->{'InputPackets'} -= $sessionPart->{'InputPackets'};
				}
				if (defined($sessionPart->{'OutputPackets'}) && $sessionPart->{'OutputPackets'} > 0) {
					$template->{'query'}->{'OutputPackets'} -= $sessionPart->{'OutputPackets'};
				}
				if (defined($sessionPart->{'SessionTime'}) && $sessionPart->{'SessionTime'} > 0) {
					$template->{'query'}->{'SessionTime'} -= $sessionPart->{'SessionTime'};
				}

				# We need to continue this session in a new entry
				$startNewPeriod = 1;
			}
		}

		# Re-calculate
		my ($inputGigawordsStr,$inputOctetsStr) = $totalInputBytes->bdiv(UINT_MAX);
		my ($outputGigawordsStr,$outputOctetsStr) = $totalOutputBytes->bdiv(UINT_MAX);

		# Conversion to strings
		$template->{'query'}->{'InputGigawords'} = $inputGigawordsStr->bstr();
		$template->{'query'}->{'InputOctets'} = $inputOctetsStr->bstr();
		$template->{'query'}->{'OutputGigawords'} = $outputGigawordsStr->bstr();
		$template->{'query'}->{'OutputOctets'} = $outputOctetsStr->bstr();

		# Check if we doing an update
		if ($startNewPeriod == 0) {
			# Replace template entries
			@dbDoParams = templateReplace($config->{'accounting_update_query'},$template);

			# Update database
			my $sth = DBDo(@dbDoParams);
			if (!$sth) {
				$server->log(LOG_ERR,"[MOD_ACCOUNTING_SQL] Failed to update accounting ALIVE record: ".
						awitpt::db::dblayer::Error());
				return MOD_RES_NACK;
			}
		# Else do a start record to continue session
		} else {
			# Replace template entries
			my @dbDoParams = templateReplace($config->{'accounting_start_query'},$template);

			# Insert into database
			my $sth = DBDo(@dbDoParams);
			if (!$sth) {
				$server->log(LOG_ERR,"[MOD_ACCOUNTING_SQL] Failed to insert accounting START record: ".
						awitpt::db::dblayer::Error());
				return MOD_RES_NACK;
			}
			$startNewPeriod = 0;
		}

	#
	# S T O P   P A C K E T
	#

	} elsif ($packet->attr('Acct-Status-Type') eq "Stop") {

		# Replace template entries
		my @dbDoParams = templateReplace($config->{'accounting_update_get_records_query'},$template);

		# Fetch data
		my $sth = DBSelect(@dbDoParams);
		if (!$sth) {
			$server->log(LOG_ERR,"[MOD_ACCOUNTING_SQL] Database query failed: ".awitpt::db::dblayer::Error());
			return MOD_RES_NACK;
		}

		# Convert session total gigawords/octets into bytes
		my $totalInputBytes = Math::BigInt->new();
		$totalInputBytes->badd($template->{'request'}->{'Acct-Input-Gigawords'})->bmul(UINT_MAX);
		$totalInputBytes->badd($template->{'request'}->{'Acct-Input-Octets'});
		my $totalOutputBytes = Math::BigInt->new();
		$totalOutputBytes->badd($template->{'request'}->{'Acct-Output-Gigawords'})->bmul(UINT_MAX);
		$totalOutputBytes->badd($template->{'request'}->{'Acct-Output-Octets'});

		# Loop through records and subtract from our totals if needed
		$template->{'query'}->{'InputPackets'} = $template->{'request'}->{'Acct-Input-Packets'};
		$template->{'query'}->{'OutputPackets'} = $template->{'request'}->{'Acct-Output-Packets'};
		$template->{'query'}->{'SessionTime'} = $template->{'request'}->{'Acct-Session-Time'};
		while (my $sessionPart = $sth->fetchrow_hashref()) {
			$sessionPart = hashifyLCtoMC(
				$sessionPart,
				qw(InputOctets InputPackets OutputOctets OutputPackets InputGigawords OutputGigawords SessionTime PeriodKey)
			);

			# Convert this session usage to bytes
			my $sessionInputBytes = Math::BigInt->new();
			$sessionInputBytes->badd($sessionPart->{'InputGigawods'})->bmul(UINT_MAX);
			$sessionInputBytes->badd($sessionPart->{'InputOctets'});
			my $sessionOutputBytes = Math::BigInt->new();
			$sessionOutputBytes->badd($sessionPart->{'OutputGigawods'})->bmul(UINT_MAX);
			$sessionOutputBytes->badd($sessionPart->{'OutputOctets'});

			# Subtract this period/session usage from total
			if (defined($sessionPart->{'PeriodKey'}) && $sessionPart->{'PeriodKey'} ne $periodKey) {

				# Subtract from our total
				$totalInputBytes->bsub($sessionInputBytes);
				$totalOutputBytes->bsub($sessionInputBytes);

				# Subtract other usage
				if (defined($sessionPart->{'InputPackets'}) && $sessionPart->{'InputPackets'} > 0) {
					$template->{'query'}->{'InputPackets'} -= $sessionPart->{'InputPackets'};
				}
				if (defined($sessionPart->{'OutputPackets'}) && $sessionPart->{'OutputPackets'} > 0) {
					$template->{'query'}->{'OutputPackets'} -= $sessionPart->{'OutputPackets'};
				}
				if (defined($sessionPart->{'SessionTime'}) && $sessionPart->{'SessionTime'} > 0) {
					$template->{'query'}->{'SessionTime'} -= $sessionPart->{'SessionTime'};
				}
			}
		}
		DBFreeRes($sth);

		# Re-calculate
		my ($inputGigawordsStr,$inputOctetsStr) = $totalInputBytes->bdiv(UINT_MAX);
		my ($outputGigawordsStr,$outputOctetsStr) = $totalOutputBytes->bdiv(UINT_MAX);

		# Conversion to strings
		$template->{'query'}->{'InputGigawords'} = $inputGigawordsStr->bstr();
		$template->{'query'}->{'InputOctets'} = $inputOctetsStr->bstr();
		$template->{'query'}->{'OutputGigawords'} = $outputGigawordsStr->bstr();
		$template->{'query'}->{'OutputOctets'} = $outputOctetsStr->bstr();

		# Replace template entries
		@dbDoParams = templateReplace($config->{'accounting_stop_query'},$template);

		# Update database (totals)
		$sth = DBDo(@dbDoParams);
		if (!$sth) {
			$server->log(LOG_ERR,"[MOD_ACCOUNTING_SQL] Failed to update accounting STOP record: ".awitpt::db::dblayer::Error());
			return MOD_RES_NACK;
		}

		# Replace template entries
		@dbDoParams = templateReplace($config->{'accounting_stop_status_query'},$template);

		# Update database (status)
		$sth = DBDo(@dbDoParams);
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

	# The datetime now..
	my $now = DateTime->now;

	# If this is a new year
	my ($prevYear,$prevMonth);
	if ($now->month == 1) {
		$prevYear = $now->year - 1;
		$prevMonth = 12;
	} else {
		$prevYear = $now->year;
		$prevMonth = $now->month - 1;
	}

	# New datetime
	my $lastMonth = DateTime->new( year => $prevYear, month => $prevMonth, day => 1 );
	my $periodKey = $lastMonth->strftime("%Y-%m");

	# Select totals for last month
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
			PeriodKey = ?
		GROUP BY
			Username
		',
		$periodKey
	);

	if (!$sth) {
		$server->log(LOG_ERR,"[MOD_ACCOUNTING_SQL] Cleanup => Failed to select accounting record: ".
				awitpt::db::dblayer::Error());
		return;
	}

	# Set blank array
	my @allRecords;

	# Load items into array
	my $index = 0;
	while (my $usageTotals = $sth->fetchrow_hashref()) {
		$usageTotals = hashifyLCtoMC(
			$usageTotals,
			qw(Username AcctSessionTime AcctInputOctets AcctInputGigawords AcctOutputOctets AcctOutputGigawords)
		);

		# Set array items
		$allRecords[$index] = {
			Username => $usageTotals->{'Username'},
			PeriodKey => $lastMonth->ymd,
			SessionTime => $usageTotals->{'AcctSessionTime'},
			InputOctets => $usageTotals->{'AcctInputOctets'},
			InputGigawords => $usageTotals->{'AcctInputGigawords'},
			OutputOctets => $usageTotals->{'AcctOutputOctets'},
			OutputGigawords => $usageTotals->{'AcctOutputGigawords'}
		};

		# Increase size
		$index++;
	}

	# Begin transaction
	DBBegin();

	# Update totals for last month
	if ($index > 0) {

		# Delete duplicate records
		my @dbDoParams;
		@dbDoParams = ('
			DELETE FROM
				@TP@accounting_summary
			WHERE
				PeriodKey = ?',
			$lastMonth->ymd
		);

		if ($sth) {
			# Do query
			$sth = DBDo(@dbDoParams);
		}

		my @insertArray;
		for (my $i = 0; $i < $index; $i++) {

			# Check if this record exists
			my $sth = DBSelect('
				SELECT
					COUNT(*) as rowCount
				FROM
					@TP@accounting
				WHERE
					PeriodKey = ?
					AND Username = ?
				',
				$allRecords[$i]->{'PeriodKey'},
				$allRecords[$i]->{'Username'}
			);

			if (!$sth) {
				$server->log(LOG_ERR,"[MOD_ACCOUNTING_SQL] Cleanup => Failed to check for existing record: ".
						awitpt::db::dblayer::Error());
				return;
			}

			my $recordCheck = $sth->fetchrow_hashref();
			$recordCheck = hashifyLCtoMC(
				$recordCheck,
				qw(rowCount)
			);

			if (defined($recordCheck->{'rowCount'}) && $recordCheck->{'rowCount'} > 0) {
				@insertArray = (
					$allRecords[$i]->{'SessionTime'},
					$allRecords[$i]->{'InputOctets'},
					$allRecords[$i]->{'InputGigawords'},
					$allRecords[$i]->{'OutputOctets'},
					$allRecords[$i]->{'OutputGigawords'},
					$allRecords[$i]->{'Username'},
					$allRecords[$i]->{'PeriodKey'}
				);

				@dbDoParams = ('
					UPDATE
						@TP@accounting_summary
					SET
						AcctSessionTime = ?,
						AcctInputOctets = ?,
						AcctInputGigawords = ?,
						AcctOutputOctets = ?,
						AcctOutputGigawords = ?
					WHERE
						Username = ?
						AND	PeriodKey = ?
					',
					@insertArray
				);

				if ($sth) {
					# Do query
					$sth = DBDo(@dbDoParams);
				}
			} else {
				@insertArray = (
					$allRecords[$i]->{'Username'},
					$allRecords[$i]->{'PeriodKey'},
					$allRecords[$i]->{'SessionTime'},
					$allRecords[$i]->{'InputOctets'},
					$allRecords[$i]->{'InputGigawords'},
					$allRecords[$i]->{'OutputOctets'},
					$allRecords[$i]->{'OutputGigawords'}
				);

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
					@insertArray
				);

				if ($sth) {
					# Do query
					$sth = DBDo(@dbDoParams);
				}
			}
		}
	}

	# Rollback with error if failed
	if (!$sth) {
		DBRollback();
		$server->log(LOG_ERR,"[MOD_ACCOUNTING_SQL] Cleanup => Failed to insert or update accounting record: ".
				awitpt::db::dblayer::Error());
		return;
	}

	# Commit if succeeded
	DBCommit();
	$server->log(LOG_NOTICE,"[MOD_ACCOUNTING_SQL] Cleanup => Totals have been updated");
}


1;
# vim: ts=4
