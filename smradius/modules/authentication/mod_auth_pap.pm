# PAP
# Copyright (C) 2007-2009, AllWorldIT
#
# References:
#	RFC1334 - PPP Authentication Protocols
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

package mod_auth_pap;

use strict;
use warnings;

# Modules we need
use smradius::attributes;
use smradius::constants;
use smradius::logging;
use Digest::MD5;



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
	Name => "PAP Authentication",
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


	# Pull in attributes
	my $encPassword = $packet->attr('User-Password');

	# Check if this is PAP authentication
	return MOD_RES_SKIP if (!defined($encPassword));

#	print(STDERR "RECEIVED\n");
#	print(STDERR "User-Pass: len = ".length($encPassword).", hex = ".unpack("H*",$encPassword)."\n");
#	print(STDERR "\n\n");

	# Decode the password using the secret
	my $clearPassword = $packet->password(getAttributeValue($user->{'ConfigAttributes'},"SMRadius-Config-Secret"),
			"User-Password");

#	print(STDERR "CALC\n");
#	print(STDERR "Result   : len = ".length($clearPassword).", hex = ".unpack("H*",$clearPassword).", password = $clearPassword\n");


	# Compare passwords
	if (defined($user->{'Attributes'}->{'User-Password'})) {
		# Operator: ==
		if (defined($user->{'Attributes'}->{'User-Password'}->{'=='})) {
			# Compare
			if ($user->{'Attributes'}->{'User-Password'}->{'=='}->{'Value'} eq $clearPassword) {
				return MOD_RES_ACK;
			} 
		} else {
			$server->log(LOG_NOTICE,"[MOD_AUTH_PAP] No valid operators for attribute 'User-Password', ".
					"supported operators are: ==");
		}
	} else {
		$server->log(LOG_NOTICE,"[MOD_AUTH_PAP] No 'User-Password' attribute, cannot authenticate");
	}

	return MOD_RES_NACK;
}


1;
# vim: ts=4
