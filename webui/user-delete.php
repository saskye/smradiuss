<?php
# Radius User Delete
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
			"Back to user list" => "user-main.php",
		),
));



# Display delete confirm screen
if (isset($_POST['frmaction']) && $_POST['frmaction'] == "delete") {

	# Check a user was selected
	if (isset($_POST['user_id'])) {
?>
		<p class="pageheader">Delete User</p>

		<form action="user-delete.php" method="post">
			<input type="hidden" name="frmaction" value="delete2" />
			<input type="hidden" name="user_id" value="<?php echo $_POST['user_id']; ?>" />
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
} elseif (isset($_POST['frmaction']) && $_POST['frmaction'] == "delete2") {
?>
	<p class="pageheader">User Delete Results</p>
<?php

	if (isset($_POST['user_id'])) {

		if (isset($_POST['confirm']) && $_POST['confirm'] == "yes") {

			$db->beginTransaction();

			$res = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}users_to_groups WHERE UserID = ".$db->quote($_POST['user_id']));
			if ($res !== FALSE) {
?>
				<div class="notice">User groups deleted</div>
<?php
			} else {
?>
				<div class="warning">Error removing groups from user</div>
				<div class="warning"><?php print_r($db->errorInfo()); ?></div>
<?php
			}

			if ($res !== FALSE) {
				$res = $db->exec("
					DELETE FROM 
						${DB_TABLE_PREFIX}wisp_userdata
					WHERE 
						UserID = ".$db->quote($_POST['user_id']."
				"));
				if ($res !== FALSE) {
?>
					<div class="notice">WiSP user data deleted</div>
<?php
				} else {
?>
					<div class="warning">Error removing WiSP user data</div>
					<div class="warning"><?php print_r($db->errorInfo()); ?></div>
<?php
				}
			}

			if ($res !== FALSE) {
				$res = $db->exec("
					DELETE FROM 
						${DB_TABLE_PREFIX}user_attributes 
					WHERE 
						UserID = ".$db->quote($_POST['user_id'])."
				");
				if ($res !== FALSE) {
?>
					<div class="notice">User attributes deleted</div>
<?php
				} else {
?>
					<div class="warning">Error removing user attributes</div>
					<div class="warning"><?php print_r($db->errorInfo()); ?></div>
<?php
				}
			}

			if ($res !== FALSE) {
				$res = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}users WHERE ID = ".$db->quote($_POST['user_id']));
				if ($res !== FALSE) {
?>
					<div class="notice">User deleted</div>
<?php
				} else {
?>
					<div class="warning">Error removing user</div>
					<div class="warning"><?php print_r($db->errorInfo()); ?></div>
<?php
				}
			}

			# Check if all is ok, if so, we can commit, else must rollback
			if ($res !== FALSE) {
				$db->commit();
?>
				<div class="notice">Changes comitted.</div>
<?php
			} else {
				$db->rollback();
?>
				<div class="notice">Changes reverted.</div>
<?php
			}

		} else {
?>
			<div class="warning">Delete user aborted</div>
<?php
		}

	} else {
?>
		<div class="warning">Invocation error, no user ID selected</div>
<?php
	}

} else {
?>
	<div class="warning">Invocation error</div>
<?php
}

printFooter();


# vim: ts=4
?>
