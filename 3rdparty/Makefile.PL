# Makefile
# Copyright (C) 2014-2016, AllWorldIT
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



use strict;
use warnings;



use ExtUtils::MakeMaker;
use File::Find;



my @additionalDirs = ();

find(
	{
		wanted => sub {
			if (/^Makefile.PL$/) {
				my $dirname = $File::Find::dir;
				if ($dirname ne ".") {
					$dirname =~ s,\./,,;
					push(@additionalDirs,$dirname);
				}
			};
		},
		bydepth => 1,
	},
	".",
);

WriteMakefile(
	'NAME' => 'SMRadius-3rdParty',

	'DIR' => \@additionalDirs,
);



# vim: ts=4
