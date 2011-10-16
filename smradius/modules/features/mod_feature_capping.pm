# Capping support
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

package smradius::modules::features::mod_feature_capping;

use strict;
use warnings;

# Modules we need
use smradius::attributes;
use smradius::constants;
use smradius::logging;
use smradius::util;

use POSIX qw(floor);


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
my $UPTIME_LIMIT_KEY = 'SMRadius-Capping-Uptime-Limit';
my $TRAFFIC_TOPUPS_KEY = 'SMRadius-Capping-Traffic-Topup';
my $TIME_TOPUPS_KEY = 'SMRadius-Capping-Uptime-Topup';

my $config;

## @internal
# Initialize module
sub init
{
	my $server = shift;
	my $scfg = $server->{'inifile'};


	# Setup SQL queries
	if (defined($scfg->{'mod_feature_capping'})) {
		# Check if option exists
		if (defined($scfg->{'mod_feature_capping'}{'enable_mikrotik'})) {
			# Pull in config
			if ($scfg->{'mod_feature_capping'}{'enable_mikrotik'} =~ /^\s*(yes|true|1)\s*$/i) {
				$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] Mikrotik-specific vendor return attributes ENABLED");
				$config->{'enable_mikrotik'} = $scfg->{'mod_feature_capping'}{'enable_mikrotik'};
			# Default?
			} elsif ($scfg->{'mod_feature_capping'}{'enable_mikrotik'} =~ /^\s*(no|false|0)\s*$/i) {
				$config->{'enable_mikrotik'} = undef;
			} else {
				$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] Value for 'enable_mikrotik' is invalid");
			}
		}
	}

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


	# Skip MAC authentication
	return MOD_RES_SKIP if ($user->{'_UserDB'}->{'Name'} eq "SQL User Database (MAC authentication)");

	$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] POST AUTH HOOK");

	#
	# Get limits from attributes
	#


	# Check if there is an uptime limit
	my $uptimeLimit;
	if (defined($user->{'Attributes'}->{$UPTIME_LIMIT_KEY})) {
		$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] '".$UPTIME_LIMIT_KEY."' is defined");

		# Check for valid attribute operator: ':='
		if (defined($user->{'Attributes'}->{$UPTIME_LIMIT_KEY}->{':='})) {

			# Check for valid attribute value
			if (defined($user->{'Attributes'}->{$UPTIME_LIMIT_KEY}->{':='}->{'Value'}) &&
					$user->{'Attributes'}->{$UPTIME_LIMIT_KEY}->{':='}->{'Value'} =~ /^\d+$/) {

				$uptimeLimit = $user->{'Attributes'}->{$UPTIME_LIMIT_KEY}->{':='}->{'Value'};
			} else {
				$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] '".$user->{'Attributes'}->{$UPTIME_LIMIT_KEY}->{':='}->{'Value'}.
						"' is NOT a numeric value");
			}

		} else {
			$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] No valid operators for attribute '".
					$user->{'Attributes'}->{$UPTIME_LIMIT_KEY}."'");
		}
	}

	# Check if there is a traffic limit
	my $trafficLimit;
	if (defined($user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY})) {
		$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] '".$TRAFFIC_LIMIT_KEY."' is defined");

		# Check for valid attribute operator: ':='
		if (defined($user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY}->{':='})) {

			# Check for valid attribute value
			if (defined($user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY}->{':='}->{'Value'}) &&
					$user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY}->{':='}->{'Value'} =~ /^\d+$/) {

				$trafficLimit = $user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY}->{':='}->{'Value'};
			} else {
				$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] '".$user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY}->{':='}->{'Value'}.
						"' is NOT a numeric value");
			}

		} else {
			$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] No valid operators for attribute '".
					$user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY}."'");
		}
	}


	#
	# Get current traffic and uptime usage
	#


	# Loop with plugins to find anything supporting getting of usage
	my $accountingUsage;
	foreach my $module (@{$server->{'module_list'}}) {
		# Do we have the correct plugin?
		if ($module->{'Accounting_getUsage'}) {
			$server->log(LOG_INFO,"[MOD_FEATURE_CAPPING] Found plugin: '".$module->{'Name'}."'");
			# Fetch users session uptime & bandwidth used
			my $res = $module->{'Accounting_getUsage'}($server,$user,$packet);
			if (!defined($res)) {
				$server->log(LOG_ERR,"[MOD_FEATURE_CAPPING] No usage data found for user '".$user->{'Username'}."'");
				return MOD_RES_SKIP;
			}

			$accountingUsage = $res;
		}
	}


	#
	# Get valid traffic and uptime topups
	#


	# Check if there was any data returned at all
	my $uptimeTopup = 0;
	if (defined($user->{'ConfigAttributes'}->{$TIME_TOPUPS_KEY})) {
		$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] '".$TIME_TOPUPS_KEY."' is defined");

		# Check if there is a value
		if (defined($user->{'ConfigAttributes'}->{$TIME_TOPUPS_KEY}->[0])) {
			# Check if the value is of a valid type
			if ($user->{'ConfigAttributes'}->{$TIME_TOPUPS_KEY}->[0] =~ /^\d+$/) {
				$uptimeTopup = $user->{'ConfigAttributes'}->{$TIME_TOPUPS_KEY}->[0];
			} else {
				$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] '".$user->{'ConfigAttributes'}->{$TIME_TOPUPS_KEY}->[0].
						"' is NOT a numeric value");
			}
		} else {
			$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] '".$TIME_TOPUPS_KEY."' has no value");
		}
	}

	# Check if there was any data returned at all
	my $trafficTopup = 0;
	if (defined($user->{'ConfigAttributes'}->{$TRAFFIC_TOPUPS_KEY})) {
		$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] '".$TRAFFIC_TOPUPS_KEY."' is defined");
		# Check for value
		if (defined($user->{'ConfigAttributes'}->{$TRAFFIC_TOPUPS_KEY}->[0])) {
			# Is it a number?
			if ($user->{'ConfigAttributes'}->{$TRAFFIC_TOPUPS_KEY}->[0] =~ /^\d+$/) {
				$trafficTopup = $user->{'ConfigAttributes'}->{$TRAFFIC_TOPUPS_KEY}->[0];
			} else {
				$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] '".$user->{'ConfigAttributes'}->{$TRAFFIC_TOPUPS_KEY}->[0].
						"' is NOT a numeric value");
			}
		} else {
			$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] '".$TRAFFIC_TOPUPS_KEY."' has no value");
		}
	}

	#
	# Set the new uptime and traffic limits (limit, if any.. + topups)
	#


	# Uptime..
	my $alteredUptimeLimit = 0;
	if ($uptimeTopup > 0) {
		if (defined($uptimeLimit)) {
			$alteredUptimeLimit = $uptimeLimit + $uptimeTopup;
		} else {
			$alteredUptimeLimit = $uptimeTopup;
		}
	} else {
		if (defined($uptimeLimit)) {
			$alteredUptimeLimit = $uptimeLimit;
		}
	}

	# Traffic..
	my $alteredTrafficLimit = 0;
	if ($trafficTopup > 0) {
		if (defined($trafficLimit)) {
			$alteredTrafficLimit = $trafficLimit + $trafficTopup;
		} else {
			$alteredTrafficLimit = $trafficTopup;
		}
	} else {
		if (defined($trafficLimit)) {
			$alteredTrafficLimit = $trafficLimit;
		}
	}


	#
	# Display our usages
	#


	# Uptime..
	if (!(defined($uptimeLimit) && $uptimeLimit == 0)) {
		if (!defined($uptimeLimit)) {
			$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Uptime => Usage total: ".$accountingUsage->{'TotalSessionTime'}.
					"Min (Cap: Prepaid, Topups: ".$uptimeTopup."Min)");
		} else {
			$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Uptime => Usage total: ".$accountingUsage->{'TotalSessionTime'}.
					"Min (Cap: ".$uptimeLimit."Min, Topups: ".$uptimeTopup."Min)");
		}
	} else {
		$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Uptime => Usage total: ".$accountingUsage->{'TotalSessionTime'}.
				"Min (Cap: Uncapped, Topups: ".$uptimeTopup."Min)");
	}

	# Traffic..
	if (!(defined($trafficLimit) && $trafficLimit == 0)) {
		if (!defined($trafficLimit)) {
			$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Bandwidth => Usage total: ".$accountingUsage->{'TotalDataUsage'}.
					"Mb (Cap: Prepaid, Topups: ".$trafficTopup."Mb)");
		} else {
			$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Bandwidth => Usage total: ".$accountingUsage->{'TotalDataUsage'}.
					"Mb (Cap: ".$trafficLimit."Mb, Topups: ".$trafficTopup."Mb)");
		}
	} else {
		$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Bandwidth => Usage total: ".$accountingUsage->{'TotalDataUsage'}.
				"Mb (Cap: Uncapped, Topups: ".$trafficTopup."Mb)");
	}


	#
	# Check if we've exceeded our limits
	#


	# Uptime..
	if (!(defined($uptimeLimit) && $uptimeLimit == 0)) {

		# Capped
		if ($accountingUsage->{'TotalSessionTime'} >= $alteredUptimeLimit) {
			$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Usage of ".$accountingUsage->{'TotalSessionTime'}.
					"Min exceeds allowed limit of ".$alteredUptimeLimit."Min. Capped.");
			return MOD_RES_NACK;
		# Setup limits
		} else {
			# Check if we returning Mikrotik vattributes
			if (defined($config->{'enable_mikrotik'})) {
				# Setup reply attributes for Mikrotik HotSpots
				my %attribute = (
					'Name' => 'Session-Timeout',
					'Operator' => '=',
					'Value' => $alteredUptimeLimit - $accountingUsage->{'TotalSessionTime'}
				);
				setReplyAttribute($server,$user->{'ReplyAttributes'},\%attribute);
			}
		}
	}

	# Traffic
	if (!(defined($trafficLimit) && $trafficLimit == 0)) {

		# Capped
		if ($accountingUsage->{'TotalDataUsage'} >= $alteredTrafficLimit) {
			$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Usage of ".$accountingUsage->{'TotalDataUsage'}.
					"Mb exceeds allowed limit of ".$alteredTrafficLimit."Mb. Capped.");
			return MOD_RES_NACK;
		# Setup limits
		} else {
			# Check if we returning Mikrotik vattributes
			if (defined($config->{'enable_mikrotik'})) {
				# Get remaining traffic
				my $remainingTraffic = $alteredTrafficLimit - $accountingUsage->{'TotalDataUsage'};
				my $remainingTrafficLimit = ( $remainingTraffic % 4096 ) * 1024 * 1024;
				my $remainingTrafficGigawords = floor($remainingTraffic / 4096);
	
				# Setup reply attributes for Mikrotik HotSpots
				for my $attrName ('Recv','Xmit','Total') {
					my %attribute = (
						'Vendor' => 14988,
						'Name' => "Mikrotik-$attrName-Limit",
						'Operator' => '=',
						# Gigawords leftovers
						'Value' => $remainingTrafficLimit
					);
					setReplyVAttribute($server,$user->{'ReplyVAttributes'},\%attribute);
	
					%attribute = (
						'Vendor' => 14988,
						'Name' => "Mikrotik-$attrName-Limit-Gigawords",
						'Operator' => '=',
						# Gigawords
						'Value' => $remainingTrafficGigawords
					);
					setReplyVAttribute($server,$user->{'ReplyVAttributes'},\%attribute);
				}
			}
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


	# We cannot cap a user if we don't have a UserDB module can we? no userdb, no cap?
	return MOD_RES_SKIP if (!defined($user->{'_UserDB'}->{'Name'}));

	# Skip MAC authentication
	return MOD_RES_SKIP if ($user->{'_UserDB'}->{'Name'} eq "SQL User Database (MAC authentication)");

	# Exceeding maximum, must be disconnected
	return MOD_RES_SKIP if ($packet->attr('Acct-Status-Type') ne "Alive");

	$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] POST ACCT HOOK");


	#
	# Get limits from attributes
	#


	# Check if there is an uptime limit
	my $uptimeLimit;
	if (defined($user->{'Attributes'}->{$UPTIME_LIMIT_KEY})) {
		$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] '".$UPTIME_LIMIT_KEY."' is defined");

		# Check for valid attribute operator: ':='
		if (defined($user->{'Attributes'}->{$UPTIME_LIMIT_KEY}->{':='})) {

			# Check for valid attribute value
			if (defined($user->{'Attributes'}->{$UPTIME_LIMIT_KEY}->{':='}->{'Value'}) &&
					$user->{'Attributes'}->{$UPTIME_LIMIT_KEY}->{':='}->{'Value'} =~ /^\d+$/) {

				$uptimeLimit = $user->{'Attributes'}->{$UPTIME_LIMIT_KEY}->{':='}->{'Value'};
			} else {
				$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] '".$user->{'Attributes'}->{$UPTIME_LIMIT_KEY}->{':='}->{'Value'}.
						"' is NOT a numeric value");
			}

		} else {
			$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] No valid operators for attribute '".
					$user->{'Attributes'}->{$UPTIME_LIMIT_KEY}."'");
		}
	}

	# Check if there is a traffic limit
	my $trafficLimit;
	if (defined($user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY})) {
		$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] '".$TRAFFIC_LIMIT_KEY."' is defined");

		# Check for valid attribute operator: ':='
		if (defined($user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY}->{':='})) {

			# Check for valid attribute value
			if (defined($user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY}->{':='}->{'Value'}) &&
					$user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY}->{':='}->{'Value'} =~ /^\d+$/) {

				$trafficLimit = $user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY}->{':='}->{'Value'};
			} else {
				$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] '".$user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY}->{':='}->{'Value'}.
						"' is NOT a numeric value");
			}

		} else {
			$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] No valid operators for attribute '".
					$user->{'Attributes'}->{$TRAFFIC_LIMIT_KEY}."'");
		}
	}


	#
	# Get current traffic and uptime usage
	#


	# Loop with plugins to find anything supporting getting of usage
	my $accountingUsage;
	foreach my $module (@{$server->{'module_list'}}) {
		# Do we have the correct plugin?
		if ($module->{'Accounting_getUsage'}) {
			$server->log(LOG_INFO,"[MOD_FEATURE_CAPPING] Found plugin: '".$module->{'Name'}."'");
			# Fetch users session uptime & bandwidth used
			my $res = $module->{'Accounting_getUsage'}($server,$user,$packet);
			if (!defined($res)) {
				$server->log(LOG_ERR,"[MOD_FEATURE_CAPPING] No usage data found for user '".$user->{'Username'}."'");
				return MOD_RES_SKIP;
			}

			$accountingUsage = $res;
		}
	}


	#
	# Get valid traffic and uptime topups
	#


	# Check if there was any data returned at all
	my $uptimeTopup = 0;
	if (defined($user->{'ConfigAttributes'}->{$TIME_TOPUPS_KEY})) {
		$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] '".$TIME_TOPUPS_KEY."' is defined");

		# Check if there is a value
		if (defined($user->{'ConfigAttributes'}->{$TIME_TOPUPS_KEY}->[0])) {
			# Check if the value is of a valid type
			if ($user->{'ConfigAttributes'}->{$TIME_TOPUPS_KEY}->[0] =~ /^\d+$/) {
				$uptimeTopup = $user->{'ConfigAttributes'}->{$TIME_TOPUPS_KEY}->[0];
			} else {
				$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] '".$user->{'ConfigAttributes'}->{$TIME_TOPUPS_KEY}->[0].
						"' is NOT a numeric value");
			}
		} else {
			$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] '".$TIME_TOPUPS_KEY."' has no value");
		}
	}

	# Check if there was any data returned at all
	my $trafficTopup = 0;
	if (defined($user->{'ConfigAttributes'}->{$TRAFFIC_TOPUPS_KEY})) {
		$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] '".$TRAFFIC_TOPUPS_KEY."' is defined");
		# Check for value
		if (defined($user->{'ConfigAttributes'}->{$TRAFFIC_TOPUPS_KEY}->[0])) {
			# Is it a number?
			if ($user->{'ConfigAttributes'}->{$TRAFFIC_TOPUPS_KEY}->[0] =~ /^\d+$/) {
				$trafficTopup = $user->{'ConfigAttributes'}->{$TRAFFIC_TOPUPS_KEY}->[0];
			} else {
				$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] '".$user->{'ConfigAttributes'}->{$TRAFFIC_TOPUPS_KEY}->[0].
						"' is NOT a numeric value");
			}
		} else {
			$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] '".$TRAFFIC_TOPUPS_KEY."' has no value");
		}
	}

	#
	# Set the new uptime and traffic limits (limit, if any.. + topups)
	#


	# Uptime..
	my $alteredUptimeLimit = 0;
	if ($uptimeTopup > 0) {
		if (defined($uptimeLimit)) {
			$alteredUptimeLimit = $uptimeLimit + $uptimeTopup;
		} else {
			$alteredUptimeLimit = $uptimeTopup;
		}
	} else {
		if (defined($uptimeLimit)) {
			$alteredUptimeLimit = $uptimeLimit;
		}
	}

	# Traffic..
	my $alteredTrafficLimit = 0;
	if ($trafficTopup > 0) {
		if (defined($trafficLimit)) {
			$alteredTrafficLimit = $trafficLimit + $trafficTopup;
		} else {
			$alteredTrafficLimit = $trafficTopup;
		}
	} else {
		if (defined($trafficLimit)) {
			$alteredTrafficLimit = $trafficLimit;
		}
	}


	#
	# Display our usages
	#


	# Uptime..
	if (!(defined($uptimeLimit) && $uptimeLimit == 0)) {
		if (!defined($uptimeLimit)) {
			$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Uptime => Usage total: ".$accountingUsage->{'TotalSessionTime'}.
					"Min (Cap: Prepaid, Topups: ".$uptimeTopup."Min)");
		} else {
			$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Uptime => Usage total: ".$accountingUsage->{'TotalSessionTime'}.
					"Min (Cap: ".$uptimeLimit."Min, Topups: ".$uptimeTopup."Min)");
		}
	} else {
		$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Uptime => Usage total: ".$accountingUsage->{'TotalSessionTime'}.
				"Min (Cap: Uncapped, Topups: ".$uptimeTopup."Min)");
	}

	# Traffic..
	if (!(defined($trafficLimit) && $trafficLimit == 0)) {
		if (!defined($trafficLimit)) {
			$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Bandwidth => Usage total: ".$accountingUsage->{'TotalDataUsage'}.
					"Mb (Cap: Prepaid, Topups: ".$trafficTopup."Mb)");
		} else {
			$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Bandwidth => Usage total: ".$accountingUsage->{'TotalDataUsage'}.
					"Mb (Cap: ".$trafficLimit."Mb, Topups: ".$trafficTopup."Mb)");
		}
	} else {
		$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Bandwidth => Usage total: ".$accountingUsage->{'TotalDataUsage'}.
				"Mb (Cap: Uncapped, Topups: ".$trafficTopup."Mb)");
	}


	#
	# Check if we've exceeded our limits
	#


	# Uptime..
	if (!(defined($uptimeLimit) && $uptimeLimit == 0)) {

		# Capped
		if ($accountingUsage->{'TotalSessionTime'} >= $alteredUptimeLimit) {
			$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Usage of ".$accountingUsage->{'TotalSessionTime'}.
					"Min exceeds allowed limit of ".$alteredUptimeLimit."Min. Capped.");
			return MOD_RES_NACK;
		}
	}

	# Traffic
	if (!(defined($trafficLimit) && $trafficLimit == 0)) {

		# Capped
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
