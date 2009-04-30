<?php
# Radius User Group List
# Copyright (C) 2007-2009, AllWorldIT
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
		"Tabs" => array(
			"Back to user list" => "user-main.php"
		),
));


?>

<p class="pageheader">Groups List</p>

<form id="main_form" action="user-groups.php" method="post">
	<div class="textcenter">
		Action
		<select id="main_form_action" name="frmaction" 
				onchange="
					var myform = document.getElementById('main_form');
					var myobj = document.getElementById('main_form_action');

					if (myobj.selectedIndex == 2) {
						myform.action = 'user-groups-add.php';
					} else if (myobj.selectedIndex == 3) {
						myform.action = 'user-groups-delete.php';
					}

					myform.submit();
				">
			<option selected="selected">select action</option>
			<option disabled="disabled"> - - - - - - - - - - - </option>
			<option value="add">Assign Group</option>
			<option value="delete">Remove Group Assignment</option>
		</select> 
	</div>

	<p />

	<table class="results" style="width: 75%;">
		<tr class="resultstitle">
			<td class="textcenter">ID</td>
			<td class="textcenter">Name</td>
			<td class="textcenter">Priority</td>
			<td class="textcenter">Disabled</td>
			<td class="textcenter">Comment</td>
		</tr>

<?php

		if (isset($_POST['user_id'])) {
			$sql = "SELECT GroupID FROM ${DB_TABLE_PREFIX}users_to_groups WHERE UserID = ".$db->quote($_POST['user_id']);
			$res = $db->query($sql);

			while ($row = $res->fetchObject()) {
				$sql = "
					SELECT 
						ID, Name, Priority, Disabled, Comment
					FROM 
						${DB_TABLE_PREFIX}groups 
					WHERE 
						ID = ".$db->quote($row->groupid)."
				";
				$res2 = $db->query($sql);

				while ($row = $res2->fetchObject()) {
?>
					<tr class="res2">
						<td><input type="radio" name="group_id" value="<?php echo $row->id; ?>"/></td>
						<td><?php echo $row->name; ?></td>
						<td><?php echo $row->priority; ?></td>
						<td class="textcenter"><?php echo $row->disabled ? 'yes' : 'no'; ?></td>
						<td><?php echo $row->comment; ?></td>
					</tr>
<?php
				}

				$res2->closeCursor();
			}

			if ($res->rowCount() == 0) {
?>
				<tr>
					<td>User does not belong to any groups</td>
				</tr>
<?php
			}

			$res->closeCursor();

		} else {
?>
			<tr>
				<td>Invocation error, no user ID selected</td>
			</tr>
<?php
		}
?>
	</table>
</form>
<?php
 
printFooter();

# vim: ts=4
?>
