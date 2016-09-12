# Radius daemon
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



package smradius::daemon;


use strict;
use warnings;


# Check if we have Net::Server::PreFork installed
if (!eval {require Net::Server::PreFork; 1;}) {
	print STDERR "You're missing Net::Server::PreFork, try 'apt-get install libnet-server-perl'\n";
	exit 1;
}

# Check Config::IniFiles is instaslled
if (!eval {require Config::IniFiles; 1;}) {
	print STDERR "You're missing Config::IniFiles, try 'apt-get install libconfig-inifiles-perl'\n";
	exit 1;
}

# Check DateTime is installed
if (!eval {require DateTime; 1;}) {
	print STDERR "You're missing DateTime, try 'apt-get install libdatetime-perl'\n";
	exit 1;
}

# Check Cache::FastMmap is installed
if (!eval {require Cache::FastMmap; 1;}) {
	print STDERR "You're missing DateTime, try 'apt-get install libcache-fastmmap-perl'\n";
	exit 1;
} else {
	eval {use AWITPT::Cache;};
}


## no critic (BuiltinFunctions::ProhibitStringyEval)
eval qq{
	use base qw(Net::Server::PreFork);
};
## use critic

use Getopt::Long;
use Socket;
use Sys::Syslog;
use Time::HiRes qw( gettimeofday tv_interval );

use AWITPT::DB::DBILayer;
use AWITPT::Util qw( booleanize );
use Radius::Packet;

use smradius::version;
use smradius::constants;
use smradius::daemon::request;
use smradius::logging;
use smradius::config;
use smradius::util;
use smradius::attributes;





