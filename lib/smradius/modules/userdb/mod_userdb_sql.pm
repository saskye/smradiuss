# SQL user database support
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

package smradius::modules::userdb::mod_userdb_sql;

use strict;
use warnings;

# Modules we need
use AWITPT::Cache;
use AWITPT::DB::DBLayer;
use AWITPT::Util;
use smradius::attributes;
use smradius::constants;
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
	Name => "SQL User Database",
	Init => \&init,

	# User database
	User_find => \&find,
	User_get => \&get,

	# Users data
	Users_data_set => \&data_set,
	Users_data_get => \&data_get,

	# Cleanup run by smadmin
	CleanupOrder => 95,
	Cleanup => \&cleanup
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
	$config->{'userdb_find_query'} = '
		SELECT
			ID, Disabled
		FROM
			@TP@users
		WHERE
			Username = %{user.Username}
	';

	$config->{'userdb_get_group_attributes_query'} = '
		SELECT
			@TP@group_attributes.Name, @TP@group_attributes.Operator, @TP@group_attributes.Value
		FROM
			@TP@group_attributes, @TP@users_to_groups
		WHERE
			@TP@users_to_groups.UserID = %{user.ID}
			AND @TP@group_attributes.GroupID = @TP@users_to_groups.GroupID
			AND @TP@group_attributes.Disabled = 0
	';

	$config->{'userdb_get_user_attributes_query'} = '
		SELECT
			Name, Operator, Value
		FROM
			@TP@user_attributes
		WHERE
			UserID = %{user.ID}
			AND Disabled = 0
	';

	$config->{'users_data_set_query'} = '
		INSERT INTO
			@TP@users_data (UserID, LastUpdated, Name, Value)
		VALUES
			(
				%{user.ID},
				%{query.LastUpdated},
				%{query.Name},
				%{query.Value}
			)
	';

	$config->{'users_data_update_query'} = '
		UPDATE
			@TP@users_data
		SET
			LastUpdated = %{query.LastUpdated},
			Value = %{query.Value}
		WHERE
			UserID = %{user.ID}
			AND Name = %{query.Name}
	';

	$config->{'users_data_get_query'} = '
		SELECT
			LastUpdated, Name, Value
		FROM
			@TP@users_data
		WHERE
			UserID = %{user.ID}
			AND Name = %{query.Name}
	';

	$config->{'users_data_delete_query'} = '
		DELETE FROM
			@TP@users_data
		WHERE
			UserID = %{user.ID}
			AND Name = %{query.Name}
	';

	# Default cache time for user data
	$config->{'userdb_data_cache_time'} = 300;

	# Setup SQL queries
	if (defined($scfg->{'mod_userdb_sql'})) {
		# Pull in queries
		if (defined($scfg->{'mod_userdb_sql'}->{'userdb_find_query'}) &&
				$scfg->{'mod_userdb_sql'}->{'userdb_find_query'} ne "") {
			if (ref($scfg->{'mod_userdb_sql'}->{'userdb_find_query'}) eq "ARRAY") {
				$config->{'userdb_find_query'} = join(' ', @{$scfg->{'mod_userdb_sql'}->{'userdb_find_query'}});
			} else {
				$config->{'userdb_find_query'} = $scfg->{'mod_userdb_sql'}->{'userdb_find_query'};
			}
		}

		if (defined($scfg->{'mod_userdb_sql'}->{'userdb_get_group_attributes_query'}) &&
				$scfg->{'mod_userdb_sql'}->{'userdb_get_group_attributes_query'} ne "") {
			if (ref($scfg->{'mod_userdb_sql'}->{'userdb_get_group_attributes_query'}) eq "ARRAY") {
				$config->{'userdb_get_group_attributes_query'} = join(' ',
						@{$scfg->{'mod_userdb_sql'}->{'userdb_get_group_attributes_query'}});
			} else {
				$config->{'userdb_get_group_attributes_query'} =
						$scfg->{'mod_userdb_sql'}->{'userdb_get_group_attributes_query'};
			}
		}

		if (defined($scfg->{'mod_userdb_sql'}->{'userdb_get_user_attributes_query'}) &&
				$scfg->{'mod_userdb_sql'}->{'userdb_get_user_attributes_query'} ne "") {
			if (ref($scfg->{'mod_userdb_sql'}->{'userdb_get_user_attributes_query'}) eq "ARRAY") {
				$config->{'userdb_get_user_attributes_query'} = join(' ',
						@{$scfg->{'mod_userdb_sql'}->{'userdb_get_user_attributes_query'}});
			} else {
					$config->{'userdb_get_user_attributes_query'} =
						$scfg->{'mod_userdb_sql'}->{'userdb_get_user_attributes_query'};
			}
		}

		if (defined($scfg->{'mod_userdb_sql'}->{'users_data_set_query'}) &&
				$scfg->{'mod_userdb_sql'}->{'users_data_set_query'} ne "") {
			if (ref($scfg->{'mod_userdb_sql'}->{'users_data_set_query'}) eq "ARRAY") {
				$config->{'users_data_set_query'} = join(' ',
						@{$scfg->{'mod_userdb_sql'}->{'users_data_set_query'}});
			} else {
					$config->{'users_data_set_query'} = $scfg->{'mod_userdb_sql'}->{'users_data_set_query'};
			}
		}

		if (defined($scfg->{'mod_userdb_sql'}->{'users_data_update_query'}) &&
				$scfg->{'mod_userdb_sql'}->{'users_data_update_query'} ne "") {
			if (ref($scfg->{'mod_userdb_sql'}->{'users_data_update_query'}) eq "ARRAY") {
				$config->{'users_data_update_query'} = join(' ',
						@{$scfg->{'mod_userdb_sql'}->{'users_data_update_query'}});
			} else {
					$config->{'users_data_update_query'} = $scfg->{'mod_userdb_sql'}->{'users_data_update_query'};
			}
		}

		if (defined($scfg->{'mod_userdb_sql'}->{'users_data_get_query'}) &&
				$scfg->{'mod_userdb_sql'}->{'users_data_get_query'} ne "") {
			if (ref($scfg->{'mod_userdb_sql'}->{'users_data_get_query'}) eq "ARRAY") {
				$config->{'users_data_get_query'} = join(' ',
						@{$scfg->{'mod_userdb_sql'}->{'users_data_get_query'}});
			} else {
					$config->{'users_data_get_query'} = $scfg->{'mod_userdb_sql'}->{'users_data_get_query'};
			}
		}

		if (defined($scfg->{'mod_userdb_sql'}->{'users_data_delete_query'}) &&
				$scfg->{'mod_userdb_sql'}->{'users_data_delete_query'} ne "") {
			if (ref($scfg->{'mod_userdb_sql'}->{'users_data_delete_query'}) eq "ARRAY") {
				$config->{'users_data_delete_query'} = join(' ',
						@{$scfg->{'mod_userdb_sql'}->{'users_data_delete_query'}});
			} else {
					$config->{'users_data_delete_query'} = $scfg->{'mod_userdb_sql'}->{'users_data_delete_query'};
			}
		}

		if (defined($scfg->{'mod_userdb_sql'}->{'userdb_data_cache_time'})) {
			if ($scfg->{'mod_userdb_sql'}{'userdb_data_cache_time'} =~ /^\s*(yes|true|1)\s*$/i) {
				# Default?
			} elsif ($scfg->{'mod_userdb_sql'}{'userdb_data_cache_time'} =~ /^\s*(no|false|0)\s*$/i) {
				$config->{'userdb_data_cache_time'} = undef;
			} elsif ($scfg->{'mod_userdb_sql'}{'userdb_data_cache_time'} =~ /^[0-9]+$/) {
				$config->{'userdb_data_cache_time'} = $scfg->{'mod_userdb_sql'}{'userdb_data_cache_time'};
			} else {
				$server->log(LOG_NOTICE,"[MOD_USERDB_SQL] Value for 'userdb_data_cache_time' is invalid");
			}
		}
	}

	# Log this for info sake
	if (defined($config->{'userdb_data_cache_time'})) {
		$server->log(LOG_NOTICE,"[MOD_USERDB_SQL] Users data caching ENABLED, cache time is %ds.",
				$config->{'userdb_data_cache_time'});
	} else {
		$server->log(LOG_NOTICE,"[MOD_USERDB_SQL] Users caching DISABLED");
	}
}

