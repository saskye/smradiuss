# Capping support
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

package smradius::modules::features::mod_feature_capping;

use strict;
use warnings;

# Modules we need
use smradius::attributes;
use smradius::constants;
use smradius::logging;
use smradius::util;

use AWITPT::Util;
use List::Util qw( min );
use MIME::Lite;
use POSIX qw( floor );


# Load exporter
use base qw(Exporter);
our @EXPORT = qw(
);
our @EXPORT_OK = qw(
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
my $TRAFFIC_LIMIT_ATTRIBUTE = 'SMRadius-Capping-Traffic-Limit';
my $UPTIME_LIMIT_ATTRIBUTE = 'SMRadius-Capping-Uptime-Limit';

my $TRAFFIC_TOPUP_ATTRIBUTE = 'SMRadius-Capping-Traffic-Topup';
my $TIME_TOPUP_ATTRIBUTE = 'SMRadius-Capping-Uptime-Topup';

my $config;



## @internal
# Initialize module
sub init
{
	my $server = shift;
	my $scfg = $server->{'inifile'};


	# Defaults
	$config->{'enable_mikrotik'} = 0;
	$config->{'caveat_captrafzero'} = 0;

	# Setup SQL queries
	if (defined($scfg->{'mod_feature_capping'})) {
		# Check if option exists
		if (defined($scfg->{'mod_feature_capping'}{'enable_mikrotik'})) {
			# Pull in config
			if (defined(my $val = isBoolean($scfg->{'mod_feature_capping'}{'enable_mikrotik'}))) {
				if ($val) {
					$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] Mikrotik-specific vendor return attributes ENABLED");
					$config->{'enable_mikrotik'} = $val;
				}
			} else {
				$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] Value for 'enable_mikrotik' is invalid");
			}

			if (defined(my $val = isBoolean($scfg->{'mod_feature_capping'}{'caveat_captrafzero'}))) {
				if ($val) {
					$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] Caveat to swap '0' and -undef- for ".
							"SMRadius-Capping-Traffic-Limit ENABLED");
					$config->{'caveat_captrafzero'} = $val;
				}
			} else {
				$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] Value for 'caveat_captrafzero' is invalid");
			}
		}
	}

	return;
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

	my $uptimeLimit = _getAttributeKeyLimit($server,$user,$UPTIME_LIMIT_ATTRIBUTE);
	my $trafficLimit = _getAttributeKeyLimit($server,$user,$TRAFFIC_LIMIT_ATTRIBUTE);

	# Swap around 0 and undef if we need to apply the captrafzero caveat
	if ($config->{'caveat_captrafzero'}) {
		if (!defined($trafficLimit)) {
			$trafficLimit = 0;
		} elsif ($trafficLimit == 0) {
			$trafficLimit = undef;
		}
	}


	#
	# Get current traffic and uptime usage
	#

	my $accountingUsage = _getAccountingUsage($server,$user,$packet);
	if (!defined($accountingUsage)) {
		return MOD_RES_SKIP;
	}


	#
	# Get valid traffic and uptime topups
	#

	# Check if there was any data returned at all
	my $uptimeTopupAmount = _getConfigAttributeNumeric($server,$user,$TIME_TOPUP_ATTRIBUTE) // 0;
	my $trafficTopupAmount = _getConfigAttributeNumeric($server,$user,$TRAFFIC_TOPUP_ATTRIBUTE) // 0;


	#
	# Set the new uptime and traffic limits (limit, if any.. + topups)
	#

	# Uptime..
	# // is a defined operator,  $a ? defined($a) : $b
	my $uptimeLimitWithTopups = ($uptimeLimit // 0) + $uptimeTopupAmount;

	# Traffic..
	# // is a defined operator,  $a ? defined($a) : $b
	my $trafficLimitWithTopups = ($trafficLimit // 0) + $trafficTopupAmount;


	#
	# Do auto-topups for both traffic and uptime
	#

	my $autoTopupTrafficAdded = _doAutoTopup($server,$user,$accountingUsage->{'TotalDataUsage'},"traffic",
			$trafficLimitWithTopups,1);
	if (defined($autoTopupTrafficAdded)) {
		$trafficLimitWithTopups += $autoTopupTrafficAdded;
	}

	my $autoTopupUptimeAdded = _doAutoTopup($server,$user,$accountingUsage->{'TotalSessionTime'},"uptime",
			$uptimeLimitWithTopups,2);
	if (defined($autoTopupUptimeAdded)) {
		$uptimeLimitWithTopups += $autoTopupUptimeAdded;
	}


	#
	# Display our usages
	#

	_logUsage($server,$accountingUsage->{'TotalDataUsage'},$uptimeLimit,$uptimeTopupAmount,'traffic');
	_logUsage($server,$accountingUsage->{'TotalSessionTime'},$uptimeLimit,$uptimeTopupAmount,'uptime');


	#
	# Add conditional variables
	#

	addAttributeConditionalVariable($user,"SMRadius_Capping_TotalDataUsage",$accountingUsage->{'TotalDataUsage'});
	addAttributeConditionalVariable($user,"SMRadius_Capping_TotalSessionTime",$accountingUsage->{'TotalSessionTime'});


	#
	# Allow for capping overrides by client attribute
	#

	if (defined($user->{'ConfigAttributes'}->{'SMRadius-Config-Capping-Uptime-Multiplier'})) {
		my $multiplier = pop(@{$user->{'ConfigAttributes'}->{'SMRadius-Config-Capping-Uptime-Multiplier'}});

		my $newLimit = $uptimeLimitWithTopups * $multiplier;
		my $newSessionTime = $accountingUsage->{'TotalSessionTime'} * $multiplier;

		$uptimeLimitWithTopups = $newLimit;
		$accountingUsage->{'TotalSessionTime'} = $newSessionTime;

		$server->log(LOG_INFO,"[MOD_FEATURE_CAPPING] Client uptime multiplier '$multiplier' changes ".
				"uptime limit ('$uptimeLimitWithTopups' => '$newLimit'), ".
				"uptime usage ('".$accountingUsage->{'TotalSessionTime'}."' => '$newSessionTime')"
		);
	}
	if (defined($user->{'ConfigAttributes'}->{'SMRadius-Config-Capping-Traffic-Multiplier'})) {
		my $multiplier = pop(@{$user->{'ConfigAttributes'}->{'SMRadius-Config-Capping-Traffic-Multiplier'}});

		my $newLimit = $trafficLimitWithTopups * $multiplier;
		my $newDataUsage = $accountingUsage->{'TotalDataUsage'} * $multiplier;

		$trafficLimitWithTopups = $newLimit;
		$accountingUsage->{'TotalDataUsage'} = $newDataUsage;

		$server->log(LOG_INFO,"[MOD_FEATURE_CAPPING] Client traffic multiplier '$multiplier' changes ".
				"traffic limit ('$trafficLimitWithTopups' => '$newLimit'), ".
				"traffic usage ('".$accountingUsage->{'TotalDataUsage'}."' => '$newDataUsage')"
		);
	}


	#
	# Check if we've exceeded our limits
	#

	# Uptime...
	if (defined($uptimeLimit)) {

		# Check session time has not exceeded what we're allowed
		if ($accountingUsage->{'TotalSessionTime'} >= $uptimeLimitWithTopups) {
			$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Usage of ".$accountingUsage->{'TotalSessionTime'}.
					"min exceeds allowed limit of ".$uptimeLimitWithTopups."min");
			return MOD_RES_NACK;
		# Setup limits
		} else {
			# Check if we returning Mikrotik vattributes
			# FIXME: NK - this is not mikrotik specific
			if ($config->{'enable_mikrotik'}) {
				# FIXME: NK - We should cap the maximum total session time to that which is already set, if something is set
				# Setup reply attributes for Mikrotik HotSpots
				my %attribute = (
					'Name' => 'Session-Timeout',
					'Operator' => '=',
					'Value' => $uptimeLimitWithTopups - $accountingUsage->{'TotalSessionTime'}
				);
				setReplyAttribute($server,$user->{'ReplyAttributes'},\%attribute);
			}
		}
	}

	# Traffic
	if (defined($trafficLimit)) {

		# Capped
		if ($accountingUsage->{'TotalDataUsage'} >= $trafficLimitWithTopups) {
			$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Usage of ".$accountingUsage->{'TotalDataUsage'}.
					"Mbyte exceeds allowed limit of ".$trafficLimitWithTopups."Mbyte");
			return MOD_RES_NACK;
		# Setup limits
		} else {
			# Check if we returning Mikrotik vattributes
			if ($config->{'enable_mikrotik'}) {
				# Get remaining traffic
				my $remainingTraffic = $trafficLimitWithTopups - $accountingUsage->{'TotalDataUsage'};
				my $remainingTrafficLimit = ( $remainingTraffic % 4096 ) * 1024 * 1024;
				my $remainingTrafficGigawords = floor($remainingTraffic / 4096);

				# Setup reply attributes for Mikrotik HotSpots
				foreach my $attrName ('Recv','Xmit','Total') {
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
	return MOD_RES_SKIP if ($packet->rawattr('Acct-Status-Type') ne "1" && $packet->rawattr('Acct-Status-Type') ne "3");

	$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] POST ACCT HOOK");


	#
	# Get limits from attributes
	#

	my $uptimeLimit = _getAttributeKeyLimit($server,$user,$UPTIME_LIMIT_ATTRIBUTE);
	my $trafficLimit = _getAttributeKeyLimit($server,$user,$TRAFFIC_LIMIT_ATTRIBUTE);

	# Swap around 0 and undef if we need to apply the captrafzero caveat
	if ($config->{'caveat_captrafzero'}) {
		if (!defined($trafficLimit)) {
			$trafficLimit = 0;
		} elsif ($trafficLimit == 0) {
			$trafficLimit = undef;
		}
	}

	#
	# Get current traffic and uptime usage
	#
	#
	my $accountingUsage = _getAccountingUsage($server,$user,$packet);
	if (!defined($accountingUsage)) {
		return MOD_RES_SKIP;
	}


	#
	# Get valid traffic and uptime topups
	#

	# Check if there was any data returned at all
	my $uptimeTopupAmount = _getConfigAttributeNumeric($server,$user,$TIME_TOPUP_ATTRIBUTE) // 0;
	my $trafficTopupAmount = _getConfigAttributeNumeric($server,$user,$TRAFFIC_TOPUP_ATTRIBUTE) // 0;


	#
	# Set the new uptime and traffic limits (limit, if any.. + topups)
	#

	# Uptime..
	# // is a defined operator,  $a ? defined($a) : $b
	my $uptimeLimitWithTopups = ($uptimeLimit // 0) + $uptimeTopupAmount;

	# Traffic..
	# // is a defined operator,  $a ? defined($a) : $b
	my $trafficLimitWithTopups = ($trafficLimit // 0) + $trafficTopupAmount;


	#
	# Do auto-topups for both traffic and uptime
	#

	my $autoTopupTrafficAdded = _doAutoTopup($server,$user,$accountingUsage->{'TotalDataUsage'},"traffic",
			$trafficLimitWithTopups,1);
	if (defined($autoTopupTrafficAdded)) {
		$trafficLimitWithTopups += $autoTopupTrafficAdded;
	}

	my $autoTopupUptimeAdded = _doAutoTopup($server,$user,$accountingUsage->{'TotalSessionTime'},"uptime",
			$uptimeLimitWithTopups,2);
	if (defined($autoTopupUptimeAdded)) {
		$uptimeLimitWithTopups += $autoTopupUptimeAdded;
	}


	#
	# Display our usages
	#

	_logUsage($server,$accountingUsage->{'TotalDataUsage'},$uptimeLimit,$uptimeTopupAmount,'traffic');
	_logUsage($server,$accountingUsage->{'TotalSessionTime'},$uptimeLimit,$uptimeTopupAmount,'uptime');


	#
	# Add conditional variables
	#

	# Add attribute conditionals BEFORE override
	addAttributeConditionalVariable($user,"SMRadius_Capping_TotalDataUsage",$accountingUsage->{'TotalDataUsage'});
	addAttributeConditionalVariable($user,"SMRadius_Capping_TotalSessionTime",$accountingUsage->{'TotalSessionTime'});


	#
	# Allow for capping overrides by client attribute
	#

	if (defined($user->{'ConfigAttributes'}->{'SMRadius-Config-Capping-Uptime-Multiplier'})) {
		my $multiplier = pop(@{$user->{'ConfigAttributes'}->{'SMRadius-Config-Capping-Uptime-Multiplier'}});
		my $newLimit = $uptimeLimitWithTopups * $multiplier;
		$server->log(LOG_INFO,"[MOD_FEATURE_CAPPING] Client cap uptime multiplier '$multiplier' changes limit ".
				"from '$uptimeLimitWithTopups' to '$newLimit'");
		$uptimeLimitWithTopups = $newLimit;
	}
	if (defined($user->{'ConfigAttributes'}->{'SMRadius-Config-Capping-Traffic-Multiplier'})) {
		my $multiplier = pop(@{$user->{'ConfigAttributes'}->{'SMRadius-Config-Capping-Traffic-Multiplier'}});
		my $newLimit = $trafficLimitWithTopups * $multiplier;
		$server->log(LOG_INFO,"[MOD_FEATURE_CAPPING] Client cap traffic multiplier '$multiplier' changes limit ".
				"from '$trafficLimitWithTopups' to '$newLimit'");
		$trafficLimitWithTopups = $newLimit;
	}


	#
	# Check if we've exceeded our limits
	#

	# Uptime..
	if (defined($uptimeLimit)) {

		# Capped
		if ($accountingUsage->{'TotalSessionTime'} >= $uptimeLimitWithTopups) {
			$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Usage of ".$accountingUsage->{'TotalSessionTime'}.
					"min exceeds allowed limit of ".$uptimeLimitWithTopups."min");
			return MOD_RES_NACK;
		}
	}

	# Traffic
	if (defined($trafficLimit)) {

		# Capped
		if ($accountingUsage->{'TotalDataUsage'} >= $trafficLimitWithTopups) {
			$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Usage of ".$accountingUsage->{'TotalDataUsage'}.
					"Mbyte exceeds allowed limit of ".$trafficLimitWithTopups."Mbyte");
			return MOD_RES_NACK;
		}
	}

	return MOD_RES_ACK;
}



## @internal
# Code snippet to grab the current uptime limit by processing the user attributes
sub _getAttributeKeyLimit
{
	my ($server,$user,$attributeKey) = @_;


	# Short circuit return if we don't have the uptime key set
	return if (!defined($user->{'Attributes'}->{$attributeKey}));

	# Short circuit if we do not have a valid attribute operator: ':='
	if (!defined($user->{'Attributes'}->{$attributeKey}->{':='})) {
		$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] No valid operators for attribute '".
				$user->{'Attributes'}->{$attributeKey}."'");
		return;
	}

	$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Attribute '".$attributeKey."' is defined");

	# Check for valid attribute value
	if (!defined($user->{'Attributes'}->{$attributeKey}->{':='}->{'Value'}) ||
			$user->{'Attributes'}->{$attributeKey}->{':='}->{'Value'} !~ /^\d+$/) {
		$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] Attribute '".$user->{'Attributes'}->{$attributeKey}->{':='}->{'Value'}.
				"' is NOT a numeric value");
		return;
	}

	return $user->{'Attributes'}->{$attributeKey}->{':='}->{'Value'};
}



