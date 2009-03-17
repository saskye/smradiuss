<?php
# Radius term code mappings
#
# Copyright (c) 2005-2008, AllWorldIT
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

		switch ($errCode) {
			case 0:
				return "Still logged in";
			case 45: # Unknown
			case 46: # Unknown
			case 63: # Unknown
			case 1:
				return "User request";
			case 2:
			case 816: # TCP connection reset? unknown
				return "Carrier loss";
			case 5:
				return "Session timeout";
			case 6: # Admin reset
			case 10: # NAS request
			case 11: # NAS reboot
			case 831: # NAS request? unknown
			case 841: # NAS request? unknown
				return "Router reset/reboot";
			case 8: # Port error
				return "Port error";
			case 180: # Unknown
				return "Local hangup";
			case 827: # Unknown
				return "Service unavailable";
			default:
				return "Unkown";
		}

	} else {
		return "Unknown";
	}

}


?> 
