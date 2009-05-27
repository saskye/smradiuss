# Capping support
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

package mod_feature_capping;

use strict;
use warnings;

# Modules we need
use smradius::constants;
use smradius::logging;
use smradius::util;
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
	Name => "User Capping Feature",
	Init => \&init,

	# Authentication hook
	'Feature_Post-Authentication_hook' => \&post_auth_hook,

	# Accounting hook
	'Feature_Post-Accounting_hook' => \&post_acct_hook,
};


# Some constants
my $TRAFFIC_LIMIT_KEY = 'SMRadius-Capping-Traffic-Limit';
my $UPTIME_LIMIT_KEY = 'SMRadius-Capping-UpTime-Limit';
my $TRAFFIC_TOPUPS_KEY = 'SMRadius-Capping-Traffic-Topup';

## @internal
# Initialize module
sub init
{
	my $server = shift;
}


## @post_auth_hook($server,$user,$packet)
# Post authentication hook
#
# @param server Server object
# @param user User data
# @param packet Radius packet
#
# @return Result
sub post_auth_hook
{
	my ($server,$user,$packet) = @_;

	$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] POST AUTH HOOK");

	my ($trafficLimit,$timeLimit);

	# Compare uptime limit
	if (defined($user->{'Attributes'}->{$UPTIME_LIMIT_KEY})) {
		$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] '".$UPTIME_LIMIT_KEY."' is defined");
		# Operator: :=
		if (defined($user->{'Attributes'}->{$UPTIME_LIMIT_KEY}->{':='})) {
			# Is it a number?
			if ($user->{'Attributes'}->{$UPTIME_LIMIT_KEY}->{':='}->{'Value'} =~ /^[0-9]+$/) {
				$timeLimit = $user->{'Attributes'}->{$UPTIME_LIMIT_KEY};
			} else {
				$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] '".$user->{'Attributes'}->{$UPTIME_LIMIT_KEY}->{':='}->{'Value'}.
						"' is NOT a numeric value");
			}
		} else {
			$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] No valid operators for attribute '".
					$user->{'Attributes'}->{$UPTIME_LIMIT_KEY}."'");
		}
	}


	# Compare traffic limit
	if (defined($user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY})) {
		$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] '".$TRAFFIC_LIMIT_KEY."' is defined");
		# Operator: +=
		if (defined($user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY}->{':='})) {
			# Is it a number?
			if ($user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY}->{':='}->{'Value'} =~ /^[0-9]+$/) {
				$trafficLimit = $user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY};
			} else {
				$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] '".$user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY}->{':='}->{'Value'}.
						"' is NOT a numeric value");
			}
		} else {
			$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] No valid operators for attribute '".
					$user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY}."'");
		}
	}

	# Check if we need to get the users' usage
	my $accountingUsage;
	if (defined($timeLimit) || defined($trafficLimit)) {
		# Loop with plugins to find anyting supporting getting of usage
		foreach my $module (@{$server->{'plugins'}}) {
			# Do we have the correct plugin?
			if ($module->{'Accounting_getUsage'}) {
				$server->log(LOG_INFO,"[MOD_FEATURE_CAPPING] Found plugin: '".$module->{'Name'}."'");
				# Fetch users session time & bandwidth used
				my $res = $module->{'Accounting_getUsage'}($server,$user,$packet);
				if (!defined($res)) {
					$server->log(LOG_ERR,"[MOD_FEATURE_CAPPING] No usage data found for user '".$packet->attr('User-Name')."'");
					return MOD_RES_SKIP;
				}

				$accountingUsage = $res;
			}
		}
	}

	# Check values against limits
	if (defined($timeLimit)) {
		if ($accountingUsage->{'TotalTimeUsage'} >= $timeLimit->{':='}->{'Value'}) {
			$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Usage exceeds ".$timeLimit->{':='}->{'Value'}.", rejecting");
			# Exceeding maximum, must be disconnected
			return MOD_RES_NACK;
		}
	}

	my $topupAmount = 0;
	if (defined($trafficLimit)) {

		# Get topups
		if (defined($user->{'ConfigAttributes'}->{$TRAFFIC_TOPUPS_KEY})) {
			$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] '".$TRAFFIC_TOPUPS_KEY."' is defined");
			# Operator: +=
			if (defined($user->{'ConfigAttributes'}->{$TRAFFIC_TOPUPS_KEY}->[0])) {
				# Is it a number?
				if ($user->{'ConfigAttributes'}->{$TRAFFIC_TOPUPS_KEY}->[0] =~ /^[0-9]+$/) {
					$topupAmount = $user->{'ConfigAttributes'}->{$TRAFFIC_TOPUPS_KEY}->[0];
				} else {
					$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] '".$user->{'ConfigAttributes'}->{$TRAFFIC_TOPUPS_KEY}->[0].
							"' is NOT a numeric value");
				}
			} else {
				$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] '".$TRAFFIC_TOPUPS_KEY."' has no value");
			}
		}

		# Set allowed traffic usage
		my $alteredTrafficLimit;
		if ($topupAmount > 0) {
			$alteredTrafficLimit = $trafficLimit->{':='}->{'Value'} + $topupAmount;
		} else {
			$alteredTrafficLimit = $trafficLimit->{':='}->{'Value'};
		}

		# Bandwidth usage
		$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Bandwidth => Usage total: ".$accountingUsage->{'TotalDataUsage'}.
				"Mb (Cap: ".$trafficLimit->{':='}->{'Value'}."Mb, Topups: ".$topupAmount."Mb)");

		# If bandwidth limit exceeded, cap user
		if ($accountingUsage->{'TotalDataUsage'} >= $alteredTrafficLimit) {
			$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Usage of ".$accountingUsage->{'TotalDataUsage'}.
					"Mb exceeds allowed limit of ".$alteredTrafficLimit."Mb. Capped.");
			return MOD_RES_NACK;
		}
	}

	return MOD_RES_ACK;
}