# Override configuration
sub configure {
	my ($self,$defaults) = @_;
	my $server = $self->{'server'};


	# If we hit a hash, add the config vars to the server
	if (defined($defaults)) {
		foreach my $item (keys %{$defaults}) {
			$server->{$item} = $defaults->{$item};
		}
		return;
	}

	# Set defaults
	my $cfg;
	$cfg->{'config_file'} = "/etc/smradiusd.conf";
	$cfg->{'cache_file'} = '/var/run/smradius/cache';

	$server->{'timeout'} = 120;
	$server->{'background'} = "yes";
	$server->{'pid_file'} = "/var/run/smradius/smradiusd.pid";
	$server->{'log_level'} = 2;
	$server->{'log_file'} = "/var/log/smradius/smradiusd.log";

	$server->{'host'} = "*";
	$server->{'port'} = [ 1812, 1813 ];
	$server->{'proto'} = 'udp';

	$server->{'min_servers'} = 4;
	$server->{'min_spare_servers'} = 4;
	$server->{'max_spare_servers'} = 12;
	$server->{'max_servers'} = 25;
	$server->{'max_requests'} = 1000;

	# Parse command line params
	my $cmdline;
	%{$cmdline} = ();
	GetOptions(
			\%{$cmdline},
			"help",
			"config:s",
			"debug",
			"fg",
	) or die "Error parsing commandline arguments";

	# Check for some args
	if ($cmdline->{'help'}) {
		$self->displayHelp();
		exit 0;
	}
	if (defined($cmdline->{'config'}) && $cmdline->{'config'} ne "") {
		$cfg->{'config_file'} = $cmdline->{'config'};
	}

	# Check config file exists
	if (! -f $cfg->{'config_file'}) {
		die("No configuration file '".$cfg->{'config_file'}."' found!\n");
	}

	# Use config file, ignore case
	tie my %inifile, 'Config::IniFiles', (
			-file => $cfg->{'config_file'},
			-nocase => 1
	) or die "Failed to open config file '".$cfg->{'config_file'}."': $!";
	# Copy config
	my %config = %inifile;
	#untie(%inifile);

	# Pull in params for the server
	my @server_params = (
			'log_level','log_file',
#			'port',   - We don't want to override this do we?
			'host',
			'cidr_allow', 'cidr_deny',
			'pid_file',
			'user', 'group',
			'timeout',
			'background',
			'min_servers',
			'min_spare_servers',
			'max_spare_servers',
			'max_servers',
			'max_requests'
	);
	foreach my $param (@server_params) {
		$server->{$param} = $config{'server'}{$param} if (defined($config{'server'}{$param}));
	}

	# Fix up these ...
	if (defined($server->{'cidr_allow'})) {
		my @lst = split(/,\s;/,$server->{'cidr_allow'});
		$server->{'cidr_allow'} = \@lst;
	}
	if (defined($server->{'cidr_deny'})) {
		my @lst = split(/,\s;/,$server->{'cidr_deny'});
		$server->{'cidr_deny'} = \@lst;
	}

	# Override
	if ($cmdline->{'debug'}) {
		$server->{'log_level'} = 4;
		$cfg->{'debug'} = 1;
	}

	# If we set on commandline for foreground, keep in foreground
	if ($cmdline->{'fg'} || (defined($config{'server'}{'background'}) && $config{'server'}{'background'} eq "no" )) {
		$server->{'background'} = undef;
		$server->{'log_file'} = undef;
	} else {
		$server->{'setsid'} = 1;
	}

	# Loop with logging detail
	if (defined($config{'server'}{'log_detail'})) {
		# Lets see what we have to enable
		foreach my $detail (split(/[,\s;]/,$config{'server'}{'log_detail'})) {
			$cfg->{'logging'}{$detail} = 1;
		}
	}

	#
	# System modules
	#
	if (ref($config{'system'}{'modules'}) eq "ARRAY") {
		foreach my $module (@{$config{'system'}{'modules'}}) {
			$module =~ s/\s+//g;
			# Skip comments
			next if ($module =~ /^#/);
			$module = "system/$module";
			push(@{$cfg->{'module_list'}},$module);
		}
	} else {
		my @moduleList = split(/\s+/,$config{'system'}{'modules'});
		foreach my $module (@moduleList) {
			# Skip comments
			next if ($module =~ /^#/);
			$module = "system/$module";
			push(@{$cfg->{'module_list'}},$module);
		}
	}

	#
	# Feature modules
	#
	if (ref($config{'features'}{'modules'}) eq "ARRAY") {
		foreach my $module (@{$config{'features'}{'modules'}}) {
			$module =~ s/\s+//g;
			# Skip comments
			next if ($module =~ /^#/);
			$module = "features/$module";
			push(@{$cfg->{'module_list'}},$module);
		}
	} else {
		my @moduleList = split(/\s+/,$config{'features'}{'modules'});
		foreach my $module (@moduleList) {
			# Skip comments
			next if ($module =~ /^#/);
			$module = "features/$module";
			push(@{$cfg->{'module_list'}},$module);
		}
	}

	#
	# Authentication modules
	#
	if (ref($config{'authentication'}{'mechanisms'}) eq "ARRAY") {
		foreach my $module (@{$config{'authentication'}{'mechanisms'}}) {
			$module =~ s/\s+//g;
			# Skip comments
			next if ($module =~ /^#/);
			$module = "authentication/$module";
			push(@{$cfg->{'module_list'}},$module);
		}
	} else {
		my @moduleList = split(/\s+/,$config{'authentication'}{'mechanisms'});
		foreach my $module (@moduleList) {
			# Skip comments
			next if ($module =~ /^#/);
			$module = "authentication/$module";
			push(@{$cfg->{'module_list'}},$module);
		}
	}

	if (ref($config{'authentication'}{'users'}) eq "ARRAY") {
		foreach my $module (@{$config{'authentication'}{'users'}}) {
			$module =~ s/\s+//g;
			# Skip comments
			next if ($module =~ /^#/);
			$module = "userdb/$module";
			push(@{$cfg->{'module_list'}},$module);
		}
	} else {
		my @moduleList = split(/\s+/,$config{'authentication'}{'users'});
		foreach my $module (@moduleList) {
			# Skip comments
			next if ($module =~ /^#/);
			$module = "userdb/$module";
			push(@{$cfg->{'module_list'}},$module);
		}
	}

	#
	# Accounting modules
	#
	if (ref($config{'accounting'}{'modules'}) eq "ARRAY") {
		foreach my $module (@{$config{'accounting'}{'modules'}}) {
			$module =~ s/\s+//g;
			# Skip comments
			next if ($module =~ /^#/);
			$module = "accounting/$module";
			push(@{$cfg->{'module_list'}},$module);
		}
	} else {
		my @moduleList = split(/\s+/,$config{'accounting'}{'modules'});
		foreach my $module (@moduleList) {
			# Skip comments
			next if ($module =~ /^#/);
			$module = "accounting/$module";
			push(@{$cfg->{'module_list'}},$module);
		}
	}

	#
	# Dictionary configuration
	#
	# Split off dictionaries to load
	if (ref($config{'dictionary'}->{'load'}) eq "ARRAY") {
		foreach my $dict (@{$config{'dictionary'}->{'load'}}) {
			$dict =~ s/\s+//g;
			# Skip comments
			next if ($dict =~ /^#/);
			push(@{$cfg->{'dictionary_list'}},$dict);
		}
	} else {
		my @dictList = split(/\s+/,$config{'dictionary'}->{'load'});
		foreach my $dict (@dictList) {
			# Skip comments
			next if ($dict =~ /^#/);
			push(@{$cfg->{'dictionary_list'}},$dict);
		}
	}

	# Check if the user specified a cache_file in the config
	if (defined($config{'server'}{'cache_file'})) {
		$cfg->{'cache_file'} = $config{'server'}{'cache_file'};
	}


	# Save our config and stuff
	$self->{'config'} = $cfg;
	$self->{'cmdline'} = $cmdline;
	$self->{'inifile'} = \%config;

	return;
}



# Run straight after ->run
sub post_configure_hook {
	my $self = shift;
	my $config = $self->{'config'};


	$self->log(LOG_NOTICE,"[SMRADIUS] SMRadius - v".VERSION);

	# Init config
	$self->log(LOG_INFO,"[SMRADIUS] Initializing configuration...");
	smradius::config::Init($self);
	$self->log(LOG_INFO,"[SMRADIUS] Configuration initialized.");

	# Load dictionaries
	$self->log(LOG_INFO,"[SMRADIUS] Initializing dictionaries...");
	my $dict = Radius::Dictionary->new();
	foreach my $df (@{$config->{'dictionary_list'}}) {
		# Load dictionary
		if (!$dict->readfile($df)) {
			$self->log(LOG_WARN,"[SMRADIUS] Failed to load dictionary '$df': $!");
		}
		$self->log(LOG_DEBUG,"[SMRADIUS] Loaded dictionary '$df'.");
	}
	$self->log(LOG_INFO,"[SMRADIUS] Dictionaries initialized.");
	# Store the dictionary
	$self->{'radius'}->{'dictionary'} = $dict;

	$self->log(LOG_INFO,"[SMRADIUS] Initializing modules...");
	# Load modules
	foreach my $module (@{$config->{'module_list'}}) {
		# Split off dir and mod name
		$module =~ /^(\w+)\/(\w+)$/;
		my ($mod_dir,$mod_name) = ($1,$2);

		# Load module
		## no critic (BuiltinFunctions::ProhibitStringyEval)
		my $res = eval qq{
			use smradius::modules::${mod_dir}::${mod_name};
			plugin_register(\$self,\"${mod_name}\",\$smradius::modules::${mod_dir}::${mod_name}::pluginInfo);
		};
		## use critic
		if ($@ || (defined($res) && $res != 0)) {
			$self->log(LOG_WARN,"[SMRADIUS] Error loading module $module ($@)");
		} else {
			$self->log(LOG_DEBUG,"[SMRADIUS] Plugin '$module' loaded.");
		}
	}
	$self->log(LOG_INFO,"[SMRADIUS] Plugins initialized.");

	$self->log(LOG_INFO,"[SMRADIUS] Initializing system modules.");
	# Init caching engine
	AWITPT::Cache::Init($self,{
		'cache_file' => $self->{'config'}{'cache_file'},
		'cache_file_user' => $self->{'server'}->{'user'},
		'cache_file_group' => $self->{'server'}->{'group'}
	});

	$self->log(LOG_INFO,"[SMRADIUS] System modules initialized.");

	return;
}



# Register plugin info
sub plugin_register {
	my ($self,$plugin,$info) = @_;


	# If no info, return
	if (!defined($info)) {
		print(STDERR "WARNING: Plugin info not found for plugin => $plugin\n");
		return -1;
	}

	# Set real module name & save
	$info->{'Module'} = $plugin;
	push(@{$self->{'module_list'}},$info);

	# If we should, init the module
	if (defined($info->{'Init'})) {
		$info->{'Init'}($self);
	}

	return 0;
}



# Initialize child
sub child_init_hook
{
	my $self = shift;
	my $config = $self->{'config'};


	$self->SUPER::child_init_hook();

	$self->log(LOG_INFO,"[SMRADIUS] Starting up caching engine");
	AWITPT::Cache::connect($self);

	# Do we need database support?
	if ($self->{'smradius'}->{'database'}->{'enabled'}) {
		# This is the database connection timestamp, if we connect, it resets to 0
		# if not its used to check if we must kill the child and try a reconnect
		$self->{'client'}->{'dbh_status'} = time();

		# Init core database support
		$self->{'client'}->{'dbh'} = AWITPT::DB::DBILayer::Init($self,'smradius');
		if (defined($self->{'client'}->{'dbh'})) {
			# Check if we succeeded
			if (!($self->{'client'}->{'dbh'}->connect())) {
			# If we succeeded, record OK
				$self->{'client'}->{'dbh_status'} = 0;
			} else {
				$self->log(LOG_WARN,"[SMRADIUS] Failed to connect to database: ".$self->{'client'}->{'dbh'}->Error().
						" ($$)");
			}
		} else {
			$self->log(LOG_WARN,"[SMRADIUS] Failed to Initialize: ".awitpt::db::dbilayer::internalError()." ($$)");
		}
	}

	return;
}



# Destroy the child
sub child_finish_hook {
	my $self = shift;
	my $server = $self->{'server'};

	$self->SUPER::child_finish_hook();

	$self->log(LOG_INFO,"[SMRADIUS] Shutting down caching engine ($$)");
	AWITPT::Cache::disconnect($self);

	return;
}



# Process requests we get
sub process_request {
	my $self = shift;
	my $server = $self->{'server'};
	my $client = $self->{'client'};
	my $log = defined($server->{'config'}{'logging'}{'module_list'});


	# Grab packet
	my $rawPacket = $server->{'udp_data'};

	# Check min size
	if (length($rawPacket) < 18)
	{
		$self->log(LOG_WARN, "[SMRADIUS] Packet too short - Ignoring");
		return;
	}

	# Very first timer ...
	my $timer0 = [gettimeofday];

	# Grab NOW()
	my $now = time();

	# VERIFY SOURCE SERVER
	$self->log(LOG_DEBUG,"[SMRADIUS] Packet From = > ".$server->{'peeraddr'});

	# Check if we got connected, if not ... bypass
	if ($self->{'client'}->{'dbh_status'} > 0) {
		my $action;

		$self->log(LOG_WARN,"[SMRADIUS] Client in BYPASS mode due to DB connection failure!");
		# Check bypass mode
		if (!defined($self->{'inifile'}{'database'}{'bypass_mode'})) {
			$self->log(LOG_ERR,
					"[SMRADIUS] No bypass_mode specified for failed database connections, defaulting to tempfail");
			$action = "tempfail";
		# Check for "tempfail"
		} elsif (lc($self->{'inifile'}{'database'}{'bypass_mode'}) eq "tempfail") {
		# And for "bypass"
		} elsif (lc($self->{'inifile'}{'database'}{'bypass_mode'}) eq "pass") {
		}

		# Check if we need to reconnect or not
		my $timeout = $self->{'inifile'}{'database'}{'bypass_timeout'};
		if (!defined($timeout)) {
			$self->log(LOG_ERR,"[SMRADIUS] No bypass_timeout specified for failed database connections, defaulting to 120s");
			$timeout = 120;
		}
		# Get time left
		my $timepassed = $now - $self->{'client'}->{'dbh_status'};
		# Then check...
		if ($timepassed >= $timeout) {
			$self->log(LOG_WARN,"[SMRADIUS] Client BYPASS timeout exceeded, reconnecting...");
			exit 0;
		} else {
			$self->log(LOG_WARN,"[SMRADIUS] Client still in BYPASS mode, ".( $timeout - $timepassed ).
					"s left till next reconnect");
			return;
		}
	}

	# Setup database handle
	AWITPT::DB::DBLayer::setHandle($self->{'client'}->{'dbh'});


	my $request = smradius::daemon::request->new($self);
	$request->setTimeZone($self->{'smradius'}->{'event_timezone'});

	$request->parsePacket($self->{'radius'}->{'dictionary'},$rawPacket);

	# Check if we need to override the packet timestamp, if we are not using the packet timestamp, set it to when we go the packet
	if (!booleanize($self->{'smradius'}->{'use_packet_timestamp'})) {
		$request->setTimestamp($now);
	}

	# Username should always be defined?
	if (!$request->hasUsername()) {
		$self->log(LOG_NOTICE,"[SMRADIUS] Packet with no username from ".$server->{'peeraddr'});
		return;
	}


	# TODO/FIXME: WIP
	my $pkt = $request->{'packet'};
	my $user = $request->{'user'};
	my $logReason = "UNKNOWN";



	# First thing we do is to make sure the NAS behaves if we using abuse prevention
	if ($self->{'smradius'}->{'use_abuse_prevention'} && defined($user->{'Username'})) {
		my ($res,$val) = cacheGetKeyPair('FloodCheck',$server->{'peeraddr'}."/".$user->{'Username'}."/".$pkt->code);
		if (defined($val)) {
			my $timePeriod = $now - $val;
			if ($pkt->code eq "Access-Request" && $timePeriod < $self->{'smradius'}->{'access_request_abuse_threshold'}) {
				$self->log(LOG_NOTICE,"[SMRADIUS] ABUSE: Server trying too fast. server = ".$server->{'peeraddr'}.", user = ".$user->{'Username'}.
						", code = ".$pkt->code.", timeout = ".($now - $val));
				return;
			} elsif ($pkt->code eq "Accounting-Request" && $timePeriod < $self->{'smradius'}->{'accounting_request_abuse_threshold'}) {
				$self->log(LOG_NOTICE,"[SMRADIUS] ABUSE: Server trying too fast. server = ".$server->{'peeraddr'}.", user = ".$user->{'Username'}.
						", code = ".$pkt->code.", timeout = ".($now - $val));
				return;
			}
		}
		# We give the benefit of the doubt and let a query take 60s. We update to right stamp at end of this function
		cacheStoreKeyPair('FloodCheck',$server->{'peeraddr'}."/".$user->{'Username'}."/".$pkt->code,$now + 60);
	}

	#
	# GRAB & PROCESS CONFIG
	#

	my $configured = 1;

	foreach my $module (@{$self->{'module_list'}}) {
		# Try find config attribute
		if ($module->{'Config_get'}) {

			# Get result from config module
			$self->log(LOG_DEBUG,"[SMRADIUS] CONFIG: Trying plugin '".$module->{'Name'}."' for incoming connection");
			my $res = $module->{'Config_get'}($self,$user,$pkt);

			# Check result
			if (!defined($res)) {
				$self->log(LOG_WARN,"[SMRADIUS] CONFIG: Error with plugin '".$module->{'Name'}."'");
				$logReason = "Config Error";

			# Check if we skipping this plugin
			} elsif ($res == MOD_RES_SKIP) {
				$self->log(LOG_DEBUG,"[SMRADIUS] CONFIG: Skipping '".$module->{'Name'}."'");

			# Check if we got a positive result back
			} elsif ($res == MOD_RES_ACK) {
				$self->log(LOG_DEBUG,"[SMRADIUS] CONFIG: Configuration retrieved from '".$module->{'Name'}."'");
				$logReason = "Config Retrieved";

			# Check if we got a negative result back
			} elsif ($res == MOD_RES_NACK) {
				$self->log(LOG_DEBUG,"[SMRADIUS] CONFIG: Configuration rejection when using '".$module->{'Name'}."'");
				$logReason = "Config Rejected";

				# FIXME/TODO NK WIP
				return;
#				$configured = 0;
#				last;
			}
		}
	}


	#
	# USERNAME TRANSFORM
	#

	# If we have a config attribute to transform username, use it
	if (defined($user->{'ConfigAttributes'}->{'SMRadius-Username-Transform'})) {

		$self->log(LOG_DEBUG,"[SMRADIUS] Attribute 'SMRadius-Username-Transform' exists, transforming username.");

# NK: Not ready for prime time yet
#		# Get clients(NAS) username transform pattern
#		my $transform = shift(@{$user->{'ConfigAttributes'}->{'SMRadius-Username-Transform'}});
#		if ($transform =~ /^(@\S+)=(@\S+)$/i) {
#
#			# Set old and new, prevents warnings
#			my ($old,$new) = ($1,$2);
#
#			# Use client username transform on temp username
#			my $tempUsername = $user->{'Username'};
#			$tempUsername =~ s/$old/$new/;
#
#			# Override username
#			$user->{'Username'} = $tempUsername;
#		} else {
#			$self->log(LOG_DEBUG,"[SMRADIUS] No string replacement possible on pattern '".
#					$transform."', using username '".$user->{'Username'}."'");
#		}
	}


	#
	# FIND USER
	#

	# Get the user timer
	my $timer1 = [gettimeofday];

	# FIXME - need secret
	# FIXME - need acl list

	# Common stuff for multiple codes....
	if ($pkt->code eq "Accounting-Request" || $pkt->code eq "Access-Request") {

		# Loop with modules to try find user
		foreach my $module (@{$self->{'module_list'}}) {

			# Try find user
			if ($module->{'User_find'}) {
				$self->log(LOG_DEBUG,"[SMRADIUS] FIND: Trying plugin '".$module->{'Name'}."' for username '".
						$user->{'Username'}."'");
				my ($res,$userdb_data) = $module->{'User_find'}($self,$user,$pkt);

				# Check result
				if (!defined($res)) {
					$self->log(LOG_WARN,"[SMRADIUS] FIND: Error with plugin '".$module->{'Name'}."'");
					$logReason = "Error Finding User";

				# Check if we skipping this plugin
				} elsif ($res == MOD_RES_SKIP) {
					$self->log(LOG_DEBUG,"[SMRADIUS] FIND: Skipping '".$module->{'Name'}."'");

				# Check if we got a positive result back
				} elsif ($res == MOD_RES_ACK) {
					$self->log(LOG_DEBUG,"[SMRADIUS] FIND: Username found with '".$module->{'Name'}."'");
					$user->{'_UserDB'} = $module;
					$user->{'_UserDB_Data'} = $userdb_data;
					last;

				# Or a negative result
				} elsif ($res == MOD_RES_NACK) {
					$self->log(LOG_DEBUG,"[SMRADIUS] FIND: Username not found with '".$module->{'Name'}."'");
					$logReason = "User Not Found";
					last;

				}
			}
		}
	}


	#
	# PROCESS PACKET
	#

	# Process the packet timer
	my $timer2 = [gettimeofday];


	# Is this an accounting request
	if ($pkt->code eq "Accounting-Request") {

		$self->log(LOG_DEBUG,"[SMRADIUS] Accounting Request Packet");

		#
		# GET USER
		#

		# Get user data
		if (defined($user->{'_UserDB'}) && defined($user->{'_UserDB'}->{'User_get'})) {
			my $res = $user->{'_UserDB'}->{'User_get'}($self,$user,$pkt);

			# Check result
			if ($res) {
				$self->log(LOG_WARN,"[SMRADIUS] GET: Error returned from '".$user->{'_UserDB'}->{'Name'}.
						"' for username '".$user->{'Username'}."'");
			}
		}

		# Loop with modules to try something that handles accounting
		foreach my $module (@{$self->{'module_list'}}) {
			# Try find user
			if ($module->{'Accounting_log'}) {
				$self->log(LOG_DEBUG,"[SMRADIUS] ACCT: Trying plugin '".$module->{'Name'}."'");
				my $res = $module->{'Accounting_log'}($self,$user,$pkt);

				# Check result
				if (!defined($res)) {
					$self->log(LOG_WARN,"[SMRADIUS] ACCT: Error with plugin '".$module->{'Name'}."'");
					$logReason = "Accounting Log Error";

				# Check if we skipping this plugin
				} elsif ($res == MOD_RES_SKIP) {
					$self->log(LOG_DEBUG,"[SMRADIUS] ACCT: Skipping '".$module->{'Name'}."'");

				# Check if we got a positive result back
				} elsif ($res == MOD_RES_ACK) {
					$self->log(LOG_DEBUG,"[SMRADIUS] ACCT: Accounting logged using '".$module->{'Name'}."'");
					$logReason = "Accounting Logged";

				# Check if we got a negative result back
				} elsif ($res == MOD_RES_NACK) {
					$self->log(LOG_DEBUG,"[SMRADIUS] ACCT: Accounting NOT LOGGED using '".$module->{'Name'}."'");
					$logReason = "Accounting NOT Logged";
				}
			}
		}

		# Tell the NAS we got its packet
		my $resp = Radius::Packet->new($self->{'radius'}->{'dictionary'});
		$resp->set_code('Accounting-Response');
		$resp->set_identifier($pkt->identifier);
		$resp->set_authenticator($pkt->authenticator);
		$server->{'client'}->send(
			auth_resp($resp->pack, getAttributeValue($user->{'ConfigAttributes'},"SMRadius-Config-Secret"))
		);

		# Are we going to POD the user?
		my $PODUser = 0;

		# Loop with modules that have post-accounting hooks
		foreach my $module (@{$self->{'module_list'}}) {
			# Try authenticate
			if ($module->{'Feature_Post-Accounting_hook'}) {
				$self->log(LOG_DEBUG,"[SMRADIUS] POST-ACCT: Trying plugin '".$module->{'Name'}."' for '".
						$user->{'Username'}."'");
				my $res = $module->{'Feature_Post-Accounting_hook'}($self,$user,$pkt);

				# Check result
				if (!defined($res)) {
					$self->log(LOG_WARN,"[SMRADIUS] POST-ACCT: Error with plugin '".$module->{'Name'}."'");
					$logReason = "Post Accounting Error";

				# Check if we skipping this plugin
				} elsif ($res == MOD_RES_SKIP) {
					$self->log(LOG_DEBUG,"[SMRADIUS] POST-ACCT: Skipping '".$module->{'Name'}."'");

				# Check if we got a positive result back
				} elsif ($res == MOD_RES_ACK) {
					$self->log(LOG_DEBUG,"[SMRADIUS] POST-ACCT: Passed post accounting hook by '".$module->{'Name'}."'");
					$logReason = "Post Accounting Success";

				# Or a negative result
				} elsif ($res == MOD_RES_NACK) {
					$self->log(LOG_DEBUG,"[SMRADIUS] POST-ACCT: Failed post accounting hook by '".$module->{'Name'}."'");
					$logReason = "Failed Post Accounting";
					$PODUser = 1;
				}
			}
		}

		# Build a list of our attributes in the packet
		my $acctAttributes;
		foreach my $attr ($pkt->attributes) {
			$acctAttributes->{$attr} = $pkt->rawattr($attr);
		}
		# Loop with attributes we got from the user
		foreach my $attrName (keys %{$user->{'Attributes'}}) {
			# Loop with operators
			foreach my $attrOp (keys %{$user->{'Attributes'}->{$attrName}}) {
				# Grab attribute
				my $attr = $user->{'Attributes'}->{$attrName}->{$attrOp};
				# Check attribute against accounting attributes attributes
				my $res = checkAcctAttribute($self,$user,$acctAttributes,$attr);
				# We don't care if it fails
			}
		}

		# Check if we must POD the user
		if ($PODUser) {
			$self->log(LOG_DEBUG,"[SMRADIUS] POST-ACCT: Trying to disconnect user...");

			my $resp = Radius::Packet->new($self->{'radius'}->{'dictionary'});

			$resp->set_code('Disconnect-Request');
			my $id = $$ & 0xff;
			$resp->set_identifier( $id );

			$resp->set_attr('User-Name',$pkt->attr('User-Name'));
			$resp->set_attr('Framed-IP-Address',$pkt->attr('Framed-IP-Address'));
			$resp->set_attr('NAS-IP-Address',$pkt->attr('NAS-IP-Address'));

			# Add onto logline
			$request->addLogLine(". REPLY => ");
			foreach my $attrName ($resp->attributes) {
				$request->addLogLine(
					"%s: '%s'",
					$attrName,
					$resp->rawattr($attrName)
				);
			}

			# Grab packet
			my $response = auth_resp($resp->pack, getAttributeValue($user->{'ConfigAttributes'},"SMRadius-Config-Secret"));

			# Check for POD Servers and send disconnect
			if (defined($user->{'ConfigAttributes'}->{'SMRadius-Config-PODServer'})) {
				$self->log(LOG_DEBUG,"[SMRADIUS] SMRadius-Config-PODServer is defined");

				# Check address format
				foreach my $podServerAttribute (@{$user->{'ConfigAttributes'}->{'SMRadius-Config-PODServer'}}) {
					# Check for valid IP
					if ($podServerAttribute =~ /^([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/) {
						my $podServer = $1;

						# If we have a port, use it, otherwise use default 1700
						my $podServerPort;
						if ($podServerAttribute =~ /:([0-9]+)$/) {
							$podServerPort = $1;
						} else {
							$podServerPort = 1700;
						}

						$self->log(LOG_DEBUG,"[SMRADIUS] POST-ACCT: Trying PODServer => IP: '".$podServer."' Port: '".$podServerPort."'");

						# Create socket to send packet out on
						my $podServerTimeout = "10";  # 10 second timeout
						my $podSock = IO::Socket::INET->new(
								PeerAddr => $podServer,
								PeerPort => $podServerPort,
								Type => SOCK_DGRAM,
								Proto => 'udp',
								TimeOut => $podServerTimeout,
						);

						if (!$podSock) {
							$self->log(LOG_ERR,"[SMRADIUS] POST-ACCT: Failed to create socket to send POD on");
							next;
						}

						# Check if we sent the packet...
						if (!$podSock->send($response)) {
							$self->log(LOG_ERR,"[SMRADIUS] POST-ACCT: Failed to send data on socket");
							next;
						}

						# Once sent, we need to get a response back
						my $sh = IO::Select->new($podSock);
						if (!$sh) {
							$self->log(LOG_ERR,"[SMRADIUS] POST-ACCT: Failed to select data on socket");
							next;
						}

						if (!$sh->can_read($podServerTimeout)) {
							$self->log(LOG_ERR,"[SMRADIUS] POST-ACCT: Failed to receive data on socket");
							next;
						}

						my $data;
						$podSock->recv($data, 65536);
						if (!$data) {
							$self->log(LOG_ERR,"[SMRADIUS] POST-ACCT: Receive data failed");
							$logReason = "POD Failure";
						} else {
							$logReason = "User POD";
						}

						#my @stuff = unpack('C C n a16 a*', $data);
						#$self->log(LOG_DEBUG,"STUFF: ".Dumper(\@stuff));
					} else {
						$self->log(LOG_DEBUG,"[SMRADIUS] Invalid POD Server value: '".$podServerAttribute."'");
					}
				}
			} else {
				$self->log(LOG_DEBUG,"[SMRADIUS] SMRadius-Config-PODServer is not defined");
			}
		}

	# Or maybe a access request
	} elsif ($pkt->code eq "Access-Request") {


		$self->log(LOG_DEBUG,"[SMRADIUS] Access Request Packet");

		# Authentication variables
		my $authenticated = 0;
		my $mechanism;
		# Authorization variables
		my $authorized = 1;


		# If no user is found, bork out ...
		if (!defined($user->{'_UserDB'})) {
			$self->log(LOG_DEBUG,"[SMRADIUS] FIND: No plugin found for username '".$user->{'Username'}."'");
			goto CHECK_RESULT;
		}

		#
		# GET USER
		#

		# Get user data
		if ($user->{'_UserDB'}->{'User_get'}) {
			my $res = $user->{'_UserDB'}->{'User_get'}($self,$user,$pkt);

			# Check result
			if ($res) {
				$self->log(LOG_WARN,"[SMRADIUS] GET: Error returned from '".$user->{'_UserDB'}->{'Name'}.
						"' for username '".$user->{'Username'}."'");
				goto CHECK_RESULT;
			}
		} else {
			$self->log(LOG_ERR,"[SMRADIUS] GET: No 'User_get' function available for module '".$user->{'_UserDB'}->{'Name'}."'");

			goto CHECK_RESULT;
		}

		#
		# AUTHENTICATE USER
		#

		# Loop with authentication modules
		foreach my $module (@{$self->{'module_list'}}) {
			# Try authenticate
			if ($module->{'Authentication_try'}) {
				$self->log(LOG_DEBUG,"[SMRADIUS] AUTH: Trying plugin '".$module->{'Name'}."' for '".$user->{'Username'}."'");
				my $res = $module->{'Authentication_try'}($self,$user,$pkt);

				# Check result
				if (!defined($res)) {
					$self->log(LOG_ERR,"[SMRADIUS] AUTH: Error with plugin '".$module->{'Name'}."'");

				# Check if we skipping this plugin
				} elsif ($res == MOD_RES_SKIP) {
					$self->log(LOG_DEBUG,"[SMRADIUS] AUTH: Skipping '".$module->{'Name'}."'");

				# Check if we got a positive result back
				} elsif ($res == MOD_RES_ACK) {
					$self->log(LOG_DEBUG,"[SMRADIUS] AUTH: Authenticated by '".$module->{'Name'}."'");
					$logReason = "User Authenticated";
					$mechanism = $module;
					$authenticated = 1;
					last;

				# Or a negative result
				} elsif ($res == MOD_RES_NACK) {
					$self->log(LOG_DEBUG,"[SMRADIUS] AUTH: Failed authentication by '".$module->{'Name'}."'");
					$logReason = "User NOT Authenticated";
					$mechanism = $module;
					last;

				}
			}
		}

		# Loop with modules that have post-authentication hooks
		if ($authenticated) {
			foreach my $module (@{$self->{'module_list'}}) {
				# Try authenticate
				if ($module->{'Feature_Post-Authentication_hook'}) {
					$self->log(LOG_DEBUG,"[SMRADIUS] POST-AUTH: Trying plugin '".$module->{'Name'}.
							"' for '".$user->{'Username'}."'");

					my $res = $module->{'Feature_Post-Authentication_hook'}($self,$user,$pkt);

					# Check result
					if (!defined($res)) {
						$self->log(LOG_ERR,"[SMRADIUS] POST-AUTH: Error with plugin '".$module->{'Name'}."'");

					# Check if we skipping this plugin
					} elsif ($res == MOD_RES_SKIP) {
						$self->log(LOG_DEBUG,"[SMRADIUS] POST-AUTH: Skipping '".$module->{'Name'}."'");

					# Check if we got a positive result back
					} elsif ($res == MOD_RES_ACK) {
						$self->log(LOG_DEBUG,"[SMRADIUS] POST-AUTH: Passed authenticated by '".$module->{'Name'}."'");
						$logReason = "Post Authentication Success";

					# Or a negative result
					} elsif ($res == MOD_RES_NACK) {
						$self->log(LOG_DEBUG,"[SMRADIUS] POST-AUTH: Failed authentication by '".$module->{'Name'}."'");
						$logReason = "Post Authentication Failure";
						$authenticated = 0;
						# Do we want to run the other modules ??
						last;
					}
				}
			}
		}

		#
		# AUTHORIZE USER
		#

		# Build a list of our attributes in the packet
		my $authAttributes;
		foreach my $attr ($pkt->attributes) {
			$authAttributes->{$attr} = $pkt->rawattr($attr);
		}
		# Peer address
		$authAttributes->{'SMRadius-Peer-Address'} = $server->{'peeraddr'};
		# Loop with attributes we got from the user
		foreach my $attrName (keys %{$user->{'Attributes'}}) {
			# Loop with operators
			foreach my $attrOp (keys %{$user->{'Attributes'}->{$attrName}}) {
				# Grab attribute
				my $attr = $user->{'Attributes'}->{$attrName}->{$attrOp};
				# Check attribute against authorization attributes
				my $res = checkAuthAttribute($self,$user,$authAttributes,$attr);
				if ($res == 0) {
					$authorized = 0;
					last;
				}
			}
			# We don't want to process everyting if something doesn't match
			last if (!$authorized);
		}

		# Check if we authenticated or not
		if ($authenticated && $authorized) {
			$self->log(LOG_DEBUG,"[SMRADIUS] Authenticated and authorized");
			$logReason = "User Authorized";

			my $resp = Radius::Packet->new($self->{'radius'}->{'dictionary'});
			$resp->set_code('Access-Accept');
			$resp->set_identifier($pkt->identifier);
			$resp->set_authenticator($pkt->authenticator);

			# Loop with attributes we got from the getReplyAttributes function, its a hash of arrays which are the values
			my %replyAttributes = %{ $user->{'ReplyAttributes'} };
			foreach my $attrName (keys %{$user->{'Attributes'}}) {
				# Loop with operators
				foreach my $attrOp (keys %{$user->{'Attributes'}->{$attrName}}) {
					# Grab attribute
					my $attr = $user->{'Attributes'}->{$attrName}->{$attrOp};
					# Add this to the reply attribute?
					setReplyAttribute($self,\%replyAttributes,$attr);
				}
			}
			# Loop with reply attributes
			$request->addLogLine(". RFILTER => ");
			foreach my $attrName (keys %replyAttributes) {
				# Loop with values
				foreach my $value (@{$replyAttributes{$attrName}}) {
					# Check for filter matches
					my $excluded = 0;
					foreach my $item (@{$user->{'ConfigAttributes'}->{'SMRadius-Config-Filter-Reply-Attribute'}}) {
						my @attrList = split(/[;,]/,$item);
						foreach my $aItem (@attrList) {
							$excluded = 1 if (lc($attrName) eq lc($aItem));
						}
					}
					# If we must be filtered, just exclude it then
					if (!$excluded) {
						# Add each value
						$resp->set_attr($attrName,$value);
					} else {
						$request->addLogLine("%s ",$attrName);
					}
				}
			}
			# Loop with vendor reply attributes
			$request->addLogLine(". RVFILTER => ");
			my %replyVAttributes = ();
			# Process reply vattributes already added
			foreach my $vendor (keys %{ $user->{'ReplyVAttributes'} }) {
				# Loop with operators
				foreach my $attrName (keys %{$user->{'ReplyVAttributes'}->{$vendor}}) {
					# Add each value
					foreach my $value (@{$user->{'ReplyVAttributes'}{$vendor}->{$attrName}}) {
						# Check for filter matches
						my $excluded = 0;
						foreach my $item (@{$user->{'ConfigAttributes'}->{'SMRadius-Config-Filter-Reply-VAttribute'}}) {
							my @attrList = split(/[;,]/,$item);
							foreach my $aItem (@attrList) {
								$excluded = 1 if (lc($attrName) eq lc($aItem));
							}
						}
						# If we must be filtered, just exclude it then
						if (!$excluded) {
							# This attribute is not excluded, so its ok
							$replyVAttributes{$vendor}->{$attrName} = $user->{'ReplyVAttributes'}->{$vendor}->{$attrName};
						} else {
							$request->addLogLine("%s ",$attrName);
						}
					}
				}
			}
			# Process VAttributes
			foreach my $attrName (keys %{$user->{'VAttributes'}}) {
				# Loop with operators
				foreach my $attrOp (keys %{$user->{'VAttributes'}->{$attrName}}) {
					# Check for filter matches
					my $excluded = 0;
					foreach my $item (@{$user->{'ConfigAttributes'}->{'SMRadius-Config-Filter-Reply-VAttribute'}}) {
						my @attrList = split(/[;,]/,$item);
						foreach my $aItem (@attrList) {
							$excluded = 1 if (lc($attrName) eq lc($aItem));
						}
					}
					# If we must be filtered, just exclude it then
					if (!$excluded) {
						# Grab attribute
						my $attr = $user->{'VAttributes'}->{$attrName}->{$attrOp};
						# Add this to the reply attribute?
						setReplyVAttribute($self,\%replyVAttributes,$attr);
					} else {
						$request->addLogLine("%s ",$attrName);
					}
				}
			}
			foreach my $vendor (keys %replyVAttributes) {
				# Loop with operators
				foreach my $attrName (keys %{$replyVAttributes{$vendor}}) {
					# Add each value
					foreach my $value (@{$replyVAttributes{$vendor}->{$attrName}}) {
						$resp->set_vsattr($vendor,$attrName,$value);
					}
				}
			}

			# Add attributes onto logline
			$request->addLogLine(". REPLY => ");
			foreach my $attrName ($resp->attributes) {
				$request->addLogLine(
					"%s: '%s",
					$attrName,
					$resp->rawattr($attrName)
				);
			}

			# Add vattributes onto logline
			$request->addLogLine(". VREPLY => ");
			# Loop with vendors
			foreach my $vendor ($resp->vendors()) {
				# Loop with attributes
				foreach my $attrName ($resp->vsattributes($vendor)) {
					# Grab the value
					my @attrRawVal = ( $resp->vsattr($vendor,$attrName) );
					my $attrVal = $attrRawVal[0][0];
					# Sanatize it a bit
					if ($attrVal =~ /[[:cntrl:]]/) {
						$attrVal = "-nonprint-";
					} else {
						$attrVal = "'$attrVal'";
					}

					$request->addLogLine(
						"%s/%s: %s",
						$attrName,
						$attrVal,
						$resp->rawattr($attrName)
					);
				}
			}

			$server->{'client'}->send(
				auth_resp($resp->pack, getAttributeValue($user->{'ConfigAttributes'},"SMRadius-Config-Secret"))
			);

		}

CHECK_RESULT:
		# Check if found and authenticated
		if (!$authenticated || !$authorized) {
			$self->log(LOG_DEBUG,"[SMRADIUS] Authentication or authorization failure");
			$logReason = "User NOT Authenticated or Authorized";

			my $resp = Radius::Packet->new($self->{'radius'}->{'dictionary'});
			$resp->set_code('Access-Reject');
			$resp->set_identifier($pkt->identifier);
			$resp->set_authenticator($pkt->authenticator);
			$server->{'client'}->send(
				auth_resp($resp->pack, getAttributeValue($user->{'ConfigAttributes'},"SMRadius-Config-Secret"))
			);
		}

	# We don't know how to handle this
	} else {
		$self->log(LOG_WARN,"[SMRADIUS] We cannot handle code: '".$pkt->code."'");
	}

	# END
	my $timer9 = [gettimeofday];
	my $timediff1 = tv_interval($timer0,$timer1);
	my $timediff2 = tv_interval($timer1,$timer2);
	my $timediff3 = tv_interval($timer2,$timer9);
	my $timediff = tv_interval($timer0,$timer9);

	# FIXME/TODO NK WIP
	my $logLine = join(' ',@{$request->{'logLine'}});
	my @logLineArgs = @{$request->{'logLineParams'}};


	# How should we output this ...
	if ($server->{'log_level'} > LOG_NOTICE) {
		$self->log(LOG_NOTICE,"[SMRADIUS] Result: $logReason (%.3fs + %.3fs + %.3fs = %.3fs) => $logLine",
				$timediff1,$timediff2,$timediff3,$timediff,@logLineArgs);
	} else {
		$self->log(LOG_NOTICE,"[SMRADIUS] Result: $logReason => $logLine",@logLineArgs);
	}

	# If we using abuse prevention record the time we ending off
	if ($self->{'smradius'}->{'use_abuse_prevention'} && defined($user->{'Username'})) {
		cacheStoreKeyPair('FloodCheck',$server->{'peeraddr'}."/".$user->{'Username'}."/".$pkt->code,time());
	}

	return;
}



# Initialize child
sub server_exit
{
	my $self = shift;


	$self->log(LOG_DEBUG,"Destroying system modules.");
	# Destroy cache
	AWITPT::Cache::Destroy($self);
	$self->log(LOG_DEBUG,"System modules destroyed.");

	# Parent exit
	$self->SUPER::server_exit();

	return;
}



# Slightly better logging
sub log ## no critic (Subroutines::ProhibitBuiltinHomonyms)
{
	my ($self,$level,$msg,@args) = @_;


	# Check log level and set text
	my $logtxt = "UNKNOWN";
	if ($level == LOG_DEBUG) {
		$logtxt = "DEBUG";
	} elsif ($level == LOG_INFO) {
		$logtxt = "INFO";
	} elsif ($level == LOG_NOTICE) {
		$logtxt = "NOTICE";
	} elsif ($level == LOG_WARN) {
		$logtxt = "WARNING";
	} elsif ($level == LOG_ERR) {
		$logtxt = "ERROR";
	}

	# Parse message nicely
	if ($msg =~ /^(\[[^\]]+\]) (.*)/s) {
		$msg = "$1 $logtxt: $2";
	} else {
		$msg = "[CORE] $logtxt: $msg";
	}

	# If we have args, this is more than likely a format string & args
	if (@args > 0) {
		$msg = sprintf($msg,@args);
	}

	return $self->SUPER::log($level,"[".$self->log_time." - $$] $msg");
}



# Display help
sub displayHelp {
	print(STDERR "SMRadius v".VERSION." - Copyright (c) 2007-2016, AllWorldIT\n");

	print(STDERR<<EOF);

Usage: $0 [args]
    --config=<file>        Configuration file
    --debug                Put into debug mode
    --fg                   Don't go into background

EOF

	return;
}



__PACKAGE__->run();



1;
# vim: ts=4