## @find
# Try find a user
#
# @param server Server object
# @param user SMRadius user hash
# @li Username Username of the user we want
# @param packet Radius packet
#
# @return _UserDB_Data Hash of db query, this is stored in the $user->{'_UserDB_Data'} hash item
sub find
{
	my ($server,$user,$packet) = @_;

	# Build template
	my $template;
	foreach my $attr ($packet->attributes) {
		$template->{'request'}->{$attr} = $packet->rawattr($attr)
	}

	# Add user details, not user ID is available here as thats what we are retrieving
	$template->{'user'}->{'Username'} = $user->{'Username'};

	# Replace template entries
	my @dbDoParams = templateReplace($config->{'userdb_find_query'},$template);

	my $sth = DBSelect(@dbDoParams);
	if (!$sth) {
		$server->log(LOG_ERR,"[MOD_USERDB_SQL] Failed to find user data: ".AWITPT::DB::DBLayer::Error());
		return MOD_RES_SKIP;
	}

	# Check if we got a result, if we did not NACK
	my $rows = $sth->rows();
	if ($rows > 1) {
		$server->log(LOG_ERR,"[MOD_USERDB_SQL] More than one result returned for user '".$user->{'Username'}."'");
		return MOD_RES_SKIP;
	} elsif ($rows < 1) {
		$server->log(LOG_DEBUG,"[MOD_USERDB_SQL] User '".$user->{'Username'}."' not found in database");
		return MOD_RES_SKIP;
	}

	# Grab record data
	my $row = hashifyLCtoMC($sth->fetchrow_hashref(), qw(ID Disabled));

	# Dont use disabled user
	my $res = isBoolean($row->{'Disabled'});
	if ($res) {
		$server->log(LOG_DEBUG,"[MOD_USERDB_SQL] User '".$user->{'Username'}."' is disabled");
		return MOD_RES_SKIP;
	}

	DBFreeRes($sth);

	return (MOD_RES_ACK,$row);
}


