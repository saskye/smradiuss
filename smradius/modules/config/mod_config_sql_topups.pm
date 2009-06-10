# Topup support
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

package mod_config_sql_topups;

use strict;
use warnings;

# Modules we need
use smradius::constants;
use smradius::logging;
use smradius::dblayer;
use smradius::util;
use smradius::attributes;
	
use POSIX qw(ceil);
use DateTime;
use Date::Parse;



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
	Name => "SQL Topup Config",
	Init => \&init,

	# Cleanup run by smadmin
	Cleanup => \&cleanup,

	# User database
	Config_get => \&getTopups
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
	if (!$server->{'smradius'}->{'database'}->{'enabled'}) {
		$server->log(LOG_NOTICE,"[MOD_USERDB_SQL] Enabling database support.");
		$server->{'smradius'}->{'database'}->{'enabled'} = 1;
	}

	# Default configs...
	$config->{'get_topups_summary_query'} = '
		SELECT 
			@TP@topups_summary.Depleted,
			@TP@topups_summary.Balance,
			@TP@topups.Type
		FROM 
			@TP@topups_summary,
			@TP@topups,
			@TP@users
		WHERE
			@TP@topups.ID = @TP@topups_summary.TopupID
			AND @TP@topups.UserID = @TP@users.ID
			AND @TP@topups_summary.PeriodKey = ?
			AND @TP@topups.Depleted = 0
			AND @TP@users.Username = ?
	';

	$config->{'get_topups_query'} = '
		SELECT 
			@TP@topups.Type,
			@TP@topups.Value
		FROM 
			@TP@topups,
			@TP@users
		WHERE
			@TP@topups.UserID = @TP@users.ID
			AND @TP@topups.ValidFrom >= ?
			AND @TP@topups.ValidTo >= ?
			AND @TP@topups.Depleted = 0
			AND @TP@users.Username = ?
	';
	

	# Setup SQL queries
	if (defined($scfg->{'mod_topups_sql'})) {
		# Pull in queries
		if (defined($scfg->{'mod_topups_sql'}->{'get_topups_summary_query'}) &&
				$scfg->{'mod_topups_sql'}->{'get_topups_summary_query'} ne "") {
			if (ref($scfg->{'mod_topups_sql'}->{'get_topups_summary_query'}) eq "ARRAY") {
				$config->{'get_topups_summary_query'} = join(' ',@{$scfg->{'mod_config_sql'}->{'get_topups_summary_query'}});
			} else {
				$config->{'get_topups_summary_query'} = $scfg->{'mod_config_sql'}->{'get_topups_summary_query'};
			}
		}

		if (defined($scfg->{'mod_topups_sql'}->{'get_config_query'}) &&
				$scfg->{'mod_topups_sql'}->{'get_config_query'} ne "") {
			if (ref($scfg->{'mod_topups_sql'}->{'get_config_query'}) eq "ARRAY") {
				$config->{'get_config_query'} = join(' ',@{$scfg->{'mod_config_sql'}->{'get_config_query'}});
			} else {
				$config->{'get_config_query'} = $scfg->{'mod_config_sql'}->{'get_config_query'};
			}
			
		}
	}
}


