# SMRadius Utility Functions
# Copyright (C) 2007-2015, AllWorldIT
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


## @class smradius::util
# Utility functions
package smradius::util;

use strict;
use warnings;

# Exporter stuff
require Exporter;
our (@ISA,@EXPORT);
@ISA = qw(Exporter);
@EXPORT = qw(
	niceUndef
	templateReplace
	isBoolean
);



## @fn niceUndef($string)
# If string defined return 'string', or if undefined return -undef-
#
# @param string String to check
#
# @return Return 'string' if defined, or -undef- otherwise
sub niceUndef
{
	my $string = shift;


	return defined($string) ? "'$string'" : '-undef-';
}


## @fn templateReplace($string,$hashref)
# Template string replacer function
#
# @param string String to replace template items in
# @param hashref Hashref containing the hash of tempalte items & values
#
# @return String with replaced items
sub templateReplace
{
	my ($string,$hashref) = @_;


	my @valueArray = ();

	# Replace blanks
	while (my ($entireMacro,$section,$item,$default) = ($string =~ /(\%{([a-z]+)\.([a-z0-9\-]+)(?:=([^}]*))?})/i )) {
		# Replace macro with ?	
		$string =~ s/$entireMacro/\?/;

		# Get value to substitute
		my $value = defined($hashref->{$section}->{$item}) ? $hashref->{$section}->{$item} : $default;

		# Add value onto our array
		push(@valueArray,$value);
		
	}

	return ($string, @valueArray);
}


## @fn isBoolean($var)
# Check if a variable is boolean
#
# @param var Variable to check
#
# @return 1, 0 or undef
sub isBoolean
{
	my $var = shift;


	# Check if we're defined
	if (!defined($var)) {
		return undef;
	}

	# Nuke whitespaces
	$var =~ s/\s//g;

	# Allow true, on, set, enabled, 1, false, off, unset, disabled, 0
	if ($var =~ /^(?:true|on|set|enabled|1)$/i) {
		return 1;
	}
	if ($var =~ /^(?:false|off|unset|disabled|0)$/i) {
		return 0;
	}

	# Invalid or unknown
	return undef;
}



1;
# vim: ts=4
