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
use POSIX qw(floor);


# Load exporter
use base qw(Exporter);
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

my $TRAFFIC_AUTOTOPUP_ATTRIBUTE = 'SMRadius-Capping-Traffic-AutoTopup';
my $TIME_AUTOTOPUP_ATTRIBUTE = 'SMRadius-Capping-Uptime-AutoTopup';

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
	my $uptimeAutoTopupAmount = _getConfigAttributeNumeric($server,$user,$TIME_AUTOTOPUP_ATTRIBUTE) // 0;
	my $trafficAutoTopupAmount = _getConfigAttributeNumeric($server,$user,$TRAFFIC_AUTOTOPUP_ATTRIBUTE) // 0;


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
	# Display our usages
	#
	_logUptimeUsage($server,$accountingUsage,$uptimeLimit,$uptimeTopupAmount);
	_logTrafficUsage($server,$accountingUsage,$trafficLimit,$trafficTopupAmount);


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

	# Uptime..
	if (!defined($uptimeLimit) || $uptimeLimit > 0) {

		# Check session time has not exceeded what we're allowed
		if ($accountingUsage->{'TotalSessionTime'} >= $uptimeLimitWithTopups) {
			$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Usage of ".$accountingUsage->{'TotalSessionTime'}.
					"min exceeds allowed limit of ".$uptimeLimitWithTopups."min");
			return MOD_RES_NACK;
		# Setup limits
		} else {
			# Check if we returning Mikrotik vattributes
			# FIXME: NK - this is not mikrotik specific
			if (defined($config->{'enable_mikrotik'})) {
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
	if (!defined($trafficLimit) || $trafficLimit > 0) {

		# Capped
		if ($accountingUsage->{'TotalDataUsage'} >= $trafficLimitWithTopups) {
			$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Usage of ".$accountingUsage->{'TotalDataUsage'}.
					"Mbyte exceeds allowed limit of ".$trafficLimitWithTopups."Mbyte");
			return MOD_RES_NACK;
		# Setup limits
		} else {
			# Check if we returning Mikrotik vattributes
			if (defined($config->{'enable_mikrotik'})) {
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
	my $uptimeAutoTopupAmount = _getConfigAttributeNumeric($server,$user,$TIME_AUTOTOPUP_ATTRIBUTE) // 0;
	my $trafficAutoTopupAmount = _getConfigAttributeNumeric($server,$user,$TRAFFIC_AUTOTOPUP_ATTRIBUTE) // 0;


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
	# Display our usages
	#

	_logUptimeUsage($server,$accountingUsage,$uptimeLimit,$uptimeTopupAmount);
	_logTrafficUsage($server,$accountingUsage,$trafficLimit,$trafficTopupAmount);


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
	if (!defined($uptimeLimit) || $uptimeLimit > 0) {

		# Capped
		if ($accountingUsage->{'TotalSessionTime'} >= $uptimeLimitWithTopups) {
			$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Usage of ".$accountingUsage->{'TotalSessionTime'}.
					"min exceeds allowed limit of ".$uptimeLimitWithTopups."min");
			return MOD_RES_NACK;
		}
	}

	# Traffic
	if (!defined($trafficLimit) || $trafficLimit > 0) {

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
sub _logUptimeUsage
{
	my ($server,$accountingUsage,$uptimeLimit,$uptimeTopupAmount) = @_;


	# Check if our limit is defined
	if (!defined($uptimeLimit)) {
		$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Uptime => Usage total: ".$accountingUsage->{'TotalSessionTime'}.
				"min (Limit: Prepaid, Topups: ".$uptimeTopupAmount."min)");
		return;
	}

	# If so, check if its > 0, which would depict its capped
	if ($uptimeLimit > 0) {
		$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Uptime => Usage total: ".$accountingUsage->{'TotalSessionTime'}.
				"min (Limit: ".$uptimeLimit."min, Topups: ".$uptimeTopupAmount."min)");
	} else {
		$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Uptime => Usage total: ".$accountingUsage->{'TotalSessionTime'}.
				"min (Limit: none, Topups: ".$uptimeTopupAmount."min)");
	}

	return;
}



## @internal
# Code snippet to log our traffic usage
sub _logTrafficUsage
{
	my ($server,$accountingUsage,$trafficLimit,$trafficTopupAmount) = @_;


	# Check if our limit is defined
	if (!defined($trafficLimit)) {
		$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Bandwidth => Usage total: ".$accountingUsage->{'TotalDataUsage'}.
				"Mbyte (Limit: Prepaid, Topups: ".$trafficTopupAmount."Mbyte)");
		return;
	}

	# If so, check if its > 0, which would depict its capped
	if ($trafficLimit > 0) {
		$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Bandwidth => Usage total: ".$accountingUsage->{'TotalDataUsage'}.
				"Mbyte (Limit: ".$trafficLimit."Mbyte, Topups: ".$trafficTopupAmount."Mbyte)");
	} else {
		$server->log(LOG_DEBUG,"[MOD_FEATURE_CAPPING] Bandwidth => Usage total: ".$accountingUsage->{'TotalDataUsage'}.
				"Mbyte (Limit: none, Topups: ".$trafficTopupAmount."Mbyte)");
	}

	return;
}



## @internal
# Function snippet to return a numeric configuration attribute
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



1;
# vim: ts=4