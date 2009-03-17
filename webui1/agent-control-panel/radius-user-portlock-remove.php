<?php
# Radius user, add port locking
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

# pre takes care of authentication and creates soap object we need
include("include/pre.php");
# Page header
include("include/header.php");
# Menu header
include("../shared-php/menu-header.php");
# Functions we need
require_once("../shared-php/miscfuncs.php");



function doRemove($portlockID) {
	global $soap;	

	
	# Create radius user and check for error
	$res = $soap->removeRadiusUserPortLock($portlockID);
	if ($res == 0) {
		displaySuccess("Port lock removed");
	} else {
		displayError("Error removing port lock: ".strSoapError($res));
	}
}



# Check if we have an action
if (!isset($_REQUEST['frmaction'])) {
	# FIXME : redirect

} elseif ($_REQUEST['frmaction'] == "portlocks_remove_main") {
	# Verify data
	if (isset($_REQUEST["portlock_id"])) {
		doRemove($_REQUEST['portlock_id']);
	} else {
		displayError("PortLockID not found!");
	}
}


# Menu footer
include("../shared-php/menu-footer.php");
# Footer
include("include/footer.php");

# vim: ts=4
?>
