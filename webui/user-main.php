<?php
# Radius User List
# Copyright (C) 2008-2009, AllWorldIT
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


include_once("includes/header.php");
include_once("includes/footer.php");
include_once("includes/db.php");

$db = connect_db();

printHeader(array(
));

# If we have no action, display list
if (!isset($_POST['frmaction']))
{
?>
	<p class="pageheader">User List</p>

	<form id="main_form" action="user-main.php" method="post">
		<div class="textcenter">
			Action
			<select id="main_form_action" name="frmaction" 
					onchange="
						var myform = document.getElementById('main_form');
						var myobj = document.getElementById('main_form_action');

						if (myobj.selectedIndex == 2) {
							myform.action = 'user-add.php';
						} else if (myobj.selectedIndex == 3) {
							myform.action = 'user-delete.php';
						} else if (myobj.selectedIndex == 5) {
							myform.action = 'user-attributes.php';
						} else if (myobj.selectedIndex == 6) {
							myform.action = 'user-groups.php';
						}

						myform.submit();
					">
				<option selected="selected">select action</option>
				<option disabled="disabled"> - - - - - - - - - - - </option>
				<option value="add">Add User</option>
				<option value="delete">Delete User</option>
				<option disabled="disabled"> - - - - - - - - - - - </option>
				<option value="members">List User Attributes</option>
				<option value="members">List User Groups</option>
			</select> 
		</div>

		<p />

		<table class="results" style="width: 75%;">
			<tr class="resultstitle">
				<td class="textcenter">ID</td>
				<td class="textcenter">Username</td>
				<td class="textcenter">Disabled</td>
			</tr>
<?php
			$sql = "SELECT ID, Username, Disabled FROM ${DB_TABLE_PREFIX}users ORDER BY ID ASC";
			$res = $db->query($sql);

			$rownums = 0;
			# List users
			while ($row = $res->fetchObject()) {
				if ($row->id != NULL) {
					$rownums = $rownums + 1;
				} else {
					$rownums = $rownums - 1;
				}
?>
					<tr class="resultsitem">
						<td><input type="radio" name="user_id" value="<?php echo $row->id ?>"/><?php echo $row->id ?></td>
						<td><?php echo $row->username ?></td>
						<td class="textcenter"><?php echo $row->disabled ? 'yes' : 'no' ?></td>
					</tr>
<?php
			}
			$res->closeCursor();
			if ($rownums <= 0) {
?>
				<tr>
					<td class="textcenter">Group list is empty</td>
				</tr>
<?php
			}
			unset($rownums);
?>
		</table>
	</form>
<?php
}
printFooter();

# vim: ts=4
?>
