<?php
# Policy add
# Copyright (C) 2008, LinuxRulz
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
include_once("includes/tooltips.php");



$db = connect_db();



printHeader(array(
		"Tabs" => array(
			"Back to user list" => "policy-main.php"
		),
));



if ($_POST['frmaction'] == "add") {
?>
	<p class="pageheader">Add user</p>

	<form method="post" action="user-add.php">
		<div>
			<input type="hidden" name="frmaction" value="add2" />
		</div>
		<table class="entry">
			<tr>
				<td class="entrytitle">Username</td>
				<td><input type="text" name="user_name" /></td>
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
} elseif ($_POST['frmaction'] == "add2") {
?>
	<p class="pageheader">User Add Results</p>

<?php
	# Check name
	if (empty($_POST['user_name'])) {
?>
		<div class="warning">Username cannot be empty</div>
<?php

	} else {
		$stmt = $db->prepare("INSERT INTO ${DB_TABLE_PREFIX}users (Username) VALUES (?)");

		$res = $stmt->execute(array(
			$_POST['user_name'],
		));
		if ($res) {
?>
			<div class="notice">User added</div>
<?php
		} else {
?>
			<div class="warning">Failed to add user</div>
			<div class="warning"><?php print_r($stmt->errorInfo()) ?></div>
<?php
		}
	}
} else {
?>
	<div class="warning">Invalid invocation</div>
<?php
}

printFooter();


# vim: ts=4
?>
