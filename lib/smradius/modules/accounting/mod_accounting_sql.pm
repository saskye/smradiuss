# SQL accounting database
# Copyright (C) 2007-2016, AllWorldIT
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
use AWITPT::Cache;
use AWITPT::DB::DBLayer;
use AWITPT::Util;
use smradius::logging;
use smradius::util;

use POSIX qw(ceil);
use DateTime;
use Math::BigInt;
use Math::BigFloat;


# Exporter stuff
use base qw(Exporter);
our @EXPORT = qw(
);
our @EXPORT_OK = qw(
);



# Plugin info
our $pluginInfo = {
	Name => "SQL Accounting Database",
	Init => \&init,

	# Cleanup run by smadmin
	CleanupOrder => 30,
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
			AcctSessionTime,
			AcctInputOctets,
			AcctInputGigawords,
			AcctInputPackets,
			AcctOutputOctets,
			AcctOutputGigawords,
			AcctOutputPackets,
			PeriodKey
		)
		VALUES
		(
			%{user.Username},
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
			%{request.Acct-Session-Time},
			%{request.Acct-Input-Octets},
			%{request.Acct-Input-Gigawords},
			%{request.Acct-Input-Packets},
			%{request.Acct-Output-Octets},
			%{request.Acct-Output-Gigawords},
			%{request.Acct-Output-Packets},
			%{query.PeriodKey}
		)
	';

	$config->{'accounting_update_get_records_query'} = '
		SELECT
			SUM(AcctInputOctets) AS AcctInputOctets,
			SUM(AcctInputPackets) AS AcctInputPackets,
			SUM(AcctOutputOctets) AS AcctOutputOctets,
			SUM(AcctOutputPackets) AS AcctOutputPackets,
			SUM(AcctInputGigawords) AS AcctInputGigawords,
			SUM(AcctOutputGigawords) AS AcctOutputGigawords,
			SUM(AcctSessionTime) AS AcctSessionTime,
			PeriodKey
		FROM
			@TP@accounting
		WHERE
			Username = %{user.Username}
			AND AcctSessionID = %{request.Acct-Session-Id}
			AND NASIPAddress = %{request.NAS-IP-Address}
			AND NASPort = %{request.NAS-Port}
		GROUP BY
			PeriodKey
		ORDER BY
			ID ASC
	';

	$config->{'accounting_update_query'} = '
		UPDATE
			@TP@accounting
		SET
			AcctSessionTime = %{query.Acct-Session-Time},
			AcctInputOctets = %{query.Acct-Input-Octets},
			AcctInputGigawords = %{query.Acct-Input-Gigawords},
			AcctInputPackets = %{query.Acct-Input-Packets},
			AcctOutputOctets = %{query.Acct-Output-Octets},
			AcctOutputGigawords = %{query.Acct-Output-Gigawords},
			AcctOutputPackets = %{query.Acct-Output-Packets},
			AcctStatusType = %{request.Acct-Status-Type}
		WHERE
			Username = %{user.Username}
			AND AcctSessionID = %{request.Acct-Session-Id}
			AND NASIPAddress = %{request.NAS-IP-Address}
			AND NASPort = %{request.NAS-Port}
			AND PeriodKey = %{query.PeriodKey}
	';

	$config->{'accounting_stop_status_query'} = '
		UPDATE
			@TP@accounting
		SET
			AcctStatusType = %{request.Acct-Status-Type},
			AcctTerminateCause = %{request.Acct-Terminate-Cause}
		WHERE
			Username = %{user.Username}
			AND AcctSessionID = %{request.Acct-Session-Id}
			AND NASIPAddress = %{request.NAS-IP-Address}
			AND NASPort = %{request.NAS-Port}
	';

	$config->{'accounting_usage_query'} = '
		SELECT
			SUM(AcctInputOctets) AS AcctInputOctets,
			SUM(AcctOutputOctets) AS AcctOutputOctets,
			SUM(AcctInputGigawords) AS AcctInputGigawords,
			SUM(AcctOutputGigawords) AS AcctOutputGigawords,
			SUM(AcctSessionTime) AS AcctSessionTime
		FROM
			@TP@accounting
		WHERE
			Username = %{user.Username}
			AND PeriodKey = %{query.PeriodKey}
	';

	$config->{'accounting_select_duplicates_query'} = '
		SELECT
			ID
		FROM
			@TP@accounting
		WHERE
			Username = %{user.Username}
			AND AcctSessionID = %{request.Acct-Session-Id}
			AND NASIPAddress = %{request.NAS-IP-Address}
			AND NASPort = %{request.NAS-Port}
			AND PeriodKey = %{query.PeriodKey}
		ORDER BY
			ID
			LIMIT 99 OFFSET 1
	';

	$config->{'accounting_delete_duplicates_query'} = '
		DELETE FROM
			@TP@accounting
		WHERE
			ID = %{query.DuplicateID}
	';

	$config->{'accounting_usage_cache_time'} = 300;


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
		if (defined($scfg->{'mod_accounting_sql'}->{'accounting_select_duplicates_query'}) &&
				$scfg->{'mod_accounting_sql'}->{'accounting_select_duplicates_query'} ne "") {
			if (ref($scfg->{'mod_accounting_sql'}->{'accounting_select_duplicates_query'}) eq "ARRAY") {
				$config->{'accounting_select_duplicates_query'} = join(' ',
						@{$scfg->{'mod_accounting_sql'}->{'accounting_select_duplicates_query'}});
			} else {
				$config->{'accounting_select_duplicates_query'} = $scfg->{'mod_accounting_sql'}->{'accounting_select_duplicates_query'};
			}
		}
		if (defined($scfg->{'mod_accounting_sql'}->{'accounting_delete_duplicates_query'}) &&
				$scfg->{'mod_accounting_sql'}->{'accounting_delete_duplicates_query'} ne "") {
			if (ref($scfg->{'mod_accounting_sql'}->{'accounting_delete_duplicates_query'}) eq "ARRAY") {
				$config->{'accounting_delete_duplicates_query'} = join(' ',
						@{$scfg->{'mod_accounting_sql'}->{'accounting_delete_duplicates_query'}});
			} else {
				$config->{'accounting_delete_duplicates_query'} = $scfg->{'mod_accounting_sql'}->{'accounting_delete_duplicates_query'};
			}
		}
		if (defined($scfg->{'mod_accounting_sql'}->{'accounting_usage_cache_time'})) {
			# Check if we're a boolean
			if (defined(my $val = isBoolean($scfg->{'mod_accounting_sql'}{'accounting_usage_cache_time'}))) {
				# If val is true, we default to the default anyway

				# We're disabled
				if (!$val) {
					$config->{'accounting_usage_cache_time'} = undef;
				}
			# We *could* have a value...
			} elsif ($scfg->{'mod_accounting_sql'}{'accounting_usage_cache_time'} =~ /^[0-9]+$/) {
				$config->{'accounting_usage_cache_time'} = $scfg->{'mod_accounting_sql'}{'accounting_usage_cache_time'};
			} else {
				$server->log(LOG_NOTICE,"[MOD_ACCOUNTING_SQL] Value for 'accounting_usage_cache_time' is invalid");
			}
		}
	}

	# Log this for info sake
	if (defined($config->{'accounting_usage_cache_time'})) {
		$server->log(LOG_NOTICE,"[MOD_ACCOUNTING_SQL] getUsage caching ENABLED, cache time is %ds.",
				$config->{'accounting_usage_cache_time'});
	} else {
		$server->log(LOG_NOTICE,"[MOD_ACCOUNTING_SQL] getUsage caching DISABLED");
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

	# Add user details
	$template->{'user'}->{'ID'} = $user->{'ID'};
	$template->{'user'}->{'Username'} = $user->{'Username'};

	# Current PeriodKey
	my $now = DateTime->now->set_time_zone($server->{'smradius'}->{'event_timezone'});
	$template->{'query'}->{'PeriodKey'} = $now->strftime("%Y-%m");

	# If we using caching, check how old the result is
	if (defined($config->{'accounting_usage_cache_time'})) {
		my ($res,$val) = cacheGetComplexKeyPair('mod_accounting_sql(getUsage)',$user->{'Username'}."/".
				$template->{'query'}->{'PeriodKey'});
		if (defined($val) && $val->{'CachedUntil'} > $user->{'_Internal'}->{'Timestamp-Unix'}) {
			return $val;
		}
	}

	# Replace template entries
	my (@dbDoParams) = templateReplace($config->{'accounting_usage_query'},$template);

	# Fetch data
	my $sth = DBSelect(@dbDoParams);
	if (!$sth) {
		$server->log(LOG_ERR,"[MOD_ACCOUNTING_SQL] Database query failed: %s",AWITPT::DB::DBLayer::error());
		return;
	}

	# Our usage hash
	my %usageTotals;
	$usageTotals{'TotalSessionTime'} = Math::BigInt->new();
	$usageTotals{'TotalDataInput'} = Math::BigInt->new();
	$usageTotals{'TotalDataOutput'} = Math::BigInt->new();

	# Pull in usage and add up
	while (my $row = hashifyLCtoMC($sth->fetchrow_hashref(),
			qw(AcctSessionTime AcctInputOctets AcctInputGigawords AcctOutputOctets AcctOutputGigawords)
	)) {

		# Look for session time
		if (defined($row->{'AcctSessionTime'}) && $row->{'AcctSessionTime'} > 0) {
			$usageTotals{'TotalSessionTime'}->badd($row->{'AcctSessionTime'});
		}
		# Add input usage if we have any
		if (defined($row->{'AcctInputOctets'}) && $row->{'AcctInputOctets'} > 0) {
			$usageTotals{'TotalDataInput'}->badd($row->{'AcctInputOctets'});
		}
		if (defined($row->{'AcctInputGigawords'}) && $row->{'AcctInputGigawords'} > 0) {
			my $inputGigawords = Math::BigInt->new($row->{'AcctInputGigawords'});
			$inputGigawords->bmul(UINT_MAX);
			$usageTotals{'TotalDataInput'}->badd($inputGigawords);
		}
		# Add output usage if we have any
		if (defined($row->{'AcctOutputOctets'}) && $row->{'AcctOutputOctets'} > 0) {
			$usageTotals{'TotalDataOutput'}->badd($row->{'AcctOutputOctets'});
		}
		if (defined($row->{'AcctOutputGigawords'}) && $row->{'AcctOutputGigawords'} > 0) {
			my $outputGigawords = Math::BigInt->new($row->{'AcctOutputGigawords'});
			$outputGigawords->bmul(UINT_MAX);
			$usageTotals{'TotalDataOutput'}->badd($outputGigawords);
		}
	}
	DBFreeRes($sth);

	# Convert to bigfloat for accuracy
	my $totalData = Math::BigFloat->new();
	$totalData->badd($usageTotals{'TotalDataOutput'})->badd($usageTotals{'TotalDataInput'});
	my $totalTime = Math::BigFloat->new();
	$totalTime->badd($usageTotals{'TotalSessionTime'});

	# Rounding up
	my %res;
	$res{'TotalDataUsage'} = $totalData->bdiv(1024)->bdiv(1024)->bceil()->bstr();
	$res{'TotalSessionTime'} = $totalTime->bdiv(60)->bceil()->bstr();

	# If we using caching and got here, it means that we must cache the result
	if (defined($config->{'accounting_usage_cache_time'})) {
		$res{'CachedUntil'} = $user->{'_Internal'}->{'Timestamp-Unix'} + $config->{'accounting_usage_cache_time'};

		# Cache the result
		cacheStoreComplexKeyPair('mod_accounting_sql(getUsage)',$user->{'Username'}."/".$template->{'query'}->{'PeriodKey'},\%res);
	}

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

	# Add user details
	$template->{'user'}->{'ID'} = $user->{'ID'};
	$template->{'user'}->{'Username'} = $user->{'Username'};

	# Current PeriodKey
	my $now = DateTime->now->set_time_zone($server->{'smradius'}->{'event_timezone'});
	my $periodKey = $now->strftime("%Y-%m");

	# For our queries
	$template->{'query'}->{'PeriodKey'} = $periodKey;

	# Default to being a new period, only if we update on INTERIM or STOP do we set this to 0
	my $newPeriod = 1;

	#
	# U P D A T E   &   S T O P   P A C K E T
	#
	if ($packet->rawattr('Acct-Status-Type') eq "2" || $packet->rawattr('Acct-Status-Type') eq "3") {
		# Replace template entries
		my @dbDoParams = templateReplace($config->{'accounting_update_get_records_query'},$template);

		# Fetch previous records of the same session
		my $sth = DBSelect(@dbDoParams);
		if (!$sth) {
			$server->log(LOG_ERR,"[MOD_ACCOUNTING_SQL] Database query failed: %s",AWITPT::DB::DBLayer::error());
			return;
		}

		# Convert session total gigawords/octets into bytes
		my $totalInputBytes = Math::BigInt->new();
		$totalInputBytes->badd($template->{'request'}->{'Acct-Input-Gigawords'})->bmul(UINT_MAX);
		$totalInputBytes->badd($template->{'request'}->{'Acct-Input-Octets'});
		my $totalOutputBytes = Math::BigInt->new();
		$totalOutputBytes->badd($template->{'request'}->{'Acct-Output-Gigawords'})->bmul(UINT_MAX);
		$totalOutputBytes->badd($template->{'request'}->{'Acct-Output-Octets'});
		# Packets, no conversion
		my $totalInputPackets = Math::BigInt->new($template->{'request'}->{'Acct-Input-Packets'});
		my $totalOutputPackets = Math::BigInt->new($template->{'request'}->{'Acct-Output-Packets'});
		# We don't need bigint here, but why not ... lets keep everything standard
		my $totalSessionTime = Math::BigInt->new($template->{'request'}->{'Acct-Session-Time'});

		# Loop through previous records and subtract them from our session totals
		while (my $sessionPart = hashifyLCtoMC($sth->fetchrow_hashref(),
				qw(AcctInputOctets AcctInputPackets AcctOutputOctets AcctOutputPackets AcctInputGigawords AcctOutputGigawords
					SessionTime PeriodKey)
		)) {

			# Convert this session usage to bytes
			my $sessionInputBytes = Math::BigInt->new();
			$sessionInputBytes->badd($sessionPart->{'AcctInputGigawods'})->bmul(UINT_MAX);
			$sessionInputBytes->badd($sessionPart->{'AcctInputOctets'});
			my $sessionOutputBytes = Math::BigInt->new();
			$sessionOutputBytes->badd($sessionPart->{'AcctOutputGigawods'})->bmul(UINT_MAX);
			$sessionOutputBytes->badd($sessionPart->{'AcctOutputOctets'});
			# And packets
			my $sessionInputPackets = Math::BigInt->new($sessionPart->{'AcctInputPackets'});
			my $sessionOutputPackets = Math::BigInt->new($sessionPart->{'AcctOutputPackets'});
			# Finally session time
			my $sessionSessionTime = Math::BigInt->new($sessionPart->{'AcctSessionTime'});

			# Check if this record is from an earlier period
			if (defined($sessionPart->{'PeriodKey'}) && $sessionPart->{'PeriodKey'} ne $periodKey) {

				# Subtract from our total, we can hit NEG!!! ... we check for that below
				$totalInputBytes->bsub($sessionInputBytes);
				$totalOutputBytes->bsub($sessionOutputBytes);
				$totalInputPackets->bsub($sessionInputPackets);
				$totalOutputPackets->bsub($sessionOutputPackets);
				$totalSessionTime->bsub($sessionSessionTime);

				# We need to continue this session in a new entry
				$newPeriod = 1;
			}
		}
		DBFreeRes($sth);

		# Sanitize
		if ($totalInputBytes->is_neg()) {
			$totalInputBytes->bzero();
		}
		if ($totalOutputBytes->is_neg()) {
			$totalOutputBytes->bzero();
		}
		if ($totalInputPackets->is_neg()) {
			$totalInputPackets->bzero();
		}
		if ($totalOutputPackets->is_neg()) {
			$totalOutputPackets->bzero();
		}
		if ($totalSessionTime->is_neg()) {
			$totalSessionTime->bzero();
		}

		# Re-calculate
		my ($inputGigawordsStr,$inputOctetsStr) = $totalInputBytes->bdiv(UINT_MAX);
		my ($outputGigawordsStr,$outputOctetsStr) = $totalOutputBytes->bdiv(UINT_MAX);

		# Conversion to strings
		$template->{'query'}->{'Acct-Input-Gigawords'} = $inputGigawordsStr->bstr();
		$template->{'query'}->{'Acct-Input-Octets'} = $inputOctetsStr->bstr();
		$template->{'query'}->{'Acct-Output-Gigawords'} = $outputGigawordsStr->bstr();
		$template->{'query'}->{'Acct-Output-Octets'} = $outputOctetsStr->bstr();

		$template->{'query'}->{'Acct-Input-Packets'} = $totalInputPackets->bstr();
		$template->{'query'}->{'Acct-Output-Packets'} = $totalOutputPackets->bstr();

		$template->{'query'}->{'Acct-Session-Time'} = $totalSessionTime->bstr();


		# Replace template entries
		@dbDoParams = templateReplace($config->{'accounting_update_query'},$template);

		# Update database
		$sth = DBDo(@dbDoParams);
		if (!$sth) {
			$server->log(LOG_ERR,"[MOD_ACCOUNTING_SQL] Failed to update accounting ALIVE record: ".
					AWITPT::DB::DBLayer::error());
			return MOD_RES_NACK;
		}

		# If we updated *something* ...
		if ($sth ne "0E0") {
			# Be very sneaky .... if we updated something, this is obviously NOT a new period
			$newPeriod = 0;
			# If we updated a few things ... possibly duplicates?
			if ($sth > 1) {
				fixDuplicates($server, $template);
			}
		}
	}


	#
	# S T A R T   P A C K E T
	#
	# Possible aswell if we are missing a start packet for this session or for the period
	#

	if ($packet->rawattr('Acct-Status-Type') eq "1" || $newPeriod) {
		# Replace template entries
		my @dbDoParams = templateReplace($config->{'accounting_start_query'},$template);
		# Insert into database
		my $sth = DBDo(@dbDoParams);
		if (!$sth) {
			$server->log(LOG_ERR,"[MOD_ACCOUNTING_SQL] Failed to insert accounting START record: ".
					AWITPT::DB::DBLayer::error());
			return MOD_RES_NACK;
		}
		# Update first login?
		if (defined($user->{'_UserDB'}->{'Users_data_get'}) && defined($user->{'_UserDB'}->{'Users_data_set'})) {
			# Try get his first login
			my $firstLogin = $user->{'_UserDB'}->{'Users_data_get'}($server,$user,'global','FirstLogin');
			# If we don't get it, set it
			if (!defined($firstLogin)) {
				$user->{'_UserDB'}->{'Users_data_set'}($server,$user,'global','FirstLogin',$user->{'_Internal'}->{'Timestamp-Unix'});
			}
		}
	}


	#
	# S T O P   P A C K E T   specifics
	#

	if ($packet->rawattr('Acct-Status-Type') eq "2") {

		# Replace template entries
		my @dbDoParams = templateReplace($config->{'accounting_stop_status_query'},$template);

		# Update database (status)
		my $sth = DBDo(@dbDoParams);
		if (!$sth) {
			$server->log(LOG_ERR,"[MOD_ACCOUNTING_SQL] Failed to update accounting STOP record: %s",AWITPT::DB::DBLayer::error());
			return MOD_RES_NACK;
		}
	}


	return MOD_RES_ACK;
}


