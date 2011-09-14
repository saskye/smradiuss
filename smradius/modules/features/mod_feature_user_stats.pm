# Support for updating user data
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

package smradius::modules::features::mod_feature_user_stats;

use strict;
use warnings;

# Modules we need
use smradius::constants;
use awitpt::db::dblayer;
use smradius::logging;
use smradius::util;

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
	Name => "User Stats",
	Init => \&init,

	# Accounting hook
	'Feature_Post-Accounting_hook' => \&updateUserStats,
#	'Feature_Post-Accounting_hook' => \&getUserStats
};



## @internal
# Initialize module
sub init
{
	my $server = shift;
}


## @updateUserStats($server,$user,$packet)
# Post accounting hook
#
# @param server Server object
# @param user User data
# @param packet Radius packet
#
# @return Result
sub updateUserStats
{
	my ($server,$user,$packet) = @_;

	# Variables we are going to set
	my $currentUsage;

	# Loop with plugins to find anything supporting getting of usage
	foreach my $module (@{$server->{'module_list'}}) {
		# Do we have the correct plugin?
		if ($module->{'Accounting_getUsage'}) {
			$server->log(LOG_INFO,"[MOD_USERS_DATA] Found plugin: '".$module->{'Name'}."'");
			# Fetch users session uptime & bandwidth used
			my $res = $module->{'Accounting_getUsage'}($server,$user,$packet);
			if (!defined($res)) {
				$server->log(LOG_ERR,"[MOD_USERS_DATA] No usage data found for user '".$user->{'Username'}."'");
				return MOD_RES_SKIP;
			}

			$currentUsage = $res;
		}
	}

	# Do we have the correct plugin?
	if ($user->{'_UserDB'}->{'Users_data_set'}) {

		$server->log(LOG_INFO,"[MOD_USERS_DATA] Found plugin: '".$user->{'_UserDB'}->{'Name'}."'");

		# Set user traffic usage
		my $res = $user->{'_UserDB'}->{'Users_data_set'}($server,$user,
				'mod_feature_user_stats','CurrentMonthTotalTraffic',
				$currentUsage->{'TotalDataUsage'}
		);
		if (!defined($res)) {
			$server->log(LOG_ERR,"[MOD_USERS_DATA] Failed to store current month traffic usage for user '"
					.$user->{'Username'}."'");
			return MOD_RES_SKIP;
		}

		# Set user uptime usage
		$res = $user->{'_UserDB'}->{'Users_data_set'}($server,$user,
				'mod_feature_user_stats','CurrentMonthTotalUptime',
				$currentUsage->{'TotalSessionTime'}
		);
		if (!defined($res)) {
			$server->log(LOG_ERR,"[MOD_USERS_DATA] Failed to store current month uptime usage for user '"
					.$user->{'Username'}."'");
			return MOD_RES_SKIP;
		}

		# Set NAS-Identifier
		if (defined(my $NASIdentifier = $packet->rawattr('NAS-Identifier'))) {
			$res = $user->{'_UserDB'}->{'Users_data_set'}($server,$user,
					'mod_feature_user_stats','NAS-Identifier',
					$NASIdentifier
			);
			if (!defined($res)) {
				$server->log(LOG_ERR,"[MOD_USERS_DATA] Failed to store NAS-Identifier for user '".$user->{'Username'}."'");
				return MOD_RES_SKIP;
			}
		}

		# Set Framed-IP-Address
		if (defined(my $framedIPAddress = $packet->rawattr('Framed-IP-Address'))) {
			$res = $user->{'_UserDB'}->{'Users_data_set'}($server,$user,
					'mod_feature_user_stats','Framed-IP-Address',
					$framedIPAddress
			);
			if (!defined($res)) {
				$server->log(LOG_ERR,"[MOD_USERS_DATA] Failed to store Framed-IP-Address for user '".$user->{'Username'}."'");
				return MOD_RES_SKIP;
			}
		}
	}

	return MOD_RES_ACK;
}


1;
# vim: ts=4
