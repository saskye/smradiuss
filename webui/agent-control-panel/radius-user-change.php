<?php
# Radius user change
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



function displayChange($userID)
{
	global $soap;


	# Grab user info
	$userInfo = $soap->getRadiusUser($userID);
	if (!is_object($userInfo)) {
		displayError("getRadiusUser: ".strSoapError($userInfo));
		return;
	}
		
	# check if we dialup or not
	$isDialup = preg_match('/dialup/i',$userInfo->Service);


?>
	<script src="static/js.getRandomPass" type="text/javascript"></script>

	
	<div class="sectiontitle">Change Radius User</div>
	<p />

	<form action="radius-user-change.php" method="post">
		<div>
			<input type="hidden" name="user_id" value="<?php echo $userID; ?>" />
			<input type="hidden" name="frmaction" value="change_final" />
		</div>
		<table class="entrytable">
			<tr>
				<td class="title">Attribute</td>
				<td class="title">Value</td>
				<td class="title">New Value</td>
			</tr>

			<tr>
				<td class="title2">Service</td>
				<td class="oldvalue"><?php echo $userInfo->Service; ?></td>
				<td></td>
			</tr>

			<tr>
				<td class="title2">UsageCap</td>
				<td class="oldvalue"><?php echo $userInfo->UsageCap; ?></td>
				<td class="entry"><input type="text" name="usageCap" /></td>
			</tr>

			<tr>
				<td class="title2">Password</td>
				<td class="oldvalue">*encrypted*</td>
				<td class="entry">
					<input type="text" name="password" />
					<input type="button" value="generate" onclick="this.form.password.value=getRandomPass(8)" />
				</td>
			</tr>
<?php
			# DSL USER ONLY 
			if (!$isDialup) {
?>
				<tr>
					<td class="title2">Notify Email</td>
					<td class="oldvalue"><?php echo $userInfo->NotifyMethodEmail ?></td>
					<td class="entry"><input type="text" name="notifyMethodEmail" /></td>
				</tr>
				<tr>
					<td class="title2">Notify Cell #</td>
					<td class="oldvalue"><?php echo $userInfo->NotifyMethodCell ?></td>
					<td class="entry"><input type="text" name="notifyMethodCell" /></td>
				</tr>
<?php
			}
?>
			<tr>
				<td class="title2">AgentRef</td>
				<td class="oldvalue"><?php echo $userInfo->AgentRef; ?></td>
				<td class="entry"><input type="text" name="agentRef" /></td>
			</tr>
			<tr>
				<td class="title2">Disabled</td>
				<td class="oldvalue"><?php echo $userInfo->AgentDisabled ? "yes" : "no"; ?></td>
				<td class="entry">
					<select name="agentDisabled">
						<option value="">-</option>
						<option value="0">no</option>
						<option value="1">yes</option>
					</select>
				</td>
				<td></td>
			</tr>
		</table>
		<div class="text-center">
			<input type="submit" value="Update" />
		</div>
	</form>		
<?php
}


# Actual form action to update radius user
function doUpdate($userID) {
	global $soap;

	# Create update hash
	$update = NULL;

	if ($_POST["password"] != "") {
		$update["Password"] = $_POST["password"];
	}		

	if ($_POST["usageCap"] != "") {
		$update["UsageCap"] = $_POST["usageCap"];
	}		

	if ($_POST["notifyMethodCell"] != "") {
		$update["NotifyMethodCell"] = $_POST["notifyMethodCell"];
	}		
	if ($_POST["notifyMethodEmail"] != "") {
		$update["NotifyMethodEmail"] = $_POST["notifyMethodEmail"];
	}		

	if ($_POST["agentRef"] != "") {
		$update["AgentRef"] = $_POST["agentRef"];
	}		

	if ($_POST["agentDisabled"] != "") {
		$update["AgentDisabled"] = $_POST["agentDisabled"];
	}		

	# If there are still updates to be done, do them
	if ($update != NULL) {
		$update["ID"] = $userID;
		
		$res = $soap->updateRadiusUser($update);
		if ($res == 0) {
			displaySuccess("Radius user updated");
		} else {
			displayError("Error updating radius user($res): ".strSoapError($res));
		}
	# Or report no updates to be made
	} else {
		displaySuccess("No updates to be made!");
	}
}




# Check if we have an action
if (!isset($_REQUEST['frmaction'])) {
	# FIXME : redirect

} elseif ($_REQUEST['frmaction'] == "change_main") {
	displayChange($_REQUEST['user_id']);

} elseif ($_REQUEST['frmaction'] == "change_final") {
	doUpdate($_REQUEST['user_id']);
}


# Menu footer
include("../shared-php/menu-footer.php");
# Footer
include("include/footer.php");

# vim: ts=4
?>
