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



function displayForm($userID)
{
	global $soap;
?>
	<form action="radius-user-portlock-add.php" method="post">
		<div>
			<input type="hidden" name="user_id" value="<?php echo $userID; ?>" />
			<input type="hidden" name="frmaction" value="portlocks_add_final" />
		</div>
		<table class="entrytable">
			<tr>
				<td class="title">Port</td>
				<td class="entry"><input type="text" name="portlock_port" /></td>
			</tr>
		</table>
		<div class="text-center">
			<input type="submit" value="Add" />
		</div>
	</form>
	<p />

Note On Searching:
<ul>
	<li>Dates are in the format of YYYY-MM-DD</li>
	<li>ValidTo is ALWAYS the first day of the next month</li>
</ul>
<?php
}


function doAdd($userID)
{
	global $soap;	

	
	$lockInfo = NULL;
	$lockInfo["UserID"] = $userID;

	# Verify data
	if (isset($_REQUEST["portlock_port"])) {
		$lockInfo["Port"] = $_REQUEST["portlock_port"];
	} else {
		displayError("Port must be specified!");
		return;
	}

	# Create radius user and check for error
	$res = $soap->createRadiusUserPortLock($lockInfo);
	if ($res > 0) {
		displaySuccess("Added port lock to user");
	} else {
		displayError("Error creating port lock: ".strSoapError($res));
	}
}


# Check if we have an action
if (!isset($_REQUEST['frmaction'])) {
	# FIXME : redirect

} elseif ($_REQUEST['frmaction'] == "portlocks_add_main") {
	displayForm($_REQUEST['user_id']);

} elseif ($_REQUEST['frmaction'] == "portlocks_add_final") {
	doAdd($_REQUEST['user_id']);
}


# Menu footer
include("../shared-php/menu-footer.php");
# Footer
include("include/footer.php");

# vim: ts=4
?>
