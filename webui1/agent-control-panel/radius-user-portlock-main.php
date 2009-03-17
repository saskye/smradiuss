<?php
# Radius user port locking
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



function displayList($userID)
{
	global $soap;

	$userInfo = $soap->getRadiusUser($userID);
	if (!is_object($userInfo)) {
		displayError("getRadiusUser: ".strSoapError($userInfo));
		return;
	}

	$portlocks = $soap->getRadiusUserPortLocks($userID);
	if (!is_array($portlocks)) {
		displayError("getRadiusUserLocks: ".strSoapError($portlocks));
		return;
	}

?>
	<div class="sectiontitle">Radius User Port Locks</div>
	<p />

	<form id="main_form" action="radius-user-portlock-main.php" method="post">
		<div>
			<input type="hidden" name="user_id" value="<?php echo $userID; ?>" />
		</div>
		<div class="text-center">
			Action
			<select id="main_form_action" name="frmaction" 
					onchange="
						var myform = document.getElementById('main_form');
						var myobj = document.getElementById('main_form_action');

						if (myobj.selectedIndex == 2) {
							myform.action = 'radius-user-portlock-add.php';
						} else if (myobj.selectedIndex == 3) {
							myform.action = 'radius-user-portlock-remove.php';
						}

						myform.submit();
			">
				<option selected="selected">select action</option>
				<option disabled="disabled"> - - - - - - - - - - - </option>
				<option value="portlocks_add_main">Add</option>
				<option value="portlocks_remove_main">Remove</option>
			</select>
		</div>

		<table class="resulttable">
			<tr>
				<td></td>
				<td class="title" style="width: 150px;">Port</td>
				<td class="title">Disabled</td>
				<td class="title">AgentRef</td>
			</tr>
<?php
			# If below one lock, display no locks
			if (count($portlocks) < 1) {
?>
			<tr>
				<td colspan="4" align="center">No locking active</td>
			</tr>
<?php
			} else {
				foreach ($portlocks as $portlock) {
?>
					<tr>
						<td><input type="radio" name="portlock_id" value="<?php echo $portlock->ID ?>" /></td>
						<td><?php echo $portlock->Port; ?></td>
						<td class="text-center"><?php echo $portlock->AgentDisabled ? "yes" : "no"; ?></td>
						<td><?php echo $portlock->AgentRef; ?></td>
					</tr>
<?php
				}
			}
?>
		</table>
	</form>
<?php
}




# Check if we have an action
if (!isset($_REQUEST['frmaction'])) {
	# FIXME : redirect

} elseif ($_REQUEST['frmaction'] == "portlocks_main") {
	displayList($_REQUEST['user_id']);
}


# Menu footer
include("../shared-php/menu-footer.php");
# Footer
include("include/footer.php");

# vim: ts=4
?>
