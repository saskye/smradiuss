<?php
# Radius Group Add
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
			"Back to groups" => "group-main.php",
		),
));


if (isset($_POST['frmaction']) && $_POST['frmaction'] == "add") {

?>

	<p class="pageheader">Add Group</p>

	<form method="post" action="group-add.php">
		<input type="hidden" name="frmaction" value="add2" />
		<table class="entry">
			<tr>
				<td class="entrytitle">Name</td>
				<td><input type="text" name="group_name" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Priority</td>
				<td><input type="text" name="group_priority" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Disabled</td>
				<td>
					<select name="group_disabled">
						<option value="0">No</option>
						<option value="1">Yes</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="entrytitle texttop">Comment</td>
				<td><textarea name="group_comment" cols="40" rows="5"></textarea></td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="submit" />
				</td>
			</tr>
		</table>
	</form>
<?php

# Check we have all params
} elseif (isset($_POST['frmaction']) && $_POST['frmaction'] == "add2") {

?>

	<p class="pageheader">Group Add Results</p>

<?php

	if (!empty($_POST['group_name'])) {

		$stmt = $db->prepare("INSERT INTO ${DB_TABLE_PREFIX}groups (Name,Priority,Disabled,Comment) VALUES (?,?,?,?)");

		$res = $stmt->execute(array(
			$_POST['group_name'],
			$_POST['group_priority'],
			$_POST['group_disabled'],
			$_POST['group_comment'],
		));
		if ($res !== FALSE) {
?>
			<div class="notice">Group created</div>
<?php
		} else {
?>
			<div class="warning">Failed to create group</div>
			<div class="warning"><?php print_r($stmt->errorInfo()) ?></div>
<?php
		}

	} else {
?>
		<div class="warning">Group name cannot be empty!</div>
<?php

	}

} else {

?>
	<div class="warning">Invalid invocation</div>
<?php

}


printFooter();


# vim: ts=4
?>
