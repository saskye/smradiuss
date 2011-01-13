<?php
# Utility functions
# Copyright (C) 2010-2011, AllWorldIT
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


## @fn isBoolean($param)
# Check if a variable is boolean
#
# @param var Variable to check
#
# @return 1, 0 or -1 on unknown
function isBoolean($param) {

	# Check if we're set
	if (!isset($param)) {
		return -1;
	}

	# If it's already bool, just return it
	if (is_bool($param)) {
		return $param;
	}

	# If it's a string..
	if (is_string($param)) {

		# Nuke whitespaces
		trim($param);

		# Allow true, on, set, enabled, 1, false, off, unset, disabled, 0
		if (preg_match('/^(?:true|on|set|enabled|1)$/i', $param)) {
			return 1;
		}
		if (preg_match('/^(?:false|off|unset|disabled|0)$/i', $param)) {
			return 0;
		}
	}

	# Invalid or unknown
	return -1;
}


# vim: ts=4

?>
