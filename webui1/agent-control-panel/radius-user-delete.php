<?php
# Radius user delete
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




# Remove screen
function displayDelete($userID) {
	global $soap;


	$userInfo = $soap->getRadiusUser($userID);
	if (!is_object($userInfo)) {
		displayError("getRadiusUser: ".strSoapError($userInfo));
		return;
	}
		
?>
	<div class="sectiontitle">Radius User Search Results</div>
	<p />

	<div class="text-center">
		<form action="radius-user-delete.php" method="post">
			<div>
				<input type="hidden" name="frmaction" value="delete_final" />
				<input type="hidden" name="user_id" value="<?php echo $userID ?>" />
				Are you very sure you wish to remove radius user <?php echo $userInfo->Username; ?>?<p />
				<input type="submit" name="delete_confirm" value="Yes" />
				<input type="submit" name="delete_confirm" value="No" />
			</div>
		</form>
	</div>
<?php
}



# Actual form action to remove a mailbox
function doDelete($userID) {
	global $soap;


	$res = $soap->removeRadiusUser($userID);
	if ($res == 0) {
		displaySuccess("Removed radius user");
	} else {
		displayError("Error removing radius user: ".strSoapError($res));
	}
}




# Check if we have an action
if (!isset($_REQUEST['frmaction'])) {
	# FIXME : redirect

} elseif ($_REQUEST['frmaction'] == "delete_main") {
	# Else no confirmation, display confirm dialog
	displayDelete($_REQUEST['user_id']);

} elseif ($_REQUEST['frmaction'] == "delete_final") {
	# If confirmed delete
	if ($_REQUEST['delete_confirm'] == "Yes") {
		doDelete($_REQUEST['user_id']);
	} else {
		# FIXME - redirect to main
	}
}


# Menu footer
include("../shared-php/menu-footer.php");
# Footer
include("include/footer.php");

# vim: ts=4
?>
