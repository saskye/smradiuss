<?php
# Radius Realms Add
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
			"Back to realms" => "realms-main.php",
		),
));


if (isset($_POST['frmaction']) && $_POST['frmaction'] == "add") {
?>
	<p class="pageheader">Add Realm</p>

	<form method="post" action="realms-add.php">
		<input type="hidden" name="frmaction" value="add2" />
		<table class="entry">
			<tr>
				<td class="entrytitle">Name</td>
				<td><input type="text" name="realms_name" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Disabled</td>
				<td>
					<select name="realms_disabled">
						<option value="0">No</option>
						<option value="1">Yes</option>
					</select>
				</td>
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
	<p class="pageheader">Realm Add Results</p>
<?php
	if (!empty($_POST['realms_name'])) {

		$stmt = $db->prepare("INSERT INTO ${DB_TABLE_PREFIX}realms (Name,Disabled) VALUES (?,?)");
		$res = $stmt->execute(array(
			$_POST['realms_name'],
			$_POST['realms_disabled']
		));

		if ($res !== FALSE) {
?>
			<div class="notice">Realm added</div>
<?php
		} else {
?>
			<div class="warning">Failed to add realm</div>
			<div class="warning"><?php print_r($stmt->errorInfo()) ?></div>
<?php
		}
	} else {
?>
		<div class="warning">Need a realm name!</dv>
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
