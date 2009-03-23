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


session_start();

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
	if (isset($_POST['group_id'])) {
?>
		<p class="pageheader">Remove Group Assignment</p>

		<form action="user-groups-delete.php" method="post">
			<div>
				<input type="hidden" name="frmaction" value="delete2" />
				<input type="hidden" name="group_id" value="<?php echo $_POST['group_id']; ?>" />
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
		<div class="warning">No group assignment selected</div>
<?php
	}
	
	
# SQL Updates
} elseif ($_POST['frmaction'] == "delete2") {
?>
	<p class="pageheader">Group Assignment Removal Results</p>
<?php
	if (isset($_POST['group_id'])) {
		if ($_POST['confirm'] == "yes") {	
			$res = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}users_to_groups WHERE UserID = ".$_SESSION['groups_user_id']." AND GroupID = ".$_POST['group_id']);
			if ($res !== FALSE) {
?>
				<div class="notice">Group with ID: <?php print_r($_POST['group_id']);?> deleted from user with ID: <?php print_r($_SESSION['groups_user_id']);?></div>
<?php		
				session_destroy();
			} else {
?>
				<div class="warning">Error removing group assignment</div>
				<div class="warning"><?php print_r($db->errorInfo()) ?></div>
<?php
			}
?>

<?php
		# Warn
		} else {
?>
		<div class="warning">Remove Group Assignment aborted</div>
<?php
		}
?>
<?php
	} else {
?>
		<div class="warning">Invocation error, no group ID selected</div>
<?php
	}

}
printFooter();


# vim: ts=4
?>