## @internal
# Code snippet to grab the current accounting usage of a user
sub _getAccountingUsage
{
	my ($server,$user,$packet) = @_;


	foreach my $module (@{$server->{'module_list'}}) {
		# Do we have the correct plugin?
		if (defined($module->{'Accounting_getUsage'})) {
			$server->log(LOG_INFO,"[MOD_FEATURE_CAPPING] Found plugin: '".$module->{'Name'}."'");
			# Fetch users session uptime & bandwidth used
			if (my $res = $module->{'Accounting_getUsage'}($server,$user,$packet)) {
				return $res;
			}
			$server->log(LOG_ERR,"[MOD_FEATURE_CAPPING] No usage data found for user '".$user->{'Username'}."'");
		}
	}

	return;
}



## @internal
# Code snippet to log our uptime usage
sub _logUsage
{
	my ($server,$accountingUsage,$limit,$topupAmount,$type) = @_;


	my $typeKey = ucfirst($type);

	# Check if our limit is defined
	if (defined($limit) && $limit == 0) {
		$limit = '-topup-';
	} else {
		$limit = '-none-';
	}

	$server->log(LOG_INFO,"[MOD_FEATURE_CAPPING] Capping information [type: %s, total: %s, limit: %s, topups: %s]",
			$type,$accountingUsage,$limit,$topupAmount);

	return;
}



