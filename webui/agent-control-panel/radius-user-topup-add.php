<?php
# Radius user, add topups
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




# Add topup
function displayForm($userID) {
	global $soap;

?>
	<div class="sectiontitle">Add Topup</div>
	<p />

	<form action="radius-user-topup-add.php" method="post">
		<div>
			<input type="hidden" name="user_id" value="<?php echo $userID; ?>" />
			<input type="hidden" name="frmaction" value="topups_add_final" />
		</div>
		<table class="entrytable">
			<tr>
				<td class="title">Bandwidth (Mbyte)</td>
				<td class="entry"><input type="text" name="bandwidth" /></td>
			</tr>
			<tr>
				<td class="title">Valid From</td>
				<td class="entry"><input type="text" name="validFrom" /></td>
			</tr>
			<tr>
				<td class="title">Valid To</td>
				<td class="entry"><input type="text" name="validTo" /></td>
			</tr>
			<tr>
				<td class="title">AgentRef</td>
				<td class="entry"><input type="text" name="agentRef" /></td>
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

	$info = NULL;

	# Verify data
	if ($_REQUEST["user_id"] != "") {
		$info["UserID"] = $_REQUEST["user_id"];
	} else {
		displayError("UserID must be specified");
		return;
	}

	if ($_REQUEST["bandwidth"] != "") {
		$info["Bandwidth"] = $_REQUEST["bandwidth"];
	} else {
		displayError("Bandwidth must be specified");
		return;
	}

	if ($_REQUEST["validFrom"] != "") {
		$info["ValidFrom"] = $_REQUEST["validFrom"];
	} else {
		displayError("ValidFrom must be specified");
		return;
	}

	if ($_REQUEST["validTo"] != "") {
		$info["ValidTo"] = $_REQUEST["validTo"];
	} else {
		displayError("ValidTo must be specified");
		return;
	}

	# Check optional data
	if ($_POST["agentRef"] != "") {
		$userInfo["AgentRef"] = $_POST["agentRef"];
	}		

	# Create radius user and check for error
	$res = $soap->createRadiusUserTopup($info);
	if ($res > 0) {
		displaySuccess("Added radius topup");
	} else {
		displayError("Error creating radius topup: ".strSoapError($res));
	}

	
}


# Check if we have an action
if (!isset($_REQUEST['frmaction'])) {
	# FIXME : redirect

} elseif ($_REQUEST['frmaction'] == "topups_add_main") {
	displayForm($_REQUEST['user_id']);

} elseif ($_REQUEST['frmaction'] == "topups_add_final") {
	doAdd($_REQUEST['user_id']);
}


# Menu footer
include("../shared-php/menu-footer.php");
# Footer
include("include/footer.php");

# vim: ts=4
?>
