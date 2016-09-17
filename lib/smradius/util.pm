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
	templateReplace
);



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
		my $value = (defined($hashref->{$section}) && defined($hashref->{$section}->{$item})) ?
				$hashref->{$section}->{$item} : $default;

		# Add value onto our array
		push(@valueArray,$value);
	}

	return ($string, @valueArray);
}


1;
# vim: ts=4