## @internal
# Function snippet to return a user attribute
sub _getConfigAttributeNumeric
{
	my ($server,$user,$attributeName) = @_;


	# Short circuit if the attribute does not exist
	return 0 if (!defined($user->{'ConfigAttributes'}->{$attributeName}));

	$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Config attribute '".$attributeName."' is defined");
	# Check for value
	if (!defined($user->{'ConfigAttributes'}->{$attributeName}->[0])) {
		$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] Config attribute '".$attributeName."' has no value");
		return 0;
	}

	# Is it a number?
	if ($user->{'ConfigAttributes'}->{$attributeName}->[0] !~ /^\d+$/) {
		$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] Config attribute '".$user->{'ConfigAttributes'}->{$attributeName}->[0].
				"' is NOT a numeric value");
		return 0;
	}

	return $user->{'ConfigAttributes'}->{$attributeName}->[0];
}



## @internal
# Function snippet to return a attribute
sub _getAttribute
{
	my ($server,$user,$attributeName) = @_;


	# Check the attribute exists
	return if (!defined($user->{'Attributes'}->{$attributeName}));

	$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] User attribute '".$attributeName."' is defined");

	# Check the required operator is present in this case :=
	if (!defined($user->{'Attributes'}->{$attributeName}->{':='})) {
		$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] User attribute '".$attributeName."' has no ':=' operator");
		return;
	}

	# Check the operator value is defined...
	if (!defined($user->{'Attributes'}->{$attributeName}->{':='}->{'Value'})) {
		$server->log(LOG_NOTICE,"[MOD_FEATURE_CAPPING] User attribute '".$attributeName."' has no value");
		return;
	}

	return $user->{'Attributes'}->{$attributeName}->{':='}->{'Value'};
}



