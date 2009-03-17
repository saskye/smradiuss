<?php
# Radius user add
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



function displayAdd()
{
	global $soap;


	$radiusClasses = $soap->getRadiusClasses();
	if (!is_array($radiusClasses)) {
		displayError("getRadiusClasses: ".strSoapError($radiusClasses));
		return;
	}
			
?>
	<script src="static/js.getRandomPass" type="text/javascript"></script>
	
	<div class="sectiontitle">Add Radius User</div>
	<p />

	<form action="radius-user-add.php" method="post">
		<div>
			<input type="hidden" name="frmaction" value="add_final">
		</div>
		<table class="entrytable">
			<tr>
				<td class="title">Username</td>
				<td class="entry"><input type="text" name="username" /></td>
			</tr>
			<tr>
				<td class="title">Password</td>
				<td class="entry">
					<input type="text" name="password" />
					<input type="button" value="generate" onClick="this.form.password.value=getRandomPass(8)" />
				</td>
			</tr>
			<tr>
				<td class="title">Class</td>
				<td class="entry">
					<select name="classID">
<?php
						foreach ($radiusClasses as $class) {
							printf("<option value=\"%s\">%s</option>",$class->ID,$class->Service);
						}
?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="title">Usage Cap</td>
				<td class="entry"><input type="text" name="usageCap" /></td>
			</tr>
			<tr>
				<td class="title">Notify Email</td>
				<td class="entry"><input type="text" name="notifyMethodEmail" /></td>
			</tr>
			<tr>
				<td class="title">Notify Cell #</td>
				<td class="entry"><input type="text" name="notifyMethodCell" /></td>
			</tr>
			<tr>
				<td class="title">AgentRef</td>
				<td class="entry"><input type="text" name="agentRef" /></td>
			</tr>
			<tr>
				<td class="title">Disabled</td>
				<td class="entry">
					<select name="agentDisabled">
						<option value="0" selected>no</option>
						<option value="1">yes</option>
					</select>
				</td>
			</tr>
			<tr>
				<td align="center" colspan="2">
					<input type="submit" value="Add" />
				</td>
			</tr>
		</table>
	</form>
<?php
}


function doAdd()
{
	global $soap;	

	
	$userInfo = NULL;

	# Verify data
	if ($_POST["username"] != "") {
		$userInfo["Username"] = $_POST["username"];
	} else {
		displayError("Username must be specified!");
		return;
	}

	if ($_POST["password"] != "") {
		$userInfo["Password"] = $_POST["password"];
	} else {
		displayError("Password must be specified!");
		return;
	}

	if ($_POST["username"] == $_POST["password"]) {
		displayError("Password must be specified!");
		return;
	}

	if ($_POST["classID"] != "") {
		$userInfo["ClassID"] = $_POST["classID"];
	} else {
		displayError("Class must be specified!");
		return;
	}

	# Check optional data
	if ($_POST["usageCap"] != "") {
		$userInfo["UsageCap"] = $_POST["usageCap"];
	}		

	if ($_POST["notifyMethodCell"] != "") {
		$userInfo["NotifyMethodCell"] = $_POST["notifyMethodCell"];
	}		
	if ($_POST["notifyMethodEmail"] != "") {
		$userInfo["NotifyMethodEmail"] = $_POST["notifyMethodEmail"];
	}		

	if ($_POST["agentRef"] != "") {
		$userInfo["AgentRef"] = $_POST["agentRef"];
	}		

	if ($_POST["agentDisabled"] != "") {
		$userInfo["AgentDisabled"] = $_POST["agentDisabled"];
	}		

	# Create radius user and check for error
	$res = $soap->createRadiusUser($userInfo);
	if ($res > 0) {
		displaySuccess("Added radius user");
	} else {
		displayError("Error creating radius user: ".strSoapError($res));
	}

}


# Check if we have an action
if (!isset($_POST['frmaction'])) {
	displayAdd();

} elseif ($_POST['frmaction'] == "add_main") {
	displayAdd();

} elseif ($_POST['frmaction'] == "add_final") {
	doAdd();
}


# Menu footer
include("../shared-php/menu-footer.php");
# Footer
include("include/footer.php");

# vim: ts=4
?>