## @get
# Try to get a user
#
# @param server Server object
# @param user Server $user hash
# @param packet Radius packet
#
# @return User attributes hash
# @li Attributes Radius attribute hash
# @li VAttributes Radius vendor attribute hash
sub get
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

	# Add in userdb data
	foreach my $item (keys %{$user->{'_UserDB_Data'}}) {
		$template->{'userdb'}->{$item} = $user->{'_UserDB_Data'}->{$item};
	}

	# Replace template entries
	my @dbDoParams = templateReplace($config->{'userdb_get_group_attributes_query'},$template);

	# Query database
	my $sth = DBSelect(@dbDoParams);
	if (!$sth) {
		$server->log(LOG_ERR,"Failed to get group attributes: ".AWITPT::DB::DBLayer::Error());
		return RES_ERROR;
	}

	# Loop with group attributes
	while (my $row = $sth->fetchrow_hashref()) {
		addAttribute($server,$user,hashifyLCtoMC($row,qw(Name Operator Value)));
	}

	DBFreeRes($sth);



	# Replace template entries again
	@dbDoParams = templateReplace($config->{'userdb_get_user_attributes_query'},$template);
	# Query database
	$sth = DBSelect(@dbDoParams);
	if (!$sth) {
		$server->log(LOG_ERR,"Failed to get user attributes: ".AWITPT::DB::DBLayer::Error());
		return RES_ERROR;
	}

	# Loop with user attributes
	while (my $row = $sth->fetchrow_hashref()) {
		addAttribute($server,$user,hashifyLCtoMC($row,qw(Name Operator Value)));
	}

	DBFreeRes($sth);

	return RES_OK;
}


## @data_set
# Set user data
#
# @param server Server object
# @param user Server $user hash
# @param module Module that is variable pertains to
# @param name Variable name
# @param value Variable value
#
# @return RES_OK on success, RES_ERROR on error
sub data_set
{
	my ($server, $user, $module, $name, $value) = @_;


	# Build template
	my $template;

	# Add user details
	$template->{'user'}->{'ID'} = $user->{'ID'};
	$template->{'user'}->{'Username'} = $user->{'Username'};

	# Add in userdb data
	foreach my $item (keys %{$user->{'_UserDB_Data'}}) {
		$template->{'userdb'}->{$item} = $user->{'_UserDB_Data'}->{$item};
	}

	# Last updated time would be now
	$template->{'query'}->{'LastUpdated'} = $user->{'_Internal'}->{'Timestamp'};
	$template->{'query'}->{'Name'} = sprintf('%s/%s',$module,$name);
	$template->{'query'}->{'Value'} = $value;

	# Replace template entries
	my @dbDoParams = templateReplace($config->{'users_data_update_query'},$template);

	# Query database
	my $sth = DBDo(@dbDoParams);
	if (!$sth) {
		$server->log(LOG_ERR,"Failed to update users data: ".AWITPT::DB::DBLayer::Error());
		return RES_ERROR;
	}

	# If we updated *something* ...
	if ($sth eq "0E0") {
		@dbDoParams = templateReplace($config->{'users_data_set_query'},$template);

		# Insert
		$sth = DBDo(@dbDoParams);
		if (!$sth) {
			$server->log(LOG_ERR,"Failed to set users data: ".AWITPT::DB::DBLayer::Error());
			return RES_ERROR;
		}
	}

	# If we using caching, cache the result of this set
	if (defined($config->{'userdb_data_cache_time'})) {
		# Build hash to store
		my %data;
		$data{'CachedUntil'} = $user->{'_Internal'}->{'Timestamp-Unix'} + $config->{'userdb_data_cache_time'};
		$data{'LastUpdated'} = $user->{'_Internal'}->{'Timestamp'};
		$data{'Module'} = $module;
		$data{'Name'} = $name;
		$data{'Value'} = $value;

		# Cache the result
		cacheStoreComplexKeyPair('mod_userdb_sql(users_data)',
				sprintf('%s/%s/%s',$module,$user->{'_UserDB_Data'}->{'ID'},$name),
				\%data
		);
	}

	return RES_OK;
}