# Resolve duplicate records
sub fixDuplicates
{
	my ($server, $template) = @_;


	# Replace template entries
	my @dbDoParams = templateReplace($config->{'accounting_select_duplicates_query'},$template);

	# Select duplicates
	my $sth = DBSelect(@dbDoParams);
	if (!$sth) {
		$server->log(LOG_ERR,"[MOD_ACCOUNTING_SQL] Database query failed: %s",AWITPT::DB::DBLayer::error());
		return;
	}

	# Pull in duplicates
	my @IDList;
	while (my $duplicates = hashifyLCtoMC($sth->fetchrow_hashref(), qw(ID))) {
		push(@IDList,$duplicates->{'ID'});
	}
	DBFreeRes($sth);

	# Loop through IDs and delete
	DBBegin();
	foreach my $duplicateID (@IDList) {
		# Add ID list to the template
		$template->{'query'}->{'DuplicateID'} = $duplicateID;

		# Replace template entries
		@dbDoParams = templateReplace($config->{'accounting_delete_duplicates_query'},$template);

		# Delete duplicates
		$sth = DBDo(@dbDoParams);
		if (!$sth) {
			$server->log(LOG_ERR,"[MOD_ACCOUNTING_SQL] Database query failed: %s",AWITPT::DB::DBLayer::error());
			DBRollback();
			return;
		}
	}

	# Commit changes to the database
	$server->log(LOG_DEBUG,"[MOD_ACCOUNTING_SQL] Duplicate accounting records deleted");
	DBCommit();


	return
}