## @post_acct_hook($server,$user,$packet)
# Post authentication hook
#
# @param server Server object
# @param user User data
# @param packet Radius packet
#
# @return Result
sub post_acct_hook
{
	my ($server,$user,$packet) = @_;


	# Exceeding maximum, must be disconnected
	return MOD_RES_SKIP if ($packet->attr('Acct-Status-Type') ne "Alive");

	$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] POST ACCT HOOK");

	my ($trafficLimit,$timeLimit);
	
	# Compare uptime limit
	if (defined($user->{'Attributes'}->{$UPTIME_LIMIT_KEY})) {
		$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] '".$UPTIME_LIMIT_KEY."' is defined");
		# Operator: :=
		if (defined($user->{'Attributes'}->{$UPTIME_LIMIT_KEY}->{':='})) {
			# Is it a number?
			if ($user->{'Attributes'}->{$UPTIME_LIMIT_KEY}->{':='}->{'Value'} =~ /^[0-9]+$/) {
				$timeLimit = $user->{'Attributes'}->{$UPTIME_LIMIT_KEY};
			} else {
				$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] '".$user->{'Attributes'}->{$UPTIME_LIMIT_KEY}->{':='}->{'Value'}.
						"' is NOT a numeric value");
			}
		} else {
			$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] No valid operators for attribute '".
					$user->{'Attributes'}->{$UPTIME_LIMIT_KEY}."'");
		}
	}


	# Compare SMRadius-Capping-Traffic-Limit
	if (defined($user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY})) {
		$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] '".$TRAFFIC_LIMIT_KEY."' is defined");
		# Operator: +=
		if (defined($user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY}->{':='})) {
			# Is it a number?
			if ($user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY}->{':='}->{'Value'} =~ /^[0-9]+$/) {
				$trafficLimit = $user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY};
			} else {
				$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] '".$user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY}->{':='}->{'Value'}.
						"' is NOT a numeric value");
			}
		} else {
			$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] No valid operators for attribute '".
					$user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY}."'");
		}
	}

	# Check if we need to get the users' usage
	my $accountingUsage;
	if (defined($timeLimit) || defined($trafficLimit)) {
		# Loop with plugins to find anyting supporting getting of usage
		foreach my $module (@{$server->{'plugins'}}) {
			# Do we have the correct plugin?
			if ($module->{'Accounting_getUsage'}) {
				$server->log(LOG_INFO,"[MOD_FEATURE_CAPPING] Found plugin: '".$module->{'Name'}."'");
				# Fetch users session time & bandwidth used
				my $res = $module->{'Accounting_getUsage'}($server,$user,$packet);
				if (!defined($res)) {
					$server->log(LOG_ERR,"[MOD_FEATURE_CAPPING] No usage data found for user '".$packet->attr('User-Name')."'");
					return MOD_RES_SKIP;
				}

				$accountingUsage = $res;
			}
		}
	}

	# Check values against limits
	if (defined($timeLimit)) {
		if ($accountingUsage->{'TotalTimeUsage'} >= $timeLimit->{':='}->{'Value'}) {
			$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Usage exceeds ".$timeLimit->{':='}->{'Value'}.", rejecting");
			# Exceeding maximum, must be disconnected
			return MOD_RES_NACK;
		}
	}
	my $topupAmount = 0;
	if (defined($trafficLimit)) {

		# Get topups
		if (defined($user->{'ConfigAttributes'}->{$TRAFFIC_TOPUPS_KEY})) {
			$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] '".$TRAFFIC_TOPUPS_KEY."' is defined");
			# Operator: +=
			if (defined($user->{'ConfigAttributes'}->{$TRAFFIC_TOPUPS_KEY}->[0])) {
				# Is it a number?
				if ($user->{'ConfigAttributes'}->{$TRAFFIC_TOPUPS_KEY}->[0] =~ /^[0-9]+$/) {
					$topupAmount = $user->{'ConfigAttributes'}->{$TRAFFIC_TOPUPS_KEY}->[0];
				} else {
					$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] '".$user->{'ConfigAttributes'}->{$TRAFFIC_TOPUPS_KEY}->[0].
							"' is NOT a numeric value");
				}
			} else {
				$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] '".$TRAFFIC_TOPUPS_KEY."' has no value");
			}
		}

		# Set allowed traffic usage
		my $alteredTrafficLimit;
		if ($topupAmount > 0) {
			$alteredTrafficLimit = $trafficLimit->{':='}->{'Value'} + $topupAmount;
		} else {
			$alteredTrafficLimit = $trafficLimit->{':='}->{'Value'};
		}

		# Bandwidth usage
		$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Bandwidth => Usage total: ".$accountingUsage->{'TotalDataUsage'}.
				"Mb (Cap: ".$trafficLimit->{':='}->{'Value'}."Mb, Topups: ".$topupAmount."Mb)");

		# If bandwidth limit exceeded, cap user
		if ($accountingUsage->{'TotalDataUsage'} >= $alteredTrafficLimit) {
			$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Usage of ".$accountingUsage->{'TotalDataUsage'}.
					"Mb exceeds allowed limit of ".$alteredTrafficLimit."Mb. Capped.");
			return MOD_RES_NACK;
		}
	}

	return MOD_RES_ACK;
}



1;
# vim: ts=4
