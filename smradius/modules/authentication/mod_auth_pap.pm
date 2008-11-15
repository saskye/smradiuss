# PAP
#
# References:
#	RFC1334 - PPP Authentication Protocols
#
# Copyright (C) 2008, AllWorldIT
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
use smradius::constants;
use Digest::MD5;

use Data::Dumper; 



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
	Name => "PAP Authentication",
	Init => \&init,
	
	# Authentication
	Auth_try => \&authenticate,
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

	print(STDERR "RECEIVED\n");
	print(STDERR "User-Pass: len = ".length($encPassword).", hex = ".unpack("H*",$encPassword)."\n");
	print(STDERR "\n\n");

	my $clearPassword = $packet->password("test","User-Password");

	print(STDERR "CALC\n");
	print(STDERR "Result   : len = ".length($clearPassword).", hex = ".unpack("H*",$clearPassword).", password = $clearPassword\n");

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
