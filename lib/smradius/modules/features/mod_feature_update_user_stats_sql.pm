# Support for updating of user stats
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

package smradius::modules::features::mod_feature_update_user_stats_sql;

use strict;
use warnings;

# Modules we need
use smradius::constants;
use AWITPT::DB::DBLayer;
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
	Name => "Update User Stats",
	Init => \&init,

	# Accounting hook
	'Feature_Post-Accounting_hook' => \&updateUserStats
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
	$server->log(LOG_NOTICE,"[MOD_FEATURE_UPDATE_USER_STATS_SQL] Enabling database support");
	if (!$server->{'smradius'}->{'database'}->{'enabled'}) {
		$server->log(LOG_NOTICE,"[MOD_FEATURE_UPDATE_USER_STATS_SQL] Enabling database support.");
		$server->{'smradius'}->{'database'}->{'enabled'} = 1;
	}

	# Default configs...
	$config->{'update_user_stats_query'} = '
		UPDATE
			@TP@users
		SET
			PeriodKey = %{query.PeriodKey},
			TotalTraffic = %{query.TotalTraffic},
			TotalUptime = %{query.TotalUptime},
			NASIdentifier = %{request.NAS-Identifier}
		WHERE
			Username = %{user.Username}
	';

	# Setup SQL queries
	if (defined($scfg->{'mod_feature_update_user_stats_sql'})) {
		# Pull in queries
		if (defined($scfg->{'mod_feature_update_user_stats_sql'}->{'update_user_stats_query'}) &&
				$scfg->{'mod_feature_update_user_stats_sql'}->{'update_user_stats_query'} ne "") {
			if (ref($scfg->{'mod_feature_update_user_stats_sql'}->{'update_user_stats_query'}) eq "ARRAY") {
				$config->{'update_user_stats_query'} = join(' ',
						@{$scfg->{'mod_feature_update_user_stats_sql'}->{'update_user_stats_query'}});
			} else {
				$config->{'update_user_stats_query'} = $scfg->{'mod_feature_update_user_stats_sql'}->{'update_user_stats_query'};
			}
		}
	}
}


## @updateUserStats($server,$user,$packet)
# Post authentication hook
#
# @param server Server object
# @param user User data
# @param packet Radius packet
#
# @return Result
sub updateUserStats
{
	my ($server,$user,$packet) = @_;


	# Skip MAC authentication
	return MOD_RES_SKIP if (defined($user->{'_UserDB'}->{'Name'}) && 
			$user->{'_UserDB'}->{'Name'} eq "SQL User Database (MAC authentication)");
	
	$server->log(LOG_DEBUG,"[MOD_FEATURE_UPDATE_USER_STATS_SQL] UPDATE USER STATS HOOK");

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

	# Loop with plugins to find anything supporting getting of usage
	my $accountingUsage;
	foreach my $module (@{$server->{'module_list'}}) {
		# Do we have the correct plugin?
		if ($module->{'Accounting_getUsage'}) {
			$server->log(LOG_INFO,"[MOD_FEATURE_UPDATE_USER_STATS_SQL] Found plugin: '".$module->{'Name'}."'");
			# Fetch users session uptime & bandwidth used
			my $res = $module->{'Accounting_getUsage'}($server,$user,$packet);
			if (!defined($res)) {
				$server->log(LOG_ERR,"[MOD_FEATURE_UPDATE_USER_STATS_SQL] No usage data found for user '".$user->{'Username'}."'");
				return MOD_RES_SKIP;
			}

			$accountingUsage = $res;
		}
	}

	# Add to our template hash
	$template->{'query'}->{'TotalTraffic'} = $accountingUsage->{'TotalDataUsage'};
	$template->{'query'}->{'TotalUptime'} = $accountingUsage->{'TotalSessionTime'};

	# Replace template entries
	my (@dbDoParams) = templateReplace($config->{'update_user_stats_query'},$template);

	# Perform query
	my $sth = DBDo(@dbDoParams);
	if (!$sth) {
		$server->log(LOG_ERR,"[MOD_FEATURE_UPDATE_USER_STATS_SQL] Database query failed: ".AWITPT::DB::DBLayer::Error());
		return;
	}

	return MOD_RES_ACK;
}



1;
# vim: ts=4