## @internal
# Function which impelments our auto-topup functionality
sub _doAutoTopup
{
	my ($server,$user,$accountingUsage,$type,$usageLimit,$topupType) = @_;
	my $scfg = $server->{'inifile'};


	# Get the key, which has the first letter uppercased
	my $typeKey = ucfirst($type);

	# Booleanize the attribute and check if its enabled
	if (my $enabled = booleanize(_getAttribute($server,$user,"SMRadius-AutoTopup-$typeKey-Enabled"))) {
		$server->log(LOG_INFO,'[MOD_FEATURE_CAPPING] AutoToups for %s is enabled',$type);
	} else {
		$server->log(LOG_DEBUG,'[MOD_FEATURE_CAPPING] AutoToups for %s is not enabled',$type);
		return;
	}

	# Do sanity checks on the auto-topup amount
	my $autoTopupAmount = _getAttribute($server,$user,"SMRadius-AutoTopup-$typeKey-Amount");
	if (!defined($autoTopupAmount)) {
		$server->log(LOG_WARN,'[MOD_FEATURE_CAPPING] SMRadius-AutoToup-%s-Amount must have a value',$typeKey);
		return;
	}
	if (!isNumber($autoTopupAmount)){
		$server->log(LOG_WARN,'[MOD_FEATURE_CAPPING] SMRadius-AutoToup-%s-Amount must be a number and be > 0, instead it was '.
				'\'%s\', IGNORING SMRadius-AutoTopup-%s-Enabled',$typeKey,$autoTopupAmount,$typeKey);
		return;
	}

	# Do sanity checks on the auto-topup threshold
	my $autoTopupThreshold = _getAttribute($server,$user,"SMRadius-AutoTopup-$typeKey-Threshold");
	if (defined($autoTopupThreshold) && !isNumber($autoTopupThreshold)){
		$server->log(LOG_WARN,'[MOD_FEATURE_CAPPING] SMRadius-AutoToup-%s-Threshold must be a number and be > 0, instead it was '.
				'\'%s\', IGNORING SMRadius-AutoTopup-%s-Threshold',$typeKey,$autoTopupAmount,$typeKey);
		$autoTopupThreshold = undef;
	}

	# Check that if the auto-topup limit is defined, that it is > 0
	my $autoTopupLimit = _getAttribute($server,$user,"SMRadius-AutoTopup-$typeKey-Limit");
	if (defined($autoTopupLimit) && !isNumber($autoTopupLimit)) {
		$server->log(LOG_WARN,'[MOD_FEATURE_CAPPING] SMRadius-AutoToup-%s-Limit must be a number and be > 0, instead it was '.
				'\'%s\', IGNORING SMRadius-AutoTopup-%s-Enabled',$typeKey,$autoTopupAmount,$typeKey);
		return;
	}

	# Pull in ahow many auto-topups were already added
	my $autoTopupsAdded = _getConfigAttributeNumeric($server,$user,"SMRadius-Capping-$typeKey-AutoTopup") // 0;

	# Default to an auto-topup threshold of the topup amount divided by two if none has been provided
	$autoTopupThreshold //= floor($autoTopupAmount / 2);

	# Check if we're still within our usage limit
	return if ($accountingUsage + $autoTopupThreshold < $usageLimit + $autoTopupsAdded);

	# Check the difference between our accounting usage and our usage limit
	my $usageDelta = $accountingUsage - $usageLimit;
	# Make sure our delta is at least 0
	$usageDelta = 0 if ($usageDelta < 0);

	# Calculate how many topups are needed
	my $autoTopupsRequired = floor($usageDelta / $autoTopupAmount) + 1;

	# Default the topups to add to the number required
	my $autoTopupsToAdd = $autoTopupsRequired;

	# If we have an auto-topup limit, recalculate how many we must add... maybe it exceeds
	if (defined($autoTopupLimit)) {
		my $autoTopupsAllowed = floor(($autoTopupLimit - $autoTopupsAdded) / $autoTopupAmount);
		$autoTopupsToAdd = min($autoTopupsRequired,$autoTopupsAllowed);
		# We cannot add a negative amount of auto-topups, if we have a negative amount, we have hit our limit
		$autoTopupsToAdd = 0 if ($autoTopupsToAdd < 0);
	}

	# Total topup amount
	my $autoTopupsToAddAmount = $autoTopupsToAdd * $autoTopupAmount;

	# The datetime now
	my $now = DateTime->now->set_time_zone($server->{'smradius'}->{'event_timezone'});
	# Use truncate to set all values after 'month' to their default values
	my $thisMonth = $now->clone()->truncate( to => "month" );
	# This month, in string form
	my $thisMonth_str = $thisMonth->strftime("%Y-%m-%d");
	# Next month..
	my $nextMonth = $thisMonth->clone()->add( months => 1 );
	my $nextMonth_str = $nextMonth->strftime("%Y-%m-%d");

	# Lets see if a module accepts to add a topup
	my $res;
	foreach my $module (@{$server->{'module_list'}}) {
		# Do we have the correct plugin?
		if (defined($module->{'Feature_Config_Topop_add'})) {
			$server->log(LOG_INFO,"[MOD_FEATURE_CAPPING] Found plugin: '".$module->{'Name'}."'");
			# Try add topup
			$res = $module->{'Feature_Config_Topop_add'}($server,$user,$thisMonth_str,$nextMonth_str,
					($topupType | 4),$autoTopupAmount);
			# Skip to end if we added a topup
			if ($res == MOD_RES_ACK) {
				my $topupsRemaining = $autoTopupsToAdd - 1;
				while ($topupsRemaining > 0) {
					# Try add another topup
					$res = $module->{'Feature_Config_Topop_add'}($server,$user,$thisMonth_str,$nextMonth_str,
							($topupType | 4),$autoTopupAmount);
					$topupsRemaining--;
				}
				last;
			}
		}
	}
	# If not, return undef
	if (!defined($res) || $res != MOD_RES_ACK) {
		$server->log(LOG_WARN,'[MOD_FEATURE_CAPPING] Auto-Topup(s) cannot be added, no module replied with ACK');
		return;
	}

	$server->log(LOG_INFO,'[MOD_FEATURE_CAPPING] Auto-Topups added [type: %s, threshold: %s, amount: %s, required: %s, limit: %s, added: %s]',
			$type,$autoTopupThreshold,$autoTopupAmount,$autoTopupsRequired,$autoTopupLimit,$autoTopupsToAdd);

	# Grab notify destinations
	my $notify;
	if (!defined($notify = _getAttribute($server,$user,"SMRadius-AutoTopup-$typeKey-Notify"))) {
		$server->log(LOG_INFO,'[MOD_FEATURE_CAPPING] AutoToups notify destination is not specified, NOT notifying');
		goto END;
	}
	$server->log(LOG_INFO,'[MOD_FEATURE_CAPPING] AutoToups notify destination is \'%s\'',$notify);

	# Grab notify template
	my $notifyTemplate;
	if (!defined($notifyTemplate = _getAttribute($server,$user,"SMRadius-AutoTopup-$typeKey-NotifyTemplate"))) {
		$server->log(LOG_INFO,'[MOD_FEATURE_CAPPING] AutoToups notify template is not specified, NOT notifying');
		goto END;
	}

	# NOTE: $autoTopupToAdd and autoTopupsToAddAmount will be 0 if no auto-topups were added

	# Create variable hash to pass to TT
	my $variables = {
		'user' => {
			'ID' => $user->{'ID'},
			'username' => $user->{'Username'},
		},
		'usage' => {
			'total' => $accountingUsage,
			'limit' => $usageLimit,
		},
		'autotopup' => {
			'amount' => $autoTopupAmount,
			'limit' => $autoTopupLimit,
			'added' => $autoTopupsAdded,
			'toAdd' => $autoTopupsToAdd,
			'toAddAmount' => $autoTopupsToAddAmount,
		},
	};

	# Split off notification targets
	my @notificationTargets = split(/[,;\s]+/,$notify);

	foreach my $notifyTarget (@notificationTargets) {
		# Parse template
		my ($notifyMsg,$error) = quickTemplateToolkit($notifyTemplate,{
				%{$variables},
				'notify' => { 'target' => $notifyTarget }
		});

		# Check if we have a result, if not, report the error
		if (!defined($notifyMsg)) {
			my $errorMsg = $error->info();
			$errorMsg =~ s/\r?\n/\\n/g;
			$server->log(LOG_WARN,'[MOD_FEATURE_CAPPING] AutoToups notify template parsing failed: %s',$errorMsg);
			next;
		}

		my %messageHeaders = ();

		# Split message into lines
		my @lines = split(/\r?\n/,$notifyMsg);
		while (defined($lines[0]) && (my $line = $lines[0]) =~ /(\S+): (.*)/) {
			my ($header,$value) = ($1,$2);
			$messageHeaders{$header} = $value;
			# Remove line
			shift(@lines);
			# Last if our next line is undefined
			last if (!defined($lines[0]));
			# If the next line is blank, remove it, and continue below
			if ($lines[0] =~ /^\s*$/) {
				# Remove blank line
				shift(@lines);
				last;
			}
		}

		# Create message
		my $msg = MIME::Lite->new(
			'Type' => 'multipart/mixed',
			'Date' => $now->strftime('%a, %d %b %Y %H:%M:%S %z'),
			%messageHeaders
		);

		# Attach body
		$msg->attach(
			'Type' => 'TEXT',
			'Encoding' => '8bit',
			'Data' => join("\n",@lines),
		);

		# Send email
		my $smtpServer = $scfg->{'server'}{'smtp_server'} // 'localhost';
		eval { $msg->send("smtp",$smtpServer); };
		if (my $error = $@) {
			$server->log(LOG_WARN,"[MOD_FEATURE_CAPPING] Email sending failed: '%s'",$error);
		}

	}

END:
	return $autoTopupsToAddAmount;
}



1;
# vim: ts=4
