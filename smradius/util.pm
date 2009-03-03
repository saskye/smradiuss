# SMRadius Utility Functions
# Copyright (C) 2008-2009, AllWorldIT
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


	# Loop with hash sections
	foreach my $section (keys %{$hashref}) {
		# Loop with section items
		foreach my $item (keys %{$hashref->{$section}}) {
			# Grab value
			my $itemVal = $hashref->{$section}->{$item};
			# Replace in string
			$string =~ s/\%{$section\.$item(=[^}]+)?}/$itemVal/gi;
		}
	}

	# Replace blanks
	$string =~ s/\%{[a-z]+\.[a-z0-9\-]+(?:=([^}]+))?}/$1/gi;

	return $string;
}



1;
# vim: ts=4