## @getTopups
# Try to get topup information
#
# @param server Server object
# @param user User
# @param packet Radius packet
#
# @return Result
sub getTopups
{
	my ($server,$user,$packet) = @_;

	# Make time for month begin
	my $now = DateTime->from_epoch( epoch => $user->{'_Internal'}->{'Timestamp-Unix'} );
	my $thisMonth = DateTime->new( year => $now->year, month => $now->month, day => 1 );

	# Format period key
	my $periodKey = $thisMonth->strftime("%Y-%m");


	# Set up dbDoParams
	my @dbDoParams = ($config->{'get_topups_summary_query'},$periodKey,$packet->attr('User-Name'));

	# Query database
	my $sth = DBSelect(@dbDoParams);
	if (!$sth) {
		$server->log(LOG_ERR,"Failed to get topup information: ".smradius::dblayer::Error());
		return MOD_RES_NACK;
	}

	# Array of config items
	my (@trafficTopups, @uptimeTopups);

	# Fetch topups from summary
	while (my $row = $sth->fetchrow_hashref()) {
		if ($row->{'type'} == 1) {
			# Add traffic topup to ConfigAttributes
			push(@trafficTopups, $row->{'balance'});
		}
		if ($row->{'type'} == 2) {
			# Add uptime topup to ConfigAttributes
			push(@uptimeTopups, $row->{'balance'});
		}
	}
	DBFreeRes($sth);


	# Set up dbDoParams
	@dbDoParams = ($config->{'get_topups_query'},$thisMonth,$now,$packet->attr('User-Name'));

	# Query database
	$sth = DBSelect(@dbDoParams);
	if (!$sth) {
		$server->log(LOG_ERR,"Failed to get topup information: ".smradius::dblayer::Error());
		return MOD_RES_NACK;
	}

	# Fetch all other topups 
	while (my $row = $sth->fetchrow_hashref()) {
		if ($row->{'type'} == 1) {
			# Add traffic topup to array
			push(@trafficTopups, $row->{'value'});
		}
		if ($row->{'type'} == 2) {
			# Add uptime topup to array
			push(@uptimeTopups, $row->{'value'});
		}
	}
	DBFreeRes($sth);

	# Add up traffic topup
	my $totalTraffic = 0;
	foreach my $topupItem (@trafficTopups) {
		if (defined($topupItem) && $topupItem =~ /^[0-9]+$/) {
			$totalTraffic += $topupItem;
		}
	}

	# Add up uptime topup
	my $totalUptime = 0;
	foreach my $topupItem (@uptimeTopups) {
		if (defined($topupItem) && $topupItem =~ /^[0-9]+$/) {
			$totalUptime += $topupItem;
		}
	}

	# Process traffic topups
	processConfigAttribute($server,$user->{'ConfigAttributes'},{ 'Name' => 'SMRadius-Capping-Traffic-Topup', 
			'Operator' => ':=', 'Value' => $totalTraffic });

	# Process uptime topups
	processConfigAttribute($server,$user->{'ConfigAttributes'},{ 'Name' => 'SMRadius-Capping-Uptime-Topup', 
			'Operator' => ':=', 'Value' => $totalUptime });

	return MOD_RES_ACK;
}


