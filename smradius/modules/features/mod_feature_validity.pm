# Validity support
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

package smradius::modules::features::mod_feature_validity;

use strict;
use warnings;

# Modules we need
use smradius::constants;
use smradius::logging;
use smradius::util;
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
	Name => "User Validity Feature",
	Init => \&init,
	
	# Authentication hook
	'Feature_Post-Authentication_hook' => \&checkValidity,
	'Feature_Post-Accounting_hook' => \&checkValidity
};


# Some constants
my $VALID_FROM_KEY = 'SMRadius-Validity-ValidFrom';
my $VALID_TO_KEY = 'SMRadius-Validity-ValidTo';
my $VALID_WINDOW_KEY = 'SMRadius-Validity-ValidWindow';


## @internal
# Initialize module
sub init
{
	my $server = shift;
}


## @checkValidity($server,$user,$packet)
# Check Validity based on date
#
# @param server Server object
# @param user User data
# @param packet Radius packet
#
# @return Result
sub checkValidity
{
	my ($server,$user,$packet) = @_;

	$server->log(LOG_DEBUG,"[MOD_FEATURE_VALIDITY] POST AUTH HOOK");
	
	my ($validFrom,$validTo,$validWindow);


	# Get validity start date 
	if (defined($user->{'Attributes'}->{$VALID_FROM_KEY})) {
		$server->log(LOG_DEBUG,"[MOD_FEATURE_VALIDITY] '".$VALID_FROM_KEY."' is defined");
		# Operator: :=
		if (defined($user->{'Attributes'}->{$VALID_FROM_KEY}->{':='})) {
			# Is it formatted as a date?
			if ($user->{'Attributes'}->{$VALID_FROM_KEY}->{':='}->{'Value'} =~ /^[0-9]{4}-[0-9]{2}-[0-9]{2}$/) {
				$validFrom = $user->{'Attributes'}->{$VALID_FROM_KEY}->{':='}->{'Value'};
			} else {
				$server->log(LOG_NOTICE,"[MOD_FEATURE_VALIDITY] '".$user->{'Attributes'}->{$VALID_FROM_KEY}->{':='}->{'Value'}.
						"' is NOT in ISO standard format 'YYYY-MM-DD'");
			}
		} else {
			$server->log(LOG_NOTICE,"[MOD_FEATURE_VALIDITY] No valid operators for attribute '$VALID_FROM_KEY'");
		} # if (defined($user->{'Attributes'}->{$VALID_FROM_KEY}->{':='})) {
	} # if (defined($user->{'Attributes'}->{$VALID_FROM_KEY})) {


	# Get validity end date 
	if (defined($user->{'Attributes'}->{$VALID_TO_KEY})) {
		$server->log(LOG_DEBUG,"[MOD_FEATURE_VALIDITY] '".$VALID_TO_KEY."' is defined");
		# Operator: :=
		if (defined($user->{'Attributes'}->{$VALID_TO_KEY}->{':='})) {
			# Is it formatted as a date?
			if ($user->{'Attributes'}->{$VALID_TO_KEY}->{':='}->{'Value'} =~ /^[0-9]{4}-[0-9]{2}-[0-9]{2}$/) {
				$validTo = $user->{'Attributes'}->{$VALID_TO_KEY}->{':='}->{'Value'};
			} else {
				$server->log(LOG_NOTICE,"[MOD_FEATURE_VALIDITY] '".$user->{'Attributes'}->{$VALID_TO_KEY}->{':='}->{'Value'}.
						"' is NOT an ISO standard format 'YYYY-MM-DD'");
			}
		} else {
			$server->log(LOG_NOTICE,"[MOD_FEATURE_VALIDITY] No valid operators for attribute '$VALID_TO_KEY'");
		} # if (defined($user->{'Attributes'}->{$VALID_TO_KEY}->{':='})) {
	} # if (defined($user->{'Attributes'}->{$VALID_TO_KEY})) {

	# Get validity window 
	if (defined($user->{'Attributes'}->{$VALID_WINDOW_KEY})) {
		$server->log(LOG_DEBUG,"[MOD_FEATURE_VALIDITY] '".$VALID_WINDOW_KEY."' is defined");
		# Operator: :=
		if (defined($user->{'Attributes'}->{$VALID_WINDOW_KEY}->{':='})) {
			# Is it a number?
			if ($user->{'Attributes'}->{$VALID_WINDOW_KEY}->{':='}->{'Value'} =~ /^\d+$/) {
				$validWindow = $user->{'Attributes'}->{$VALID_WINDOW_KEY}->{':='}->{'Value'};
			} else {
				$server->log(LOG_NOTICE,"[MOD_FEATURE_VALIDITY] '".$user->{'Attributes'}->{$VALID_WINDOW_KEY}->{':='}->{'Value'}.
						"' is NOT an integer");
			}
		} else {
			$server->log(LOG_NOTICE,"[MOD_FEATURE_VALIDITY] No valid operators for attribute '$VALID_WINDOW_KEY'");
		} # if (defined($user->{'Attributes'}->{$VALID_WINDOW_KEY}->{':='})) {
	} # if (defined($user->{'Attributes'}->{$VALID_WINDOW_KEY})) {



	# Now ...
	my $now = $user->{'_Internal'}->{'Timestamp-Unix'};


	# Do we have a begin date?
	if (defined($validFrom)) {

		# Convert string to datetime
		my $validFrom_unixtime = str2time($validFrom);
		if (!defined($validFrom_unixtime)) {
			$server->log(LOG_NOTICE,"[MOD_FEATURE_VALIDITY] Date conversion failed on '".$validFrom."'");

		# If current time before start of valid pariod
		} elsif ($now < $validFrom_unixtime) {
			my $pretty_dt = DateTime->from_epoch( epoch => $validFrom_unixtime )->strftime('%Y-%m-%d %H:%M:%S');

			$server->log(LOG_DEBUG,"[MOD_FEATURE_VALIDITY] Current date outside valid start date: '".$pretty_dt."', rejecting");
			# Date not within valid period, must be disconnected

			return MOD_RES_NACK;
		} # if (!defined($validFrom_unixtime)) {
	} # if (defined($validFrom)) {

	# Do we have an end date?
	if (defined($validTo)) {

		# Convert string to datetime
		my $validTo_unixtime = str2time($validTo);
		if (!defined($validTo_unixtime)) {
				$server->log(LOG_DEBUG,"[MOD_FEATURE_VALIDITY] Date conversion failed on '".$validTo."'");

		# If current time after start of valid pariod
		} elsif ($now > $validTo_unixtime) {
			my $pretty_dt = DateTime->from_epoch( epoch => $validTo_unixtime )->strftime('%Y-%m-%d %H:%M:%S');
			$server->log(LOG_DEBUG,"[MOD_FEATURE_VALIDITY] Current date outside valid end date: '".$pretty_dt."', rejecting");
			# Date not within valid period, must be disconnected

			return MOD_RES_NACK;
		} # if (!defined($validTo_unixtime)) {
	} # if (defined($validTo)) {

	# Do we have a validity window
	if (defined($validWindow)) {

		# Check first if we have the ability to support this feature
		if (defined($user->{'_UserDB'}->{'Users_data_get'})) {
			# Fetch users_data for first login
			if (defined(my $res = $user->{'_UserDB'}->{'Users_data_get'}($server,$user,'global','FirstLogin'))) {
				# Check if this user should be disconnected
				if (defined($validWindow) && defined($res)) {
					my $validUntil = $validWindow + $res->{'Value'};
					# If current time after start of valid pariod
					if ($now > $validUntil) {
						my $pretty_dt = DateTime->from_epoch( epoch => $validUntil )->strftime('%Y-%m-%d %H:%M:%S');
						$server->log(LOG_DEBUG,"[MOD_FEATURE_VALIDITY] Current date outside valid window end date: '".$pretty_dt."', rejecting");
						# Date not within valid window, must be disconnected
						return MOD_RES_NACK;
					}
				}
	
			} else {
				$server->log(LOG_NOTICE,"[MOD_FEATURE_VALIDITY] No users_data 'global/FirstLogin' found for user '".$packet->attr('User-Name')."'");
			} # if (defined(my $res = $module->{'Users_data_get'}($server,$user,'global','FirstLogin'))) {
		} else {
			$server->log(LOG_WARN,"[MOD_FEATURE_VALIDITY] UserDB module '".$user->{'_UserDB'}->{'Name'}.
					"' does not support 'users_data'. Therefore no support for Validity Window feature");
		} # if (defined($user->{'_UserDB'}->{'Users_data_get'})) {
	}

	return MOD_RES_ACK;
}


1;
# vim: ts=4