# Add up totals function
sub cleanup
{
	my ($server,$runForDate) = @_;


	# The datetime now
	my $now = DateTime->from_epoch(epoch => $runForDate)->set_time_zone($server->{'smradius'}->{'event_timezone'});

	# Use truncate to set all values after 'month' to their default values
	my $thisMonth = $now->clone()->truncate( to => "month" );

	# Last month..
	my $lastMonth = $thisMonth->clone()->subtract( months => 1 );
	my $prevPeriodKey = $lastMonth->strftime("%Y-%m");


	# Begin transaction
	DBBegin();

	$server->log(LOG_NOTICE,"[MOD_ACCOUNTING_SQL] Cleanup => Removing previous accounting summaries (if any)");

	# Delete duplicate records
	# NK: MYSQL SPECIFIC
	my $sth = DBDo('
		DELETE FROM
			@TP@accounting_summary
		WHERE
			STR_TO_DATE(PeriodKey,"%Y-%m") >= ?',
		$prevPeriodKey
	);
	if (!$sth) {
		$server->log(LOG_ERR,"[MOD_ACCOUNTING_SQL] Cleanup => Failed to delete accounting summary record: ".
				AWITPT::DB::DBLayer::error());
		DBRollback();
		return;
	}

	$server->log(LOG_NOTICE,"[MOD_ACCOUNTING_SQL] Cleanup => Generating accounting summaries");

	# Select totals for last month
	$sth = DBSelect('
		SELECT
			Username,
			AcctSessionTime,
			AcctInputOctets,
			AcctInputGigawords,
			AcctOutputOctets,
			AcctOutputGigawords
		FROM
			@TP@accounting
		WHERE
			PeriodKey = ?
		',
		$prevPeriodKey
	);
	if (!$sth) {
		$server->log(LOG_ERR,"[MOD_ACCOUNTING_SQL] Cleanup => Failed to select accounting record: ".
				AWITPT::DB::DBLayer::error());
		return;
	}

	# Load items into array
	my %usageTotals;
	while (my $row = hashifyLCtoMC($sth->fetchrow_hashref(),
			qw(Username AcctSessionTime AcctInputOctets AcctInputGigawords AcctOutputOctets AcctOutputGigawords)
	)) {

		# check if we've seen this user, if so just add up
		if (defined($usageTotals{$row->{'Username'}})) {
			# Look for session time
			if (defined($row->{'AcctSessionTime'}) && $row->{'AcctSessionTime'} > 0) {
				$usageTotals{$row->{'Username'}}{'TotalSessionTime'}->badd($row->{'AcctSessionTime'});
			}
			# Add input usage if we have any
			if (defined($row->{'AcctInputOctets'}) && $row->{'AcctInputOctets'} > 0) {
				$usageTotals{$row->{'Username'}}{'TotalDataInput'}->badd($row->{'AcctInputOctets'});
			}
			if (defined($row->{'AcctInputGigawords'}) && $row->{'AcctInputGigawords'} > 0) {
				my $inputGigawords = Math::BigInt->new($row->{'AcctInputGigawords'});
				$inputGigawords->bmul(UINT_MAX);
				$usageTotals{$row->{'Username'}}{'TotalDataInput'}->badd($inputGigawords);
			}
			# Add output usage if we have any
			if (defined($row->{'AcctOutputOctets'}) && $row->{'AcctOutputOctets'} > 0) {
				$usageTotals{$row->{'Username'}}{'TotalDataOutput'}->badd($row->{'AcctOutputOctets'});
			}
			if (defined($row->{'AcctOutputGigawords'}) && $row->{'AcctOutputGigawords'} > 0) {
				my $outputGigawords = Math::BigInt->new($row->{'AcctOutputGigawords'});
				$outputGigawords->bmul(UINT_MAX);
				$usageTotals{$row->{'Username'}}{'TotalDataOutput'}->badd($outputGigawords);
			}

		# This is a new record...
		} else {

			# Make BigInts for this user
			$usageTotals{$row->{'Username'}}{'TotalSessionTime'} = Math::BigInt->new();
			$usageTotals{$row->{'Username'}}{'TotalDataInput'} = Math::BigInt->new();
			$usageTotals{$row->{'Username'}}{'TotalDataOutput'} = Math::BigInt->new();

			# Look for session time
			if (defined($row->{'AcctSessionTime'}) && $row->{'AcctSessionTime'} > 0) {
				$usageTotals{$row->{'Username'}}{'TotalSessionTime'}->badd($row->{'AcctSessionTime'});
			}
			# Add input usage if we have any
			if (defined($row->{'AcctInputOctets'}) && $row->{'AcctInputOctets'} > 0) {
				$usageTotals{$row->{'Username'}}{'TotalDataInput'}->badd($row->{'AcctInputOctets'});
			}
			if (defined($row->{'AcctInputGigawords'}) && $row->{'AcctInputGigawords'} > 0) {
				my $inputGigawords = Math::BigInt->new($row->{'AcctInputGigawords'});
				$inputGigawords->bmul(UINT_MAX);
				$usageTotals{$row->{'Username'}}{'TotalDataInput'}->badd($inputGigawords);
			}
			# Add output usage if we have any
			if (defined($row->{'AcctOutputOctets'}) && $row->{'AcctOutputOctets'} > 0) {
				$usageTotals{$row->{'Username'}}{'TotalDataOutput'}->badd($row->{'AcctOutputOctets'});
			}
			if (defined($row->{'AcctOutputGigawords'}) && $row->{'AcctOutputGigawords'} > 0) {
				my $outputGigawords = Math::BigInt->new($row->{'AcctOutputGigawords'});
				$outputGigawords->bmul(UINT_MAX);
				$usageTotals{$row->{'Username'}}{'TotalDataOutput'}->badd($outputGigawords);
			}

		}
	}

	$server->log(LOG_NOTICE,"[MOD_ACCOUNTING_SQL] Cleanup => Creating new accounting summaries");

	# Loop through users and insert totals
	foreach my $username (keys %usageTotals) {

		# Convert to bigfloat for accuracy
		my $totalDataOutput = Math::BigFloat->new($usageTotals{$username}{'TotalDataOutput'});
		my $totalDataInput = Math::BigFloat->new($usageTotals{$username}{'TotalDataInput'});
		my $totalTime = Math::BigFloat->new($usageTotals{$username}{'TotalSessionTime'});

		# Rounding up
		my $res;
		$res->{'TotalDataInput'} = $totalDataInput->bdiv(1024)->bdiv(1024)->bceil()->bstr();
		$res->{'TotalDataOutput'} = $totalDataOutput->bdiv(1024)->bdiv(1024)->bceil()->bstr();
		$res->{'TotalSessionTime'} = $totalTime->bdiv(60)->bceil()->bstr();

		# Do query
		$sth = DBDo('
			INSERT INTO
				@TP@accounting_summary
			(
				Username,
				PeriodKey,
				TotalSessionTime,
				TotalInput,
				TotalOutput
			)
			VALUES
				(?,?,?,?,?)
			',
			$username,
			$prevPeriodKey,
			$res->{'TotalSessionTime'},
			$res->{'TotalDataInput'},
			$res->{'TotalDataOutput'}
		);
		if (!$sth) {
			$server->log(LOG_ERR,"[MOD_ACCOUNTING_SQL] Cleanup => Failed to create accounting summary record: ".
					AWITPT::DB::DBLayer::error());
			DBRollback();
			return;
		}

		# Lets log
		$server->log(LOG_DEBUG,"[MOD_ACCOUNTING_SQL] Cleanup => INSERT: Username = '%s', PeriodKey = '%s', ".
				"TotalSessionTime = '%s', TotalInput = '%s', TotalOutput = '%s'", $username, $prevPeriodKey,
				$res->{'TotalSessionTime'}, $res->{'TotalDataInput'}, $res->{'TotalDataOutput'});
	}

	# Commit if succeeded
	DBCommit();
	$server->log(LOG_NOTICE,"[MOD_ACCOUNTING_SQL] Cleanup => Accounting summaries created");
}



1;
# vim: ts=4
