# CHAP authentication
# Copyright (C) 2007-2009, AllWorldIT
#
# References:
#	RFC1944 - PPP Challenge Handshake Authentication Protocol (CHAP)
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




package smradius::modules::authentication::mod_auth_chap;

use strict;
use warnings;

# Modules we need
use smradius::constants;
use smradius::logging;
use Digest::MD5;



# Exporter stuff
require Exporter;
our (@ISA,@EXPORT,@EXPORT_OK);
@ISA = qw(Exporter);
@EXPORT = qw(
	encode_chap
);
@EXPORT_OK = qw(
);



# Plugin info
our $pluginInfo = {
	Name => "CHAP Authentication",
	Init => \&init,

	# Authentication
	Authentication_try => \&authenticate,
};



## @internal
# Initialize module
sub init
{
	my $server = shift;
}



## @authenticate
# Try authenticate user
#
# @param server Server object
# @param user User hash
# @param packet Radius packet
#
# @return Result
sub authenticate
{
	my ($server,$user,$packet) = @_;


	# Grab attributes
	my $challenge = $packet->attr('CHAP-Challenge');
	my $password = $packet->attr('CHAP-Password');

	# Check if this is a CHAP auth
	return MOD_RES_SKIP if (!defined($challenge) || !defined($password));

	$server->log(LOG_DEBUG,"[MOD_AUTH_CHAP] This is a CHAP challenge");

	# Grab our own version of the password
	my $ourPassword;
	if (defined($user->{'Attributes'}->{'User-Password'})) {
		# Operator: ==
		if (defined($user->{'Attributes'}->{'User-Password'}->{'=='})) {
			# Set password
			$ourPassword = $user->{'Attributes'}->{'User-Password'}->{'=='}->{'Value'};
		} else {
			$server->log(LOG_NOTICE,"[MOD_AUTH_CHAP] No valid operators for attribute 'User-Password', ".
					"supported operators are: ==");
		}
	} else {
		$server->log(LOG_NOTICE,"[MOD_AUTH_CHAP] No 'User-Password' attribute, cannot authenticate");
		return MOD_RES_NACK;
	}

#	print(STDERR "RECEIVED\n");
#	print(STDERR "Challenge: len = ".length($challenge).", hex = ".unpack("H*",$challenge)."\n");
#	print(STDERR "Password : len = ".length($password).", hex = ".unpack("H*",$password)."\n");
#	print(STDERR "\n\n");

	# Pull off the ID
	my $id = substr($password,0,1);
#	print(STDERR "ID: ".length($id).", hex = ".unpack("H*",$id)."\n");

	# Calculate the result
	my $result = encode_chap($id,$challenge,$ourPassword);
	
#	print(STDERR "CALC\n");
#	print(STDERR "Result   : len = ".length($result).", hex = ".unpack("H*",$result)."\n");
#	print(STDERR "\n\n");

	# Check if the password and the result match
	if ($password eq $result) {
		return MOD_RES_ACK;
	}
	
	return MOD_RES_NACK;
}



# Encode CHAP password from ID, Challenge and Password
sub encode_chap
{
	my ($id,$challenge,$password) = @_;


	# Build MD5
	my $md5 = Digest::MD5->new();
	$md5->add($id);
	$md5->add($password);
	$md5->add($challenge);

	# Create encoded
	my $encoded = $id . $md5->digest();

	return $encoded;
}



1;
# vim: ts=4
