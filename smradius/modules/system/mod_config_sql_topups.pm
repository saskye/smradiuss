# Topup support
# Copyright (C) 2007-2011, AllWorldIT
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

package smradius::modules::system::mod_config_sql_topups;

use strict;
use warnings;

# Modules we need
use smradius::constants;
use smradius::logging;
use awitpt::db::dblayer;
use smradius::util;
use smradius::attributes;

use POSIX qw(ceil strftime);
use DateTime;
use Date::Parse;
use Math::BigInt;
use Math::BigFloat;



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
	CleanupOrder => 80,
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
			@TP@topups_summary.Balance,
			@TP@topups.Type,
			@TP@topups.ID
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
			@TP@topups.ID,
			@TP@topups.Type,
			@TP@topups.Value
		FROM
			@TP@topups,
			@TP@users
		WHERE
			@TP@topups.UserID = @TP@users.ID
			AND @TP@topups.ValidFrom = ?
			AND @TP@topups.ValidTo >= ?
			AND @TP@topups.Depleted = 0
			AND @TP@users.Username = ?
	';
	

	# Setup SQL queries
	if (defined($scfg->{'mod_config_sql_topups'})) {
		# Pull in queries
		if (defined($scfg->{'mod_config_sql_topups'}->{'get_topups_summary_query'}) &&
				$scfg->{'mod_config_sql_topups'}->{'get_topups_summary_query'} ne "") {
			if (ref($scfg->{'mod_config_sql_topups'}->{'get_topups_summary_query'}) eq "ARRAY") {
				$config->{'get_topups_summary_query'} = join(' ',@{$scfg->{'mod_config_sql_topups'}->{'get_topups_summary_query'}});
			} else {
				$config->{'get_topups_summary_query'} = $scfg->{'mod_config_sql_topups'}->{'get_topups_summary_query'};
			}
		}

		if (defined($scfg->{'mod_config_sql_topups'}->{'get_topups_query'}) &&
				$scfg->{'mod_config_sql_topups'}->{'get_topups_query'} ne "") {
			if (ref($scfg->{'mod_config_sql_topups'}->{'get_topups_query'}) eq "ARRAY") {
				$config->{'get_topups_query'} = join(' ',@{$scfg->{'mod_config_sql_topups'}->{'get_topups_query'}});
			} else {
				$config->{'get_topups_query'} = $scfg->{'mod_config_sql_topups'}->{'get_topups_query'};
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


	# Check to see if we have a username
	my $username = $user->{'Username'};

	# Skip this module if we don't have a username
	if (!defined($username)) {
		return MOD_RES_SKIP;
	}

	# Make time for month begin
	my $now = DateTime->from_epoch( epoch => $user->{'_Internal'}->{'Timestamp-Unix'} );
	my $thisMonth = DateTime->new( year => $now->year, month => $now->month, day => 1 );

	# Format period key
	my $periodKey = $thisMonth->strftime("%Y-%m");

	# Query database
	my $sth = DBSelect($config->{'get_topups_summary_query'},$periodKey,$username);
	if (!$sth) {
		$server->log(LOG_ERR,"Failed to get topup information: ".awitpt::db::dblayer::Error());
		return MOD_RES_NACK;
	}

	# Fetch all summaries
	my (@trafficSummary,@uptimeSummary);
	while (my $row = hashifyLCtoMC($sth->fetchrow_hashref(), qw(Balance Type ID))) {
		if ($row->{'Type'} == 1) {
			# Add to traffic summary list
			push(@trafficSummary, { Value => $row->{'Balance'}, ID => $row->{'ID'} });
		}
		if ($row->{'Type'} == 2) {
			# Add to uptime summary list
			push(@uptimeSummary, { Value => $row->{'Balance'}, ID => $row->{'ID'} });
		}
	}
	DBFreeRes($sth);

	# Query database
	$sth = DBSelect($config->{'get_topups_query'},$thisMonth->ymd,$now->ymd,$username);
	if (!$sth) {
		$server->log(LOG_ERR,"Failed to get topup information: ".awitpt::db::dblayer::Error());
		return MOD_RES_NACK;
	}

	# Fetch all new topups 
	my (@trafficTopups,@uptimeTopups);
	while (my $row = hashifyLCtoMC($sth->fetchrow_hashref(), qw(ID Type Value))) {
		if ($row->{'Type'} == 1) {
			# Add topup to traffic array
			push(@trafficTopups, { Value => $row->{'Value'}, ID => $row->{'ID'} });
		}
		if ($row->{'Type'} == 2) {
			# Add topup to uptime array
			push(@uptimeTopups, { Value => $row->{'Value'}, ID => $row->{'ID'} });
		}
	}

	DBFreeRes($sth);

	# Add up traffic
	my $totalTopupTraffic = 0;
	# Traffic topups..
	foreach my $topup (@trafficTopups) {
		# Use only if numeric
		if ($topup->{'Value'} =~ /^[0-9]+$/) {
			$totalTopupTraffic += $topup->{'Value'};
		} else {
			$server->log(LOG_DEBUG,"[MOD_CONFIG_SQL_TOPUPS] Topup with ID '".niceUndef($topup->{'ID'}).
					"' is not a numeric value");
			return MOD_RES_NACK;
		}
	}
	# Traffic summaries..
	foreach my $summary (@trafficSummary) {
		# Use only if numeric
		if ($summary->{'Value'} =~ /^[0-9]+$/) {
			$totalTopupTraffic += $summary->{'Value'};
		} else {
			$server->log(LOG_DEBUG,"[MOD_CONFIG_SQL_TOPUPS] Topup with ID '".niceUndef($summary->{'ID'}).
					"' is not a numeric value");
			return MOD_RES_NACK;
		}
	}

	# Add up uptime
	my $totalTopupUptime = 0;
	# Uptime topups..
	foreach my $topup (@uptimeTopups) {
		# Use only if numeric
		if ($topup->{'Value'} =~ /^[0-9]+$/) {
			$totalTopupUptime += $topup->{'Value'};
		} else {
			$server->log(LOG_DEBUG,"[MOD_CONFIG_SQL_TOPUPS] Topup with ID '".niceUndef($topup->{'ID'}).
					"' is not a numeric value");
			return MOD_RES_NACK;
		}
	}
	# Uptime summaries..
	foreach my $summary (@uptimeSummary) {
		# Use only if numeric
		if ($summary->{'Value'} =~ /^[0-9]+$/) {
			$totalTopupUptime += $summary->{'Value'};
		} else {
			$server->log(LOG_DEBUG,"[MOD_CONFIG_SQL_TOPUPS] Topup with ID '".niceUndef($summary->{'ID'}).
					"' is not a numeric value");
			return MOD_RES_NACK;
		}
	}

	# Process traffic topups
	processConfigAttribute($server,$user,{ 'Name' => 'SMRadius-Capping-Traffic-Topup',
			'Operator' => ':=', 'Value' => $totalTopupTraffic });

	# Process uptime topups
	processConfigAttribute($server,$user,{ 'Name' => 'SMRadius-Capping-Uptime-Topup',
			'Operator' => ':=', 'Value' => $totalTopupUptime });

	return MOD_RES_ACK;
}


# Topup summary function
sub cleanup
{
	my ($server,$runForDate) = @_;

	# The datetime now
	my $now = DateTime->from_epoch(epoch => $runForDate)->set_time_zone($server->{'smradius'}->{'event_timezone'});

	# Use truncate to set all values after 'month' to their default values
	my $thisMonth = $now->clone()->truncate( to => "month" );
	# Format this month period key
	my $curPeriodKey = $thisMonth->strftime("%Y-%m");

	# Last month..
	my $lastMonth = $thisMonth->clone()->subtract( months => 1 );
	my $prevPeriodKey = $lastMonth->strftime("%Y-%m");

	# Next month..
	my $nextMonth = $thisMonth->clone()->add( months => 1 );
	my $unix_nextMonth = $nextMonth->epoch();

	# Get a timestamp for this user
	my $depletedTimestamp = $now->strftime('%Y-%m-%d %H:%M:%S');



	$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Generating list of users");

	# TODO - be more dynamic, we may not be using SQL users
	# Get all usernames
	my $sth = DBSelect('SELECT ID, Username FROM @TP@users');

	if (!$sth) {
		$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Failed to select users: ".
				awitpt::db::dblayer::Error());
		return;
	}

	# Create hash of usernames
	my %users;
	while (my $user = hashifyLCtoMC($sth->fetchrow_hashref(), qw(ID Username))) {
		$users{$user->{'ID'}} = $user->{'Username'};
	}

	# Finished for now
	DBFreeRes($sth);
	
	# Start of multiple queries
	DBBegin();

	$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Removing all old topup summaries");

	# Remove topup summaries
	# NK: MYSQL SPECIFIC
	$sth = DBDo('
		DELETE FROM
			@TP@topups_summary
		WHERE
			STR_TO_DATE(PeriodKey,"%Y-%m") >= ?',
		$curPeriodKey
	);
	if (!$sth) {
		$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Failed to delete topup summaries: ".
				awitpt::db::dblayer::Error());
		DBRollback();
		return;
	}

	# Undeplete topups
	$sth = DBDo('
		UPDATE
			@TP@topups
		SET
			Depleted = 0,
			SMAdminDepletedOn = NULL
		WHERE
			SMAdminDepletedOn >= ?', $thisMonth->ymd()
	);
	if (!$sth) {
		$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Failed to undeplete topups: ".
				awitpt::db::dblayer::Error());
		DBRollback();
		return;
	}

	$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Retrieving accounting summaries");

	# Undeplete topup summaries
	$sth = DBDo('
		UPDATE
			@TP@topups_summary
		SET
			Depleted = 0,
			SMAdminDepletedOn = NULL
		WHERE
			SMAdminDepletedOn >= ?', $thisMonth->ymd()
	);
	if (!$sth) {
		$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Failed to retrieve accounting summaries: ".
				awitpt::db::dblayer::Error());
		DBRollback();
		return;
	}

	# Loop through users
	foreach my $userID (keys %users) {
		my $username = $users{$userID};

		# TODO - in future we must be more dynamic, we may not be using SQL accunting

		# Get traffic and uptime usage for last month
		my $sth = DBSelect('
			SELECT
				TotalInput,
				TotalOutput,
				TotalSessionTime
			FROM
				@TP@accounting_summary
			WHERE
				PeriodKey = ?
				AND Username = ?
			',
			$prevPeriodKey,$username
		);
		if (!$sth) {
			$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Failed to select accounting summary record: ".
					awitpt::db::dblayer::Error());
			goto FAIL_ROLLBACK;
		}

		# Our usage hash
		my %usageTotals;
		$usageTotals{'TotalSessionTime'} = Math::BigInt->new();
		$usageTotals{'TotalDataUsage'} = Math::BigInt->new();

		# Pull in usage and add up
		if (my $row = hashifyLCtoMC($sth->fetchrow_hashref(),
				qw(TotalSessionTime TotalInput TotalOutput)
		)) {

			# Look for session time
			if (defined($row->{'TotalSessionTime'}) && $row->{'TotalSessionTime'} > 0) {
				$usageTotals{'TotalSessionTime'}->badd($row->{'TotalSessionTime'});
			}
			# Add input usage if we have any
			if (defined($row->{'TotalInput'}) && $row->{'TotalInput'} > 0) {
				$usageTotals{'TotalDataUsage'}->badd($row->{'TotalInput'});
			}
			# Add output usage if we have any
			if (defined($row->{'TotalOutput'}) && $row->{'TotalOutput'} > 0) {
				$usageTotals{'TotalDataUsage'}->badd($row->{'TotalOutput'});
			}
		}
		DBFreeRes($sth);

		# Log the summary	
		$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Username '%s', PeriodKey '%s', TotalSessionTime '%s', ".
				" TotalDataUsage '%s'",$username,$prevPeriodKey,$usageTotals{'TotalSessionTime'}->bstr(),
				$usageTotals{'TotalDataUsage'}->bstr(),	$usageTotals{'TotalDataUsage'}->bstr());

		# Get user traffic and uptime limits from group attributes
		# FIXME - Support for realm config
		$sth = DBSelect('
			SELECT
				@TP@group_attributes.Name, @TP@group_attributes.Operator, @TP@group_attributes.Value
			FROM
				@TP@group_attributes, @TP@users_to_groups
			WHERE
				@TP@group_attributes.GroupID = @TP@users_to_groups.GroupID
				AND @TP@users_to_groups.UserID = ?
			',
			$userID
		);

		if (!$sth) {
			$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Failed to select group usage caps: ".
					awitpt::db::dblayer::Error());
			goto FAIL_ROLLBACK;
		}

		# Store limits in capRecord hash
		my %capRecord;
		while (my $row = hashifyLCtoMC($sth->fetchrow_hashref(), qw(Name Operator Value))) {

			if (defined($row->{'Name'})) {
				if ($row->{'Name'} eq 'SMRadius-Capping-Traffic-Limit') {
					if (defined($row->{'Operator'}) && $row->{'Operator'} eq ':=') {
						if (defined($row->{'Value'}) && $row->{'Value'} =~ /^[\d]+$/) {
							$capRecord{'TrafficLimit'} = $row->{'Value'};
						} else {
							$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => SMRadius-Capping-Traffic-Limit ".
									"value invalid for user '".$username."'");
						}
					} else {
						$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Incorrect '".$row->{'Name'}."' operator '"
								.$row->{'Operator'}."' used  for user '".$username."'");
					}
				}
				if ($row->{'Name'} eq 'SMRadius-Capping-Uptime-Limit') {
					if (defined($row->{'Operator'}) && $row->{'Operator'} eq ':=') {
						if (defined($row->{'Value'}) && $row->{'Value'} =~ /^[\d]+$/) {
							$capRecord{'UptimeLimit'} = $row->{'Value'};
						} else {
							$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => SMRadius-Capping-Uptime-Limit value ".
							"invalid for user '".$username."'");
						}
					} else {
						$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Incorrect '".$row->{'Name'}."' operator '"
								.$row->{'Operator'}."' used  for user '".$username."'");
					}
				}
			}
		}

		# Finished for now
		DBFreeRes($sth);

		# Get user traffic and uptime limits from user attributes
		$sth = DBSelect('
			SELECT
				Name, Operator, Value
			FROM
				@TP@user_attributes
			WHERE
				UserID = ?
			',
			$userID
		);

		if (!$sth) {
			$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Failed to select user usage caps: ".
					awitpt::db::dblayer::Error());
			goto FAIL_ROLLBACK;
		}

		# Store limits in capRecord hash
		while (my $row = hashifyLCtoMC($sth->fetchrow_hashref(), qw(Name Operator Value))) {

			if (defined($row->{'Name'})) {
				if ($row->{'Name'} eq 'SMRadius-Capping-Traffic-Limit') {
					if (defined($row->{'Operator'}) && $row->{'Operator'} eq ':=') {
						if (defined($row->{'Value'}) && $row->{'Value'} =~ /^[\d]+$/) {
							$capRecord{'TrafficLimit'} = $row->{'Value'};
						} else {
							$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => SMRadius-Capping-Traffic-Limit value ".
									"invalid for user '".$username."'");
						}
					} else {
						$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Incorrect '".$row->{'Name'}."' operator '"
								.$row->{'Operator'}."' used  for user '".$username."'");
					}
				}
				if ($row->{'Name'} eq 'SMRadius-Capping-Uptime-Limit') {
					if (defined($row->{'Operator'}) && $row->{'Operator'} eq ':=') {
						if (defined($row->{'Value'}) && $row->{'Value'} =~ /^[\d]+$/) {
							$capRecord{'UptimeLimit'} = $row->{'Value'};
						} else {
							$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => SMRadius-Capping-Uptime-Limit value ".
									"invalid for user '".$username."'");
						}
					} else {
						$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Incorrect '".$row->{'Name'}."' operator '"
								.$row->{'Operator'}."' used  for user '".$username."'");
					}
				}
			}
		}

		# Finished for now
		DBFreeRes($sth);

		$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     CAP: ".
				"SMRadius-Capping-Traffic-Limit '%s', SMRadius-Capping-Uptime-Limit '%s'",
				$capRecord{'TrafficLimit'} ? $capRecord{'TrafficLimit'} : "-",
				$capRecord{'UptimeLimit'} ? $capRecord{'TrafficLimit'} : "-"
		);

		# Get users topups that are still valid from topups_summary, must not be depleted
		$sth = DBSelect('
			SELECT
				@TP@topups_summary.TopupID,
				@TP@topups_summary.Balance,
				@TP@topups.ValidTo,
				@TP@topups.Type
			FROM
				@TP@topups_summary, @TP@topups
			WHERE
				@TP@topups_summary.Depleted = 0
				AND @TP@topups.Depleted = 0
				AND @TP@topups_summary.TopupID = @TP@topups.ID
				AND @TP@topups.UserID = ?
				AND @TP@topups_summary.PeriodKey = ?
			ORDER BY
				@TP@topups.Timestamp
			',
			$userID, $prevPeriodKey
		);

		if (!$sth) {
			$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Failed to select topup summaries: ".
					awitpt::db::dblayer::Error());
			goto FAIL_ROLLBACK;
		}


		# Add previous valid topups to lists
		my @trafficSummary = ();
		my @uptimeSummary = ();
		while (my $row = hashifyLCtoMC($sth->fetchrow_hashref(), qw(TopupID Balance Value ValidTo Type))) {

			if (defined($row->{'ValidTo'})) {
				# Convert string to unix time
				my $unix_validTo = str2time($row->{'ValidTo'});
				# Process traffic topup
				if ($row->{'Type'} == 1) {
					push(@trafficSummary, { 
							TopupID => $row->{'TopupID'},
							Balance => $row->{'Balance'},
							ValidTo => $unix_validTo,
							Type => $row->{'Type'}
					});

					$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     TRAFFIC SUMMARY TOPUP: ".
							"ID '%s', Balance '%s', ValidTo '%s'",
							$row->{'TopupID'},
							$row->{'Balance'},
							DateTime->from_epoch(epoch => $unix_validTo)->strftime("%F")
					);

				# Process uptime topup
				} elsif ($row->{'Type'} == 2) {
					push(@uptimeSummary, { 
							TopupID => $row->{'TopupID'},
							Balance => $row->{'Balance'},
							ValidTo => $unix_validTo,
							Type => $row->{'Type'}
					});

					$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     UPTIME SUMMARY TOPUP: ".
							"ID '%s', Balance '%s', ValidTo '%s'",
							$$row->{'TopupID'},
							$row->{'Balance'},
							DateTime->from_epoch(epoch => $unix_validTo)->strftime("%F")
					);
				}
			}
		}

		# Finished for now
		DBFreeRes($sth);


		# Get topups from last month
		$sth = DBSelect('
			SELECT
				ID, Value, Type, ValidTo
			FROM
				@TP@topups
			WHERE
				Depleted = 0
				AND UserID = ?
				AND ValidFrom = ?
				AND ValidTo >= ?
			ORDER BY
				Timestamp
			',
			$userID,$lastMonth,$thisMonth
		);

		if (!$sth) {
			$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Failed to select topups: ".
					awitpt::db::dblayer::Error());
			goto FAIL_ROLLBACK;
		}

		# Loop with the topups and push them into arrays
		my (@trafficTopups,@uptimeTopups);
		while (my $row = hashifyLCtoMC($sth->fetchrow_hashref(), qw(ID Value Type ValidTo))) {

			# Convert string to unix time
			my $unix_validTo = str2time($row->{'ValidTo'});
			# If this is a traffic topup ...
			if ($row->{'Type'} == 1) {
				push(@trafficTopups, {
					ID => $row->{'ID'},
					Value => $row->{'Value'},
					ValidTo => $unix_validTo
				});

				$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     TRAFFIC TOPUP: ".
						"ID '%s', Balance '%s', ValidTo '%s'",
						$row->{'ID'},
						$row->{'Value'},
						DateTime->from_epoch(epoch => $unix_validTo)->strftime("%F")
				);

			# Or a uptime topup...
			} elsif ($row->{'Type'} == 2) {
				push(@uptimeTopups, {
					ID => $row->{'ID'},
					Value => $row->{'Value'},
					ValidTo => $unix_validTo
				});

				$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     UPTIME TOPUP: ".
						"ID '%s', Balance '%s', ValidTo '%s'",
						$row->{'ID'},
						$row->{'Value'},
						DateTime->from_epoch(epoch => $unix_validTo)->strftime("%F")
				);
			}
		}

		# Finished for now
		DBFreeRes($sth);

		# List of summaries depleted
		my @depletedSummary = ();
		my @depletedTopups = ();

		# Summaries to be edited/repeated
		my @summaryTopups = ();

		# Calculate excess usage if necessary
		my $trafficOverUsage = 0;
		if (defined($capRecord{'TrafficLimit'}) && $capRecord{'TrafficLimit'} > 0) {
			$trafficOverUsage = $usageTotals{'TotalDataUsage'} - $capRecord{'TrafficLimit'};
		} elsif (!(defined($capRecord{'TrafficLimit'}))) {
			$trafficOverUsage = $usageTotals{'TotalDataUsage'};
		}

		# User has started using topup bandwidth..
		if ($trafficOverUsage > 0) {
			$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     TRAFFIC OVERAGE: $trafficOverUsage");

			# Sort topups first expiring first
			my @sortedTrafficSummary = sort { $a->{'ValidTo'} cmp $b->{'ValidTo'} } @trafficSummary;

			# Loop with previous topups, setting them depleted or repeating as necessary
			foreach my $summaryItem (@sortedTrafficSummary) {

				# Summary has not been used, if valid add to list to be repeated
				if ($trafficOverUsage <= 0 && $summaryItem->{'ValidTo'} >= $unix_nextMonth) {
					push(@summaryTopups, {
							ID => $summaryItem->{'TopupID'},
							PeriodKey => $curPeriodKey,
							Balance => $summaryItem->{'Balance'}
					});

					$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     TRAFFIC SUMMARY UNUSED: ".
							"TOPUPID '%s', Balance '%s'",
							$summaryItem->{'TopupID'},
							$summaryItem->{'Balance'},
					);

				# Topup summary depleted
				} elsif ($summaryItem->{'Balance'} <= $trafficOverUsage) {
					push(@depletedSummary, $summaryItem->{'TopupID'});
					push(@depletedTopups, $summaryItem->{'TopupID'});

					# Excess traffic remaining
					$trafficOverUsage -= $summaryItem->{'Balance'};

					$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     TRAFFIC SUMMARY DEPLETED: ".
							"TOPUPID '%s', Balance '%s', Overage Left '%s'",
							$summaryItem->{'TopupID'},
							$summaryItem->{'Balance'},
							$trafficOverUsage
					);

				# Topup summary still alive
				} else {
					my $trafficRemaining = $summaryItem->{'Balance'} - $trafficOverUsage;
					if ($summaryItem->{'ValidTo'} >= $unix_nextMonth) {
						push(@summaryTopups, {
								ID => $summaryItem->{'TopupID'},
								PeriodKey => $curPeriodKey,
								Balance => $trafficRemaining
						});
					}

					$trafficOverUsage = 0;

					$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     TRAFFIC SUMMARY USAGE: ".
							"TOPUPID '%s', Balance '%s', Overage Left '%s'",
							$summaryItem->{'TopupID'},
							$trafficRemaining,
							$trafficOverUsage
					);
				}
			}

			# Sort topups first expiring first
			my @sortedTrafficTopups = sort { $a->{'ValidTo'} cmp $b->{'ValidTo'} } @trafficTopups;

			# Loop with topups, setting them depleted or adding summary as necessary
			foreach my $topup (@sortedTrafficTopups) {

				# Topup has not been used, if valid add to summary
				if ($trafficOverUsage <= 0 && $topup->{'ValidTo'} >= $unix_nextMonth) {
					push(@summaryTopups, {
							ID => $topup->{'ID'},
							PeriodKey => $curPeriodKey,
							Balance => $topup->{'Value'}
					});

					$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     TRAFFIC TOPUP UNUSED: ".
							"TOPUPID '%s', Balance '%s'",
							$topup->{'ID'},
							$topup->{'Value'}
					);

				# Topup depleted
				} elsif ($topup->{'Value'} <= $trafficOverUsage) {
					push(@depletedTopups, $topup->{'ID'});

					# Excess traffic remaining
					$trafficOverUsage -= $topup->{'Value'};

					$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     TRAFFIC TOPUP DEPLETED: ".
							"TOPUPID '%s', Balance '%s', Overage Left '%s'",
							$topup->{'ID'},
							$topup->{'Value'},
							$trafficOverUsage
					);

				# Topup still alive
				} else {
					# Check if this summary exists in the list
					my $trafficRemaining = $topup->{'Value'} - $trafficOverUsage;

					if ($topup->{'ValidTo'} >= $unix_nextMonth) {
						push(@summaryTopups, {
								ID => $topup->{'ID'},
								PeriodKey => $curPeriodKey,
								Balance => $trafficRemaining
						});
					}

					$trafficOverUsage = 0;

					$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     TRAFFIC TOPUP USAGE: ".
							"TOPUPID '%s', Balance '%s', Overage Left '%s'",
							$topup->{'ID'},
							$trafficRemaining,
							$trafficOverUsage
					);
				}
			}

		# User has not used up cap but may have topups to carry over
		} else {
			# Check for summaries
			foreach my $summaryItem (@trafficSummary) {
				# Add summary
				if ($summaryItem->{'ValidTo'} >= $unix_nextMonth) {
					push(@summaryTopups, {
							ID => $summaryItem->{'TopupID'},
							PeriodKey => $curPeriodKey,
							Balance => $summaryItem->{'Balance'}
					});

					$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     TRAFFIC SUMMARY CARRY: ".
							"TOPUPID '%s', Balance '%s'",
							$summaryItem->{'TopupID'},
							$summaryItem->{'Balance'}
					);
				}
			}
			# Check for topups
			foreach my $topup (@trafficTopups) {
				# Add to summaries
				if ($topup->{'ValidTo'} >= $unix_nextMonth) {
					push(@summaryTopups, {
							ID => $topup->{'ID'},
							PeriodKey => $curPeriodKey,
							Balance => $topup->{'Value'}
					});

					$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     TRAFFIC TOPUP CARRY: ".
							"TOPUPID '%s', Balance '%s'",
							$topup->{'ID'},
							$topup->{'Value'}
					);
				}
			}
		}


		# Calculate excess usage if necessary
		my $uptimeOverUsage = 0;
		if (defined($capRecord{'UptimeLimit'}) && $capRecord{'UptimeLimit'} > 0) {
			$uptimeOverUsage = $usageTotals{'TotalSessionTime'} - $capRecord{'UptimeLimit'};
		} elsif (!(defined($capRecord{'UptimeLimit'}))) {
			$uptimeOverUsage = $usageTotals{'TotalSessionTime'};
		}

		# User has started using topup uptime..
		if ($uptimeOverUsage > 0) {
			$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     UPTIME OVERAGE: $uptimeOverUsage");
			
			# Sort topups first expiring first
			my @sortedUptimeSummary = sort { $a->{'ValidTo'} cmp $b->{'ValidTo'} } @uptimeSummary;

			# Loop with previous topups, setting them depleted or repeating as necessary
			foreach my $summaryItem (@sortedUptimeSummary) {
				# Summary has not been used, if valid add to list to be repeated
				if ($uptimeOverUsage <= 0 && $summaryItem->{'ValidTo'} >= $unix_nextMonth) {
					push(@summaryTopups, {
							ID => $summaryItem->{'TopupID'},
							PeriodKey => $curPeriodKey,
							Balance => $summaryItem->{'Balance'}
					});

					$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     UPTIME SUMMARY UNUSED: ".
							"TOPUPID '%s', Balance '%s'",
							$summaryItem->{'TopupID'},
							$summaryItem->{'Balance'},
					);

				# Topup summary depleted
				} elsif ($summaryItem->{'Balance'} <= $uptimeOverUsage) {
					push(@depletedSummary, $summaryItem->{'TopupID'});
					push(@depletedTopups, $summaryItem->{'TopupID'});

					# Excess uptime remaining
					$uptimeOverUsage -= $summaryItem->{'Balance'};

					$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     UPTIME SUMMARY DEPLETED: ".
							"TOPUPID '%s', Balance '%s', Overage Left '%s'",
							$summaryItem->{'TopupID'},
							$summaryItem->{'Balance'},
							$uptimeOverUsage
					);

				# Topup summary still alive
				} else {
					my $uptimeRemaining = $summaryItem->{'Balance'} - $uptimeOverUsage;
					if ($summaryItem->{'ValidTo'} >= $unix_nextMonth) {
						push(@summaryTopups, {
								ID => $summaryItem->{'TopupID'},
								PeriodKey => $curPeriodKey,
								Balance => $uptimeRemaining
						});
					}

					$uptimeOverUsage = 0;

					$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     UPTIME SUMMARY USAGE: ".
							"TOPUPID '%s', Balance '%s', Overage Left '%s'",
							$summaryItem->{'TopupID'},
							$uptimeRemaining,
							$uptimeOverUsage
					);
				}
			}

			# Sort topups first expiring first
			my @sortedUptimeTopups = sort { $a->{'ValidTo'} cmp $b->{'ValidTo'} } @uptimeTopups;

			# Loop with topups, setting them depleted or adding summary as necessary
			foreach my $topup (@sortedUptimeTopups) {
				# Topup has not been used, if valid add to summary
				if ($uptimeOverUsage <= 0 && $topup->{'ValidTo'} >= $unix_nextMonth) {
					push(@summaryTopups, {
							ID => $topup->{'ID'},
							PeriodKey => $curPeriodKey,
							Balance => $topup->{'Value'}
					});

					$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     UPTIME TOPUP UNUSED: ".
							"TOPUPID '%s', Balance '%s'",
							$topup->{'ID'},
							$topup->{'Value'}
					);

				# Topup depleted
				} elsif ($topup->{'Value'} <= $uptimeOverUsage) {
					push(@depletedTopups, $topup->{'ID'});
					# Excess uptime remaining
					$uptimeOverUsage -= $topup->{'Value'};

					$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     UPTIME TOPUP DEPLETED: ".
							"TOPUPID '%s', Balance '%s', Overage Left '%s'",
							$topup->{'ID'},
							$topup->{'Value'},
							$uptimeOverUsage
					);

				# Topup still alive
				} else {
					my $uptimeRemaining = $topup->{'Value'} - $uptimeOverUsage;
					if ($topup->{'ValidTo'} >= $unix_nextMonth) {
						push(@summaryTopups, {
								ID => $topup->{'ID'},
								PeriodKey => $curPeriodKey,
								Balance => $uptimeRemaining
						});
					}

					$uptimeOverUsage = 0;

					$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     UPTIME TOPUP USAGE: ".
							"TOPUPID '%s', Balance '%s', Overage Left '%s'",
							$topup->{'ID'},
							$uptimeRemaining,
							$uptimeOverUsage
					);
				}
			}

		# User has not used up cap but may have topups to carry over
		} else {
			# Check for summaries
			foreach my $summaryItem (@uptimeSummary) {
				# Add summary
				if ($summaryItem->{'ValidTo'} >= $unix_nextMonth) {
					push(@summaryTopups, {
							ID => $summaryItem->{'TopupID'},
							PeriodKey => $curPeriodKey,
							Balance => $summaryItem->{'Balance'}
					});

					$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     UPTIME SUMMARY CARRY: ".
							"TOPUPID '%s', Balance '%s'",
							$summaryItem->{'TopupID'},
							$summaryItem->{'Balance'}
					);
				}
			}
			# Check for topups
			foreach my $topup (@uptimeTopups) {
				# Check if summary exists
				if ($topup->{'ValidTo'} >= $unix_nextMonth) {
					push(@summaryTopups, {
							ID => $topup->{'ID'},
							PeriodKey => $curPeriodKey,
							Balance => $topup->{'Value'}
					});

					$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     UPTIME TOPUP CARRY: ".
							"TOPUPID '%s', Balance '%s'",
							$topup->{'ID'},
							$topup->{'Value'}
					);
				}
			}
		}

		# Loop through summary topups
		foreach my $summaryTopup (@summaryTopups) {
			# Create topup summaries
			$sth = DBDo('
				INSERT INTO
					@TP@topups_summary (TopupID,PeriodKey,Balance)
				VALUES
					(?,?,?)
				',
				$summaryTopup->{'ID'},$curPeriodKey,$summaryTopup->{'Balance'}
			);

			if (!$sth) {
				$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Failed to create topup summary: ".
						awitpt::db::dblayer::Error());
				goto FAIL_ROLLBACK;
			}

			$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     CREATE TOPUP SUMMARY: ".
					"TOPUPID '%s', Balance '%s'",
					$summaryTopup->{'ID'},
					$summaryTopup->{'Balance'}
			);
		}

		# Loop through topups that are depleted
		foreach my $topupID (@depletedTopups) {
			# Set users depleted topups
			$sth = DBSelect('
				UPDATE
					@TP@topups
				SET
					Depleted = 1,
					SMAdminDepletedOn = ?
				WHERE
					ID = ?
				',
				$depletedTimestamp,$topupID
			);
			if (!$sth) {
				$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Failed to deplete topup: ".
						awitpt::db::dblayer::Error());
				goto FAIL_ROLLBACK;
			}

			$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     DEPLETED TOPUP: ".
					"TOPUPID '%s'",
					$topupID
			);
		}

		# Loop through topup summary items that are depleted
		foreach my $topupID (@depletedSummary) {
			# Set users depleted topup summaries
			$sth = DBSelect('
				UPDATE
					@TP@topups_summary
				SET
					Depleted = 1,
					SMAdminDepletedOn = ?
				WHERE
					TopupID = ?
				',
				$depletedTimestamp,$topupID
			);
			if (!$sth) {
				$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Failed to update topups_summary: ".
						awitpt::db::dblayer::Error());
				goto FAIL_ROLLBACK;
			}

			$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup =>     DEPLETED TOPUP SUMMARY: ".
					"TOPUPID '%s'",
					$topupID
			);
		}
	}

	# Finished
	$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Topups have been updated and summaries created");
	DBCommit();
	return;

FAIL_ROLLBACK:
	DBRollback();
	$server->log(LOG_NOTICE,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Database has been rolled back, no records updated");
	return;
}


1;
# vim: ts=4