## @data_get
# Get user data
#
# @param server Server object
# @param user UserDB hash we got from find()
# @param module Module that is variable pertains to
# @param name Variable name
#
# @return Users data hash
# @li LastUpdated Time of last update
# @li Name Variable Name
# @li Value Variable Value
sub data_get
{
	my ($server, $user, $module, $name) = @_;


	# Build template
	my $template;

	# Add user details
	$template->{'user'}->{'ID'} = $user->{'ID'};
	$template->{'user'}->{'Username'} = $user->{'Username'};

	# Add in userdb data
	foreach my $item (keys %{$user->{'_UserDB_Data'}}) {
		$template->{'userdb'}->{$item} = $user->{'_UserDB_Data'}->{$item};
	}

	$template->{'query'}->{'Name'} = sprintf('%s/%s',$module,$name);

	# If we using caching, check how old the result is
	if (defined($config->{'userdb_data_cache_time'})) {
		my ($res,$val) = cacheGetComplexKeyPair('mod_userdb_sql(data_get)',
				sprintf('%s/%s/%s',$module,$user->{'_UserDB_Data'}->{'ID'},$name)
		);

		if (defined($val) && $val->{'CachedUntil'} > $user->{'_Internal'}->{'Timestamp-Unix'}) {
			return $val;
		}
	}

	# Replace template entries
	my @dbDoParams = templateReplace($config->{'users_data_get_query'},$template);

	# Query database
	my $sth = DBSelect(@dbDoParams);
	if (!$sth) {
		$server->log(LOG_ERR,"Failed to get users data: ".AWITPT::DB::DBLayer::Error());
		return RES_ERROR;
	}

	# Fetch user data
	my $row = hashifyLCtoMC($sth->fetchrow_hashref(), qw(LastUpdated Name Value));

	# If there is no result, just return undef
	return if (!defined($row));

	# If there is data, go through the long process of continuing ...
	my %data;
	$data{'LastUpdated'} = $row->{'LastUpdated'};
	$data{'Module'} = $module;
	$data{'Name'} = $row->{'Name'};
	$data{'Value'} = $row->{'Value'};

	# If we using caching and got here, it means that we must cache the result
	if (defined($config->{'userdb_data_cache_time'})) {
		$data{'CachedUntil'} = $user->{'_Internal'}->{'Timestamp-Unix'} + $config->{'userdb_data_cache_time'};

		# Cache the result
		cacheStoreComplexKeyPair('mod_userdb_sql(users_data)',
				sprintf('%s/%s/%s',$module,$user->{'_UserDB_Data'}->{'ID'},$name),
				\%data
		);
	}

	return \%data;
}


# Clean up of old user variables
sub cleanup
{
	my ($server,$runForDate,$resetUserData) = @_;

	$server->log(LOG_NOTICE,"[MOD_USERDB_SQL] Cleanup => Removing old user data");
	# Begin operation
	DBBegin();

	# Perform query
	my $sth = DBDo('
		DELETE FROM
			@TP@users_data
		WHERE UserID NOT IN
			(
				SELECT ID FROM users
			)
	');

	# Error and rollback
	if (!$sth) {
		$server->log(LOG_NOTICE,"[MOD_USERDB_SQL] Cleanup => Database has been rolled back, no data deleted");
		DBRollback();
		return;
	}

	if ($resetUserData) {
		$server->log(LOG_NOTICE,"[MOD_USERDB_SQL] Cleanup => Resetting user data counters");

		# Perform query
		my $sth = DBDo('
			UPDATE
				@TP@users_data
			SET
				Value = 0
			WHERE
				Name = '.DBQuote('CurrentMonthTotalTraffic').'
				OR Name = '.DBQuote('CurrentMonthTotalUptime').'
		');

		# Error and rollback
		if (!$sth) {
			$server->log(LOG_NOTICE,"[MOD_USERDB_SQL] Cleanup => Database has been rolled back, no data reset");
			DBRollback();
			return;
		}
		$server->log(LOG_NOTICE,"[MOD_USERDB_SQL] Cleanup => User data counters have been reset");
	}

	# Commit
	DBCommit();
	$server->log(LOG_NOTICE,"[MOD_USERDB_SQL] Cleanup => Old user data cleaned up");
}


1;
# vim: ts=4
