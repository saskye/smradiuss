# Radius client
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



package smradius::client;


use strict;
use warnings;

use base qw(AWITPT::Object);

use Getopt::Long qw( GetOptionsFromArray );
use IO::Select;
use IO::Socket;

use smradius::version;
use smradius::Radius::Packet;

# Check Config::IniFiles is instaslled
if (!eval {require Config::IniFiles; 1;}) {
	print STDERR "You're missing Config::IniFiles, try 'apt-get install libconfig-inifiles-perl'\n";
	exit 1;
}



# Run the client
sub run
{
	my ($self,@methodArgs) = @_;
	# Instantiate if we're not already instantiated
	$self = $self->new() if (!ref($self));


	print(STDERR "SMRadClient v".VERSION." - Copyright (c) 2007-2016, AllWorldIT\n");

	print(STDERR "\n");

	# Set defaults
	my $cfg;
	$cfg->{'config_file'} = "/etc/smradiusd.conf";

	# Grab runtime arguments
	my @runArgs = @methodArgs ? @methodArgs : @ARGV;

	# Parse command line params
	my $cmdline;
	%{$cmdline} = ();
	if (!GetOptionsFromArray(
		\@runArgs,
		\%{$cmdline},
		"config:s",
		"raddb:s",
		"help",
	)) {
	   print(STDERR "ERROR: Error parsing commandline arguments");
	   return 1;
	}

	# Check for some args
	if ($cmdline->{'help'}) {
		displayHelp();
		return 0;
	}

	# Make sure we only have 2 additional args
	if (@runArgs < 3) {
		print(STDERR "ERROR: Invalid number of arguments\n");
		displayHelp();
		return 1;
	}

	if (!defined($cmdline->{'raddb'}) || $cmdline->{'raddb'} eq "") {
		print(STDERR "ERROR: No raddb directory specified!\n");
		displayHelp();
		return 1;
	}

	# Get variables we need
	my $server = shift(@runArgs);
	my $type = shift(@runArgs);
	$self->{'secret'} = shift(@runArgs);

	# Validate type
	if (!defined($type) || ( $type ne "acct" && $type ne "auth" && $type ne "disconnect")) {
		print(STDERR "ERROR: Invalid packet type specified!\n");
		displayHelp();
		return 1;
	}


	print(STDERR "\n");


	# Time to start loading the dictionary
	print(STDERR "Loading dictionaries...");
	my $raddb = smradius::Radius::Dictionary->new();

	# Look for files in the dir
	my $DIR;
	if (!opendir($DIR, $cmdline->{'raddb'})) {
		print(STDERR "ERROR: Cannot open '".$cmdline->{'raddb'}."': $!");
		return 1;
	}
	my @raddb_files = readdir($DIR);

	# And load the dictionary
	foreach my $df (@raddb_files) {
		my $df_fn = $cmdline->{'raddb'}."/$df";
		# Load dictionary
		if (!$raddb->readfile($df_fn)) {
			print(STDERR "Failed to load dictionary '$df_fn': $!");
		}
		print(STDERR ".");
	}
	print(STDERR "\n");

	# Decide what type of packet this is
	my $port;
	my $pkt_code;
	if ($type eq "acct") {
		$port = 1813;
		$pkt_code = "Accounting-Request";
	} elsif ($type eq "auth") {
		$port = 1812;
		$pkt_code = "Access-Request";
	} elsif ($type eq "disconnect") {
		$port = 1813;
		$pkt_code = "Disconnect-Request";
	}


	print(STDERR "\nRequest:\n");
	printf(STDERR " > Secret => '%s'\n",$self->{'secret'});
	# Build packet
	$self->{'pkt'} = smradius::Radius::Packet->new($raddb);
	$self->{'pkt'}->set_code($pkt_code);
	# Generate identifier
	my $ident = int(rand(32768));
	$self->{'pkt'}->set_identifier($ident);
	print(STDERR " > Identifier: $ident\n");
	# Generate authenticator number
	my $authen = int(rand(32768));
	$self->{'pkt'}->set_authenticator($authen);
	print(STDERR " > Authenticator: $ident\n");

	# Pull in attributes from STDIN
	while (my $line = <STDIN>) {
		$self->addAttributesFromString($line);
	}

	# Pull in attributes from commandline
	while (my $line = shift(@runArgs)) {
		$self->addAttributesFromString($line);
	}

	# Create UDP packet
	my $udp_packet = $self->{'pkt'}->pack();

	# Create socket to send packet out on
	my $sockTimeout = "10";  # 10 second timeout
	my $sock = IO::Socket::INET->new(
		PeerAddr => $server,
		PeerPort => $port,
		Type => SOCK_DGRAM,
		Proto => 'udp',
		TimeOut => $sockTimeout,
	);

	if (!$sock) {
		print(STDERR "ERROR: Failed to create socket\n");
		return 1;
	}

	# Check if we sent the packet...
	if (!$sock->send($udp_packet)) {
		print(STDERR "ERROR: Failed to send data on socket\n");
		return 1;
	}


	# And time for the response
	print(STDERR "\nResponse:\n");

	# Once sent, we need to get a response back
	my $rsock = IO::Select->new($sock);
	if (!$rsock) {
		print(STDERR "ERROR: Failed to select response data on socket\n");
		return 1;
	}

	# Check if we can read a response after the select()
	if (!$rsock->can_read($sockTimeout)) {
		print(STDERR "ERROR: Failed to receive response data on socket\n");
		return 1;
	}

	# Read packet
	$sock->recv($udp_packet, 65536);
	if (!$udp_packet) {
		print(STDERR "ERROR: Receive response data failed: $!\n");
		return 1;
	}

	# Parse packet
	my $pkt = smradius::Radius::Packet->new($raddb,$udp_packet);
	print(STDERR " > Authenticated: ". (defined(auth_req_verify($udp_packet,$self->{'secret'},$authen)) ? "yes" : "no") ."\n");
	print(STDERR $pkt->str_dump());


	if (@methodArgs) {
		warn "CALLED FROM FUNCTION";
	}

	return 0;
}




# Allow adding attribute from a string
sub addAttributesFromString
{
	my ($self,$line) = @_;


	# Remove EOL
	chomp($line);
	# Split on , and newline
	my @rawAttributes = split(/[,\n]+/,$line);
	foreach my $attr (@rawAttributes) {
		# Pull off attribute name & value
		my ($name,$value) = ($attr =~ /\s*(\S+)\s*=\s?(.+)/);
		$self->addAttribute($name,$value);
	}

	return;
}



# Add attribute to packet
sub addAttribute
{
	my ($self,$name,$value) = @_;


	# Add to packet
	print(STDERR " > Adding '$name' => '$value'\n");
	if ($name eq "User-Password") {
		$self->{'pkt'}->set_password($value,$self->{'secret'});
	} else {
		$self->{'pkt'}->set_attr($name,$value);
	}

	return;
}



# Display help
sub displayHelp {
	print(STDERR<<EOF);

Usage: $0 [args] <server> <acct|auth|disconnect> <secret> [ATTR=VALUE,...]
    --raddb=<DIR>          Directory where the radius dictionary files are

EOF

	return;
}


1;
# vim: ts=4
