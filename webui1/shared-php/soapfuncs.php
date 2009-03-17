<?php
# Misc funcs we use for SOAP stuff
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


# Function to return a stringified soap error
function strSoapError($errCode) {

	if (is_numeric($errCode)) {

		if ($errCode == -1) {
			return "Backend returned unknown error!";

		} elseif ($errCode == -2) {
			return "Backend database error!";

		} elseif ($errCode == -3) {
			return "Record already exists!";

		} elseif ($errCode == -4) {
			return "Parameter(s) invalid!";

		} elseif ($errCode == -5) {
			return "Not allowed!";

		} elseif ($errCode == -6) {
			return "Backend system error!";

		} elseif ($errCode == -101) {
			return "Incorrect use of SOAP function!";

		} elseif ($errCode == -102) {
			return "Not authorized to access specified record!";

		} elseif ($errCode == -103) {
			return "Not authorized to access SOAP function!";

		} elseif ($errCode == -104) {
			return "SOAP API error!";

		} else {
			return "Unknown error code $errCode, please contact your ISP.";
		}

	} else {
		return "Unknown return value!";
	}

}


?> 
