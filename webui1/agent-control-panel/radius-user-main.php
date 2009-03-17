<?php
# Radius user search
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

# Include databae functionality
include_once("include/db.php");



$db = connect_db();



# Function to display search box
function displaySearch() {
?>
	<div class="sectiontitle">Search Radius Users</div>
	<p />

	<form action="radius-user-main.php" method="post">
		<div>
			<input type="hidden" name="frmaction" value="search_main" />
		</div>
		<table class="entrytable">
			<tr>
				<td colspan="2"></td>
				<td class="textcenter">Order by</td>
			</tr>
			<tr>
				<td class="title">Username</td>
				<td class="entry">
					<input type="text" name="searchUsername" class="entry" value="<?php
						if (isset($_SESSION['radiusUser_searchUsername'])) {
							echo $_SESSION['radiusUser_searchUsername'];
						}
					?>" />
				</td>
				<td align="center">
					<input type="radio" name="searchOrderBy" value="Username" <?php
						if (
								isset($_SESSION['radiusUser_searchOrderBy']) && (
									$_SESSION['radiusUser_searchOrderBy'] == "" || 
									$_SESSION['radiusUser_searchOrderBy'] == "Username"
								)
						) {
							echo "checked";
						}
					?> />
				</td>
			</tr>
		</table>
		<div class="text-center">
			<input type="submit" />
		</div>
	</form>

Note On Searching:
<ul>
	<li>Wildcards can be specified with *'s. For example: *@realm</li>
	<li>Blank search criteria matches everything</li>
	<li>Results limited to 100</li>
</ul>
<?php
}





function displaySearchResults($searchOptions)
{
	global $soap;
	global $userID;

?>
	<div class="sectiontitle">Radius User Search Results</div>
	<p />

	<form id="main_form" action="radius-user-main.php" method="post">
		<div class="text-center">
			Action
			<select id="main_form_action" name="frmaction" 
					onchange="
						var myform = document.getElementById('main_form');
						var myobj = document.getElementById('main_form_action');

						if (myobj.selectedIndex == 2) {
							myform.action = 'radius-user-change.php';
						} else if (myobj.selectedIndex == 3) {
							myform.action = 'radius-user-delete.php';
						} else if (myobj.selectedIndex == 4) {
							myform.action = 'radius-user-info.php';
						} else if (myobj.selectedIndex == 5) {
							myform.action = 'radius-user-logs.php';
						} else if (myobj.selectedIndex == 6) {
							myform.action = 'radius-user-topup-add.php';
						} else if (myobj.selectedIndex == 7) {
							myform.action = 'radius-user-topup-main.php';
						} else if (myobj.selectedIndex == 8) {
							myform.action = 'radius-user-portlock-main.php';
						} else if (myobj.selectedIndex == 10) {
							myform.action = 'radius-user-add.php';
						}

						myform.submit();
			">
				<option selected="selected">select action</option>
				<option disabled="disabled"> - T H I S - U S E R - </option>
				<option value="change_main">Change</option>
				<option value="delete_main">Delete</option>
				<option value="info_main">Info</option>
				<option value="logs_main">Logs</option>
				<option value="topups_add_main">Topups: Add</option>
				<option value="topups_main">Topups: Search</option>
				<option value="portlocks_main">Port Locking</option>
				<option disabled="disabled"> - - - - - - - - - - - </option>
				<option value="add_main">Add User</option>
			</select>
		</div>

		<table class="resulttable">
			<tr>
				<td></td>
				<td class="title">Username</td>
				<td class="title">Service Class</td>
				<td class="title">UsageCap</td>
				<td class="title">AgentRef</td>
				<td class="title">Disabled</td>
			</tr>
<?php
			$sql = "SELECT ID, Username, Disabled FROM ${DB_TABLE_PREFIX}users ORDER BY ID ASC";
			$res = $db->query($sql);

			while ($row = $res->fetchObject()) {
?>
				<tr>
					<td><input type="radio" name="user_id" value="<?php echo $row->id; ?>" /></td>
					<td><?php echo $row->username;  ?></td>
					<td align="center"><?php echo $row->disabled ? "yes" : "no"; ?></td>
				</tr>
<?php
			}
			$res->closeCursor();
?>
		</table>
	</form>
<?php
}



# Menu footer
include("../shared-php/menu-footer.php");
# Footer
include("include/footer.php");

# vim: ts=4
?>
