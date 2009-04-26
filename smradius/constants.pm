# SMRadius Constants
# Copyright (C) 2007-2009, AllWorldIT
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



## @class smradius::constants
# SMRadius constants package
package smradius::constants;

use strict;

# Exporter stuff
require Exporter;
our (@ISA,@EXPORT,@EXPORT_OK);
@ISA = qw(Exporter);
@EXPORT = qw(
	RES_OK
	RES_ERROR
	
	MOD_RES_ACK
	MOD_RES_NACK
	MOD_RES_SKIP
);
@EXPORT_OK = ();


use constant {
	RES_OK			=> 0,
	RES_ERROR		=> -1,

	MOD_RES_SKIP		=> 0,
	MOD_RES_ACK		=> 1,
	MOD_RES_NACK		=> 2,
};



1;
# vim: ts=4
