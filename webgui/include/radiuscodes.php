<?php
# Radius term code mappings
# Copyright (C) 2007-2011, AllWorldIT
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




# Return string for radius term code
function strRadiusTermCode($errCode) {

	if (is_numeric($errCode)) {
		# Terminate codes RFC 2866
		switch ($errCode) {
			case 1:
				return "User Request";
			case 2:
				return "Lost Carrier";
			case 3:
				return "Lost Service";
			case 4:
				return "Idle Timeout";
			case 5:
				return "Session Timeout";
			case 6:
				return "Admin Reset";
			case 7:
				return "Admin Reboot";
			case 8:
				return "Port Error";
			case 9:
				return "NAS Error";
			case 10:
				return "NAS Request";
			case 11:
				return "NAS Reboot";
			case 12:
				return "Port Unneeded";
			case 13:
				return "Port Preempted";
			case 14:
				return "Port Suspended";
			case 15:
				return "Service Unavailable";
			case 16:
				return "Callback";
			case 17:
				return "User Error";
			case 18:
				return "Host Request";
			default:
				return "Unkown";
		}
	} else {
		switch ($errCode) {
			case NULL:
				return "Still logged in";
			default:
				return "Unkown";
		}
	}

}


# vim: ts=4
?> 
