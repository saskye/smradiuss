<?php
# Module: Policy delete
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



$db = connect_db();



printHeader(array(
		"Tabs" => array(
			"Back to user list" => "user-main.php",
		),
));



# Display delete confirm screen
if ($_POST['frmaction'] == "delete") {
	# Check a user was selected
	if (isset($_POST['user_id'])) {
?>
		<p class="pageheader">Delete User</p>

		<form action="user-delete.php" method="post">
			<div>
				<input type="hidden" name="frmaction" value="delete2" />
				<input type="hidden" name="user_id" value="<?php echo $_POST['user_id']; ?>" />
			</div>
			<div class="textcenter">
				Are you very sure? <br />
				<input type="submit" name="confirm" value="yes" />
				<input type="submit" name="confirm" value="no" />
			</div>
		</form>
<?php
	} else {
?>
		<div class="warning">No user selected</div>
<?php
	}
# SQL Updates
} elseif ($_POST['frmaction'] == "delete2") {
?>
	<p class="pageheader">User Delete Results</p>
<?php
	if (isset($_POST['user_id'])) {
		# Check to see if user's attributes are empty
		$temp = $_POST['user_id'];
		$sql = "SELECT * FROM ${DB_TABLE_PREFIX}user_attributes WHERE UserID = $temp";
		$check = $db->query($sql);

		if ($check->num_rows == 0) {
			if ($_POST['confirm'] == "yes") {	
				$res = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}users WHERE ID = ".$_POST['user_id']);
				if ($res !== FALSE) {
?>
					<div class="notice">User with ID: <?php print_r($_POST['user_id']);?> deleted</div>
<?php
				} else {
?>
					<div class="warning">Error deleting user</div>
					<div class="warning"><?php print_r($db->errorInfo()) ?></div>
<?php
				}
			} else {
?>
			<div class="warning">Delete user aborted</div>
<?php
			}
		} else {
?>
			<div class="warning">Attribute list is not empty!</div>
<?php
		}
	} else {
?>
		<div class="warning">Invocation error, no user ID selected</div>
<?php
	}
}
printFooter();


# vim: ts=4
?>

