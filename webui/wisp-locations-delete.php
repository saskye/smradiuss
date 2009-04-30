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
			"Back to location list" => "wisp-locations-main.php",
		),
));



# Display delete confirm screen
if (isset($_POST['frmaction']) && $_POST['frmaction'] == "delete") {

	# Check a user was selected
	if (isset($_POST['location_id'])) {
?>
		<p class="pageheader">Delete Location</p>

		<form action="wisp-locations-delete.php" method="post">
			<input type="hidden" name="frmaction" value="delete2" />
			<input type="hidden" name="location_id" value="<?php echo $_POST['location_id']; ?>" />
			<div class="textcenter">
				Are you very sure you wish to remove this location and unlink all users linked to it? <br />
				<input type="submit" name="confirm" value="yes" />
				<input type="submit" name="confirm" value="no" />
			</div>
		</form>
<?php

	} else {
?>
		<div class="warning">No location selected</div>
<?php
	}

# SQL Updates
} elseif (isset($_POST['frmaction']) && $_POST['frmaction'] == "delete2") {
?>
	<p class="pageheader">Location Delete Results</p>
<?php

	if (isset($_POST['location_id'])) {

		if (isset($_POST['confirm']) && $_POST['confirm'] == "yes") {

			$db->beginTransaction();

			$res = $db->exec("
				UPDATE 
					${DB_TABLE_PREFIX}wisp_userdata 
				SET 
					LocationID = NULL 
				WHERE 
					LocationID = ".$db->quote($_POST['location_id'])."
			");

			if ($res !== FALSE) {
?>
				<div class="notice">Location members unlinked</div>
<?php
			} else {
?>
				<div class="warning">Error removing users from location</div>
				<div class="warning"><?php print_r($db->errorInfo()); ?></div>
<?php
				$db->rollback();
			}

			if ($res !== FALSE) {

				$res = $db->exec("
					DELETE FROM 
						${DB_TABLE_PREFIX}wisp_locations 
					WHERE 
						ID = ".$db->quote($_POST['location_id'])."
				");

				if ($res !== FALSE) {
?>
					<div class="notice">Location deleted</div>
<?php
				} else {
?>
					<div class="warning">Error removing location</div>
					<div class="warning"><?php print_r($db->errorInfo()); ?></div>
<?php
					$db->rollback();
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
			<div class="warning">Delete location aborted</div>
<?php
		}

	} else {
?>
		<div class="warning">Invocation error, no location ID selected</div>
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