# Topup summary function 
sub cleanup
{
	my ($server) = @_;
	my $sth;


	# TODO - be more dynamic, we may not be using SQL users
	# Get all usernames
	$sth = DBSelect('SELECT ID, Username FROM @TP@users');

	if (!$sth) {
		$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Failed to select from users: ".
				smradius::dblayer::Error());
		return;
	}

	# Create hash of usernames
	my %users;
	while (my $user = $sth->fetchrow_hashref()) {
		$users{$user->{'id'}} = $user->{'username'};
	}

	# Finished for now
	DBFreeRes($sth);

	# The datetime now
	my $now = DateTime->now;
	# Make datetime
	my $thisMonth = DateTime->new( year => $now->year, month => $now->month, day => 1 );

	# Get begin date of last month
	my ($prevYear,$prevMonth);
	if ($now->month == 1) {
		$prevYear = $now->year - 1;
		$prevMonth = 12;
	} else {
		$prevYear = $now->year;
		$prevMonth = $now->month - 1;
	}
	my $lastMonth = DateTime->new( year => $prevYear, month => $prevMonth, day => 1 );

	# Get begin date of next month
	my ($folYear,$folMonth);
	if ($now->month == 12) {
		$folYear = $now->year + 1;
		$folMonth = 1;
	} else {
		$folYear = $now->year;
		$folMonth = $now->month + 1;
	}
	my $nextMonth = DateTime->new( year => $folYear, month => $folMonth, day => 1 );
	my $unix_nextMonth = $nextMonth->epoch();

	# Start of multiple queries
	DBBegin();

	# Loop through users and check each topup, setting it to default if needed and updating summary
	foreach my $userID (keys %users) {
		my $userName = $users{$userID};

		# TODO - in future we must be more dynamic, we may not be using SQL accunting
		# Get current usage
		$sth = DBSelect('
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
				AND Username = ?
			GROUP BY
				Username
			',
			$lastMonth,$userName
		);

		if (!$sth) {
			$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Failed to select accounting records: ".
					smradius::dblayer::Error());
			goto FAIL_ROLLBACK;
		}

		# Pull data
		my $row = $sth->fetchrow_hashref();

		# Total up input
		my $totalData = 0; 
		if (defined($row->{'acctinputoctets'}) && $row->{'acctinputoctets'} > 0) {
			$totalData += $row->{'acctinputoctets'} / 1024 / 1024;
		}
		if (defined($row->{'acctinputgigawords'}) && $row->{'acctinputgigawords'} > 0) {
			$totalData += $row->{'acctinputgigawords'} * 4096;
		}
		# Add up output
		if (defined($row->{'acctoutputoctets'}) && $row->{'acctoutputoctets'} > 0) {
			$totalData += $row->{'acctoutputoctets'} / 1024 / 1024;
		}
		if (defined($row->{'acctoutputgigawords'}) && $row->{'acctoutputgigawords'} > 0) {
			$totalData += $row->{'acctoutputgigawords'} * 4096;
		}

		# Add up uptime
		my $totalTime = 0; 
		if (defined($row->{'acctsessiontime'}) && $row->{'acctsessiontime'} > 0) {
			$totalTime = $row->{'acctsessiontime'} / 60;
		}

		# Rounding up
		my %res;
		$res{'TotalTrafficUsage'} = ceil($totalData);
		$res{'TotalUptimeUsage'} = ceil($totalTime);

		# Finished for now
		DBFreeRes($sth);

		# TODO?
		# FIXME - support for groups and realm config?

		# Get users caps
		$sth = DBSelect('
			SELECT
					@TP@user_attributes.Name, @TP@user_attributes.Value
			FROM
					@TP@user_attributes, @TP@users
			WHERE
					@TP@users.Username = ?
			',
			$userName
		);

		if (!$sth) {
			$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Failed to select usage caps: ".
					smradius::dblayer::Error());
			goto FAIL_ROLLBACK;
		}

		# Add cap limits to capRecord hash
		my %capRecord;
		while ($row = $sth->fetchrow_hashref()) {
			if ($row->{'name'} eq 'SMRadius-Capping-Traffic-Limit') {
				$capRecord{'TrafficLimit'} = $row->{'value'};
			}
			if ($row->{'name'} eq 'SMRadius-Capping-Uptime-Limit') {
				$capRecord{'UptimeLimit'} = $row->{'value'};
			}
		}

		# Finished for now
		DBFreeRes($sth);

		# Set excess traffic / uptime used
		my $excessUptime = 0;
		my $excessTraffic = 0;
		if (defined($capRecord{'TrafficLimit'})) {
			$excessTraffic = $res{'TotalTrafficUsage'} - $capRecord{'TrafficLimit'};
		}
		if (defined($capRecord{'UptimeLimit'})) {
			$excessUptime = $res{'TotalUptimeUsage'} - $capRecord{'UptimeLimit'};
		}

		# Get users topups not depleted
		$sth = DBSelect('
			SELECT
				@TP@topups.ID, @TP@topups.Value, @TP@topups.Type, @TP@topups.ValidTo
			FROM
				@TP@topups, @TP@users
			WHERE
				@TP@topups.Depleted = 0
				AND @TP@topups.UserID = @TP@users.ID
				AND	@TP@users.Username = ?
				AND @TP@topups.ValidFrom <= ?
				AND @TP@topups.ValidTo >= ?
			ORDER BY
				@TP@topups.Timestamp
			',
			$userName,$lastMonth,$thisMonth
		);

		if (!$sth) {
			$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Failed to select topups: ".
					smradius::dblayer::Error());
			goto FAIL_ROLLBACK;
		}

		# Loop with the topups and push them into arrays
		my @trafficTopups = ();
		my @uptimeTopups = ();
		while (my $row = $sth->fetchrow_hashref()) {
			# Convert string to unix time
			my $unix_validto = str2time($row->{'validto'});
			# If this is a traffic topup ...
			if ($row->{'type'} == 1) {
				push(@trafficTopups, { 
					ID => $row->{'id'}, 
					Value => $row->{'value'},
					ValidTo => $unix_validto
				});

			# Or a uptime topup...
			} elsif ($row->{'type'} == 2) {
				push(@uptimeTopups, {
					ID => $row->{'id'}, 
					Value => $row->{'value'},
					ValidTo => $unix_validto
				});
			}
		}

		# Finished for now
		DBFreeRes($sth);

		# List of topups to be set depleted
		my @depletedTopups = ();

		# Working with traffic
		if (defined($excessTraffic) && $excessTraffic gt 0) {
			foreach my $topup (@trafficTopups) {
				# Topup depleted
				if ($topup->{'Value'} < $excessTraffic) {
					# Add topup ID to depleted list
					push(@depletedTopups, $topup->{'ID'});
					# Excess traffic remaining
					$excessTraffic -= $topup->{'Value'};

				# Topup still alive
				} else {
					my $trafficRemaining = $topup->{'Value'} - $excessTraffic;

					if ($topup->{'ValidTo'} >= $unix_nextMonth) {

						# Format period key
						my $periodKey = $thisMonth->strftime("%Y-%m");

						# Set users depleted topups
						$sth = DBDo('
							INSERT INTO
								@TP@topups_summary (TopupID,PeriodKey,Balance)
							VALUES
								(?,?,?)
							',
							$topup->{'ID'},$periodKey,$trafficRemaining
						);
						if (!$sth) {
							$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Failed to update topup summary: ".
									smradius::dblayer::Error());
							goto FAIL_ROLLBACK;
						}
					}

					# We dont want to continue if a non depleted topup found
					last;
				}
			}
		}

		# Working with uptime
		if (defined($excessUptime) && $excessUptime gt 0) {
			foreach my $topup (@uptimeTopups) {
				# Topup depleted
				if ($topup->{'Value'} < $excessUptime) {
					# Add topup ID to depleted list
					push(@depletedTopups, $topup->{'ID'});
					# Excess uptime remaining
					$excessUptime -= $topup->{'Value'};

				# Topup still alive
				} else {
					my $uptimeRemaining = $topup->{'Value'} - $excessUptime;

					if ($topup->{'ValidTo'} >= $unix_nextMonth) {

						# Format period key
						my $periodKey = $thisMonth->strftime("%Y-%m");

						# Set users depleted topups
						$sth = DBDo('
							INSERT INTO
								@TP@topups_summary (TopupID,PeriodKey,Balance)
							VALUES
								(?,?,?)
							',
							$topup->{'ID'},$periodKey,$uptimeRemaining
						);
						if (!$sth) {
							$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Failed to update topups: ".
									smradius::dblayer::Error());
							goto FAIL_ROLLBACK;
						}
					}

					# We dont want to continue if a non depleted topup found
					last;
				}
			}
		}

		# Loop through topups that are depleted
		my $topupID;
		foreach $topupID (@depletedTopups) {
			# Set users depleted topups
			$sth = DBSelect('
				UPDATE
					@TP@topups
				SET
					Depleted = 1
				WHERE
					ID = ? 
				',
				$topupID
			);
			if (!$sth) {
				$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Failed to update topups: ".
						smradius::dblayer::Error());
				goto FAIL_ROLLBACK;
			}
		}
	}

	# Finished
	$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Topup summaries have been updated");
	DBCommit();
	return;

FAIL_ROLLBACK:
	DBRollback();
	$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Database has been rolled back, no records updated");
	return;
}


1;
# vim: ts=4
