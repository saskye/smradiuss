<?php
# Radius User Group Add
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


session_start();


include_once("includes/header.php");
include_once("includes/footer.php");
include_once("includes/db.php");


$db = connect_db();


printHeader(array(
));


if (isset($_SESSION['groups_user_id'])) {
	if (isset($_POST['frmaction']) && $_POST['frmaction'] == "add") {

?>
		<p class="pageheader">Available Groups</p>

		<form id="main_form" action="user-groups-add.php" method="post">
			<div class="textcenter">
				<input type="hidden" name="frmaction" value="add2" />
				<table class="entry">
					<tr>
						<td class="entrytitle">Comment</td>
						<td class="entrytitle">Disabled</td>
					</tr>
					<tr>
						<td><input type="text" name="users_to_groups_comment" /></td>
						<td>
							<select name="users_group_disabled">
								<option value="0">No</option>
								<option value="1">Yes</option>
							</select>
						</td>
						<td>
							<input type="submit" value="Submit" />
						</td>
					</tr>
				</table>
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

				# List current available groups
				$sql = "SELECT ID, Name, Priority, Disabled, Comment FROM ${DB_TABLE_PREFIX}groups ORDER BY ID";
				$res = $db->query($sql);

				while ($row = $res->fetchObject()) {

?>

					<tr class="resultsitem">
						<td><input type="radio" name="group_id" value="<?php echo $row->id; ?>" /></td>
						<td><?php echo $row->name; ?></td>
						<td><?php echo $row->priority; ?></td>
						<td class="textcenter"><?php echo $row->disabled ? 'yes' : 'no'; ?></td>
						<td><?php echo $row->comment; ?></td>
					</tr>

<?php

				}
				$res->closeCursor();

?>

			</table>
		</form>

<?php

	} elseif (isset($_POST['frmaction']) && $_POST['frmaction'] == "add2") {

?>

		<p class="pageheader">Group assignment results</p>

<?php

		if (isset($_POST['group_id']) && !empty($_POST['users_to_groups_comment'])) {
			$stmt = $db->prepare("INSERT INTO ${DB_TABLE_PREFIX}users_to_groups (UserID,GroupID,Comment,Disabled) VALUES (?,?,?,?)");

			$res = $stmt->execute(array(
						$_SESSION['groups_user_id'],
						$_POST['group_id'],
						$_POST['users_group_comment'],
						$_POST['users_group_disabled'],
						));

			if ($res) {

?>

				<div class="notice">Group assignment successful</div>

<?php

			} else {

?>

				<div class="warning">Failed to assign group to user</div>
				<div class="warning"><?php print_r($stmt->errorInfo()) ?></div>

<?php

			}
		} else {

?>

			<div class="warning">One or more values not set</div>

<?php

		}
	}
} else {

?>

	<div class="warning">No user id received</div>

<?php

}

printFooter();

# vim: ts=4
?>
