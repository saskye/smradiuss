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

package smradius::modules::system::mod_config_sql_topups;

use strict;
use warnings;

# Modules we need
use smradius::constants;
use smradius::logging;
use awitpt::db::dblayer;
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

	# Query database
	my $sth = DBSelect($config->{'get_topups_summary_query'},$periodKey,$packet->attr('User-Name'));
	if (!$sth) {
		$server->log(LOG_ERR,"Failed to get topup information: ".awitpt::db::dblayer::Error());
		return MOD_RES_NACK;
	}

	# Fetch all summaries
	my (@trafficSummary,@uptimeSummary);
	while (my $row = $sth->fetchrow_hashref()) {
		$row = hashifyLCtoMC($row, qw(Balance Type ID));

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
	$sth = DBSelect($config->{'get_topups_query'},$thisMonth,$now,$packet->attr('User-Name'));
	if (!$sth) {
		$server->log(LOG_ERR,"Failed to get topup information: ".awitpt::db::dblayer::Error());
		return MOD_RES_NACK;
	}

	# Fetch all new topups 
	my (@trafficTopups,@uptimeTopups);
	while (my $row = $sth->fetchrow_hashref()) {
		$row = hashifyLCtoMC($row, qw(ID Type Value));

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
	processConfigAttribute($server,$user->{'ConfigAttributes'},{ 'Name' => 'SMRadius-Capping-Traffic-Topup',
			'Operator' => ':=', 'Value' => $totalTopupTraffic });

	# Process uptime topups
	processConfigAttribute($server,$user->{'ConfigAttributes'},{ 'Name' => 'SMRadius-Capping-Uptime-Topup',
			'Operator' => ':=', 'Value' => $totalTopupUptime });

	return MOD_RES_ACK;
}


# Topup summary function
sub cleanup
{
	my ($server) = @_;

	# TODO - be more dynamic, we may not be using SQL users
	# Get all usernames
	my $sth = DBSelect('SELECT ID, Username FROM @TP@users');

	if (!$sth) {
		$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Failed to select from users: ".
				awitpt::db::dblayer::Error());
		return;
	}

	# Create hash of usernames
	my %users;
	while (my $user = $sth->fetchrow_hashref()) {
		$user = hashifyLCtoMC($user, qw(ID Username));
		$users{$user->{'ID'}} = $user->{'Username'};
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

	# Loop through users
	foreach my $userID (keys %users) {
		my $userName = $users{$userID};

		# TODO - in future we must be more dynamic, we may not be using SQL accunting

		# Get traffic and uptime usage for last month
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
				AND Username = ?
			GROUP BY
				Username
			',
			$lastMonth,$userName
		);

		if (!$sth) {
			$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Failed to select accounting records: ".
					awitpt::db::dblayer::Error());
			goto FAIL_ROLLBACK;
		}

		my $row = $sth->fetchrow_hashref();
		if ($sth->rows > 0) {
			$row = hashifyLCtoMC(
				$row,
				qw(Username AcctSessionTime AcctInputOctets AcctInputGigawords AcctOutputOctets AcctOutputGigawords)
			);
		}

		# Add up traffic
		my $totalData = 0; 
		if (defined($row->{'AcctInputOctets'}) && $row->{'AcctInputOctets'} > 0) {
			$totalData += $row->{'AcctInputOctets'} / 1024 / 1024;
		}
		if (defined($row->{'AcctInputGigawords'}) && $row->{'AcctInputGigawords'} > 0) {
			$totalData += $row->{'AcctInputGigawords'} * 4096;
		}
		if (defined($row->{'AcctOutputOctets'}) && $row->{'AcctOutputOctets'} > 0) {
			$totalData += $row->{'AcctOutputOctets'} / 1024 / 1024;
		}
		if (defined($row->{'AcctOutputGigawords'}) && $row->{'AcctOutputGigawords'} > 0) {
			$totalData += $row->{'AcctOutputGigawords'} * 4096;
		}

		# Add up uptime
		my $totalTime = 0; 
		if (defined($row->{'AcctSessionTime'}) && $row->{'AcctSessionTime'} > 0) {
			$totalTime = $row->{'AcctSessionTime'} / 60;
		}

		# Rounding up
		my $totalTrafficUsage = ceil($totalData);
		my $totalUptimeUsage = ceil($totalTime);

		# Finished for now
		DBFreeRes($sth);


		# TODO?
		# FIXME - support for groups and realm config?

		# Get user traffic and uptime limits
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
					awitpt::db::dblayer::Error());
			goto FAIL_ROLLBACK;
		}

		# Store limits in capRecord hash
		my %capRecord;
		while ($row = $sth->fetchrow_hashref()) {
			$row = hashifyLCtoMC(
				$row,
				qw(Name Value)
			);

			if ($row->{'Name'} eq 'SMRadius-Capping-Traffic-Limit') {
				$capRecord{'TrafficLimit'} = $row->{'Value'};
			}
			if ($row->{'Name'} eq 'SMRadius-Capping-Uptime-Limit') {
				$capRecord{'UptimeLimit'} = $row->{'Value'};
			}
		}

		# Finished for now
		DBFreeRes($sth);


		# Get users topups that are still valid from topups_summary, must not be depleted
		my $prevPeriodKey = $lastMonth->strftime("%Y-%m");
		$sth = DBSelect('
			SELECT
				@TP@topups_summary.TopupID,
				@TP@topups_summary.Balance,
				@TP@topups.Value,
				@TP@topups.ValidTo,
				@TP@topups.Type
			FROM
				@TP@topups_summary, @TP@topups, @TP@users
			WHERE
				@TP@topups_summary.Depleted = 0
				AND @TP@topups_summary.TopupID = @TP@topups.ID
				AND @TP@users.ID = @TP@topups.UserID
				AND @TP@users.Username = ?
				AND @TP@topups_summary.PeriodKey = ?
			ORDER BY
				@TP@topups.Timestamp
			',
			$userName, $prevPeriodKey
		);

		if (!$sth) {
			$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Failed to select topup summaries: ".
					awitpt::db::dblayer::Error());
			goto FAIL_ROLLBACK;
		}


		# Add previous valid topups to lists
		my @trafficSummary = ();
		my @uptimeSummary = ();
		while (my $row = $sth->fetchrow_hashref()) {
			$row = hashifyLCtoMC(
				$row,
				qw(TopupID Balance Value ValidTo Type)
			);

			if (defined($row->{'ValidTo'})) {
				# Convert string to unix time
				my $unix_validTo = str2time($row->{'ValidTo'});
				if ($row->{'Type'} == 1) {
					push(@trafficSummary, { 
							TopupID => $row->{'TopupID'},
							Balance => $row->{'Balance'},
							Value => $row->{'Value'},
							ValidTo => $unix_validTo,
							Type => $row->{'Type'}
					});
				} elsif ($row->{'Type'} == 2) {
					push(@uptimeSummary, { 
							TopupID => $row->{'TopupID'},
							Balance => $row->{'Balance'},
							Value => $row->{'Value'},
							ValidTo => $unix_validTo,
							Type => $row->{'Type'}
					});
				}
			}
		}

		# Finished for now
		DBFreeRes($sth);


		# Get topups from last month
		$sth = DBSelect('
			SELECT
				@TP@topups.ID, @TP@topups.Value, @TP@topups.Type, @TP@topups.ValidTo
			FROM
				@TP@topups, @TP@users
			WHERE
				@TP@topups.Depleted = 0
				AND @TP@topups.UserID = @TP@users.ID
				AND @TP@users.Username = ?
				AND @TP@topups.ValidFrom <= ?
				AND @TP@topups.ValidTo >= ?
			ORDER BY
				@TP@topups.Timestamp
			',
			$userName,$lastMonth,$thisMonth
		);

		if (!$sth) {
			$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Failed to select topups: ".
					awitpt::db::dblayer::Error());
			goto FAIL_ROLLBACK;
		}

		# Loop with the topups and push them into arrays
		my (@trafficTopups,@uptimeTopups);
		while (my $row = $sth->fetchrow_hashref()) {
			$row = hashifyLCtoMC(
				$row,
				qw(ID Value Type ValidTo)
			);

			# Convert string to unix time
			my $unix_validTo = str2time($row->{'ValidTo'});
			# If this is a traffic topup ...
			if ($row->{'Type'} == 1) {
				push(@trafficTopups, {
					ID => $row->{'ID'},
					Value => $row->{'Value'},
					ValidTo => $unix_validTo
				});

			# Or a uptime topup...
			} elsif ($row->{'Type'} == 2) {
				push(@uptimeTopups, {
					ID => $row->{'ID'},
					Value => $row->{'Value'},
					ValidTo => $unix_validTo
				});
			}
		}

		# Finished for now
		DBFreeRes($sth);

		# List of summaries depleted
		my @depletedSummary = ();
		# Summaries to be edited/repeated
		my @summaryTopups = ();
		# List of depleted topups, looping through summaries may
		# deplete a topup and topups table must be updated too
		my @depletedTopups = ();

		# Format this month period key
		my $periodKey = $thisMonth->strftime("%Y-%m");

		my $uptimeOverUsage = 0;
		my $trafficOverUsage = 0;
		if (defined($capRecord{'TrafficLimit'})) {
			# Check traffic used against cap 
			$trafficOverUsage = $totalTrafficUsage - $capRecord{'TrafficLimit'};
		# If there is no limit, this may be a prepaid user
		} else {
			$capRecord{'TrafficLimit'} = 0;
			foreach my $prevTopup (@trafficSummary) {
				$capRecord{'TrafficLimit'} += $prevTopup->{'Balance'};
			}
			foreach my $topup (@trafficTopups) {
				$capRecord{'TrafficLimit'} += $topup->{'Value'};
			}
			$trafficOverUsage = $totalTrafficUsage - $capRecord{'TrafficLimit'};
		}

		# User has started using topup bandwidth..
		if ($trafficOverUsage > 0) {

			# Loop with previous topups, setting them depleted or repeating as necessary
			foreach my $summaryItem (@trafficSummary) {
				# Summary has not been used, if valid add to list to be repeated
				if ($trafficOverUsage <= 0 && $summaryItem->{'ValidTo'} >= $unix_nextMonth) {
					push(@summaryTopups, {
							ID => $summaryItem->{'TopupID'},
							PeriodKey => $periodKey,
							Balance => $summaryItem->{'Value'}
					});
					# This topup has not been touched and will be carried over
					next;
				# Topup summary depleted
				} elsif ($summaryItem->{'Balance'} <= $trafficOverUsage) {
					push(@depletedSummary, $summaryItem->{'TopupID'});
					push(@depletedTopups, $summaryItem->{'TopupID'});

					# Excess traffic remaining
					$trafficOverUsage -= $summaryItem->{'Balance'};

				# Topup summary still alive
				} else {
					my $trafficRemaining = $summaryItem->{'Balance'} - $trafficOverUsage;
					if ($summaryItem->{'ValidTo'} >= $unix_nextMonth) {
						push(@summaryTopups, {
								ID => $summaryItem->{'TopupID'},
								PeriodKey => $periodKey,
								Balance => $trafficRemaining
						});
					}
					# All excess traffic has been "paid" for
					$trafficOverUsage = 0;
				}
			}

			# Loop with topups, setting them depleted or adding summary as necessary
			foreach my $topup (@trafficTopups) {
				# Topup has not been used, if valid add to summary
				if ($trafficOverUsage <= 0 && $topup->{'ValidTo'} >= $unix_nextMonth) {
					push(@summaryTopups, {
							ID => $topup->{'ID'},
							PeriodKey => $periodKey,
							Balance => $topup->{'Value'}
					});
					# This topup has not been touched and will be carried over
					next;
				# Topup depleted
				} elsif ($topup->{'Value'} <= $trafficOverUsage) {
					push(@depletedTopups, $topup->{'ID'});
					# Excess traffic remaining
					$trafficOverUsage -= $topup->{'Value'};
				# Topup still alive
				} else {
					# Check if this summary exists in the list
					my $trafficRemaining = $topup->{'Value'} - $trafficOverUsage;
					if ($topup->{'ValidTo'} >= $unix_nextMonth) {
						push(@summaryTopups, {
								ID => $topup->{'ID'},
								PeriodKey => $periodKey,
								Balance => $trafficRemaining
						});
					}
					# All excess traffic has been "paid" for
					$trafficOverUsage = 0;
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
							PeriodKey => $periodKey,
							Balance => $summaryItem->{'Value'}
					});
				}
			}
			# Check for topups
			foreach my $topup (@trafficTopups) {
				# Add to summaries
				if ($topup->{'ValidTo'} >= $unix_nextMonth) {
					push(@summaryTopups, {
							ID => $topup->{'ID'},
							PeriodKey => $periodKey,
							Balance => $topup->{'Value'}
					});
				}
			}
		}


		if (defined($capRecord{'UptimeLimit'})) {
			# Check traffic used against cap
			$uptimeOverUsage = $totalUptimeUsage - $capRecord{'UptimeLimit'};
		# If there is no limit, this may be a prepaid user
		} else {
			$capRecord{'UptimeLimit'} = 0;
			foreach my $prevTopup (@uptimeSummary) {
				$capRecord{'UptimeLimit'} += $prevTopup->{'Balance'};
			}
			foreach my $topup (@uptimeTopups) {
				$capRecord{'UptimeLimit'} += $topup->{'Value'};
			}
			$uptimeOverUsage = $totalUptimeUsage - $capRecord{'UptimeLimit'};
		}

		# User has started using topup uptime..
		if ($uptimeOverUsage > 0) {

			# Loop with previous topups, setting them depleted or repeating as necessary
			foreach my $summaryItem (@uptimeSummary) {
				# Summary has not been used, if valid add to list to be repeated
				if ($uptimeOverUsage <= 0 && $summaryItem->{'ValidTo'} >= $unix_nextMonth) {
					push(@summaryTopups, {
							ID => $summaryItem->{'TopupID'},
							PeriodKey => $periodKey,
							Balance => $summaryItem->{'Value'}
					});
					# This topup has not been touched and will be carried over
					next;
				# Topup summary depleted
				} elsif ($summaryItem->{'Balance'} <= $uptimeOverUsage) {
					push(@depletedSummary, $summaryItem->{'TopupID'});
					push(@depletedTopups, $summaryItem->{'TopupID'});

					# Excess uptime remaining
					$uptimeOverUsage -= $summaryItem->{'Balance'};

				# Topup summary still alive
				} else {
					my $uptimeRemaining = $summaryItem->{'Balance'} - $uptimeOverUsage;
					if ($summaryItem->{'ValidTo'} >= $unix_nextMonth) {
						push(@summaryTopups, {
								ID => $summaryItem->{'TopupID'},
								PeriodKey => $periodKey,
								Balance => $uptimeRemaining
						});
					}
					# All excess uptime has been "paid" for
					$uptimeOverUsage = 0;
				}
			}

			# Loop with topups, setting them depleted or adding summary as necessary
			foreach my $topup (@uptimeTopups) {
				# Topup has not been used, if valid add to summary
				if ($uptimeOverUsage <= 0 && $topup->{'ValidTo'} >= $unix_nextMonth) {
					push(@summaryTopups, {
							ID => $topup->{'ID'},
							PeriodKey => $periodKey,
							Balance => $topup->{'Value'}
					});
					# This topup has not been touched and will be carried over
					next;
				# Topup depleted
				} elsif ($topup->{'Value'} <= $uptimeOverUsage) {
					push(@depletedTopups, $topup->{'ID'});
					# Excess uptime remaining
					$uptimeOverUsage -= $topup->{'Value'};
				# Topup still alive
				} else {
					my $uptimeRemaining = $topup->{'Value'} - $uptimeOverUsage;
					if ($topup->{'ValidTo'} >= $unix_nextMonth) {
						push(@summaryTopups, {
								ID => $topup->{'ID'},
								PeriodKey => $periodKey,
								Balance => $uptimeRemaining
						});
					}
					# All excess uptime has been "paid" for
					$uptimeOverUsage = 0;
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
							PeriodKey => $periodKey,
							Balance => $summaryItem->{'Value'}
					});
				}
			}
			# Check for topups
			foreach my $topup (@uptimeTopups) {
				# Check if summary exists
				if ($topup->{'ValidTo'} >= $unix_nextMonth) {
					push(@summaryTopups, {
							ID => $topup->{'ID'},
							PeriodKey => $periodKey,
							Balance => $topup->{'Value'}
					});
				}
			}
		}

		# Loop through summary topups
		foreach my $summaryTopup (@summaryTopups) {
			# Set users depleted topups
			$sth = DBDo('
				INSERT INTO
					@TP@topups_summary (TopupID,PeriodKey,Balance)
				VALUES
					(?,?,?)
				',
				$summaryTopup->{'ID'},$periodKey,$summaryTopup->{'Balance'}
			);
			if (!$sth) {
				$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Failed to update topups_summary: ".
						awitpt::db::dblayer::Error());
				goto FAIL_ROLLBACK;
			}
		}

		# Loop through topups that are depleted
		foreach my $topupID (@depletedTopups) {
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
						awitpt::db::dblayer::Error());
				goto FAIL_ROLLBACK;
			}
		}

		# Loop through topup summary items that are depleted
		foreach my $topupID (@depletedSummary) {
			# Set users depleted topup summaries
			$sth = DBSelect('
				UPDATE
					@TP@topups_summary
				SET
					Depleted = 1
				WHERE
					TopupID = ?
				',
				$topupID
			);
			if (!$sth) {
				$server->log(LOG_ERR,"[MOD_CONFIG_SQL_TOPUPS] Cleanup => Failed to update topups_summary: ".
						awitpt::db::dblayer::Error());
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
