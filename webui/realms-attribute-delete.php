<?php
# Radius Realms Attribute Delete
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
			"Back to realm list" => "realms-main.php",
		),
));


# Display delete confirm screen
if (isset($_POST['frmaction']) && $_POST['frmaction'] == "delete") {

	# Check a user was selected
	if (isset($_POST['attr_id'])) {
?>
		<p class="pageheader">Delete Attribute</p>

		<form action="realms-attribute-delete.php" method="post">
			<div>
				<input type="hidden" name="frmaction" value="delete2" />
				<input type="hidden" name="attr_id" value="<?php echo $_POST['attr_id']; ?>" />
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
		<div class="warning">No attribute selected</div>
<?php
	}

# SQL Updates
} elseif (isset($_POST['frmaction']) && $_POST['frmaction'] == "delete2") {

?>
	<p class="pageheader">Attribute Delete Results</p>
<?php

	# Make sure we have the attribute ID set
	if (isset($_POST['attr_id'])) {

		# And make sure user confirmed
		if (isset($_POST['confirm']) && $_POST['confirm'] == "yes") {

			$res = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}realm_attributes WHERE ID = ".$db->quote($_POST['attr_id']));
			if ($res !== FALSE) {
?>
				<div class="notice">Attribute with ID: <?php print_r($_POST['attr_id']);?> deleted</div>
<?php
			} else {
?>
				<div class="warning">Error deleting attribute</div>
				<div class="warning"><?php print_r($db->errorInfo()) ?></div>
<?php
			}

		# Warn
		} else {
?>
			<div class="warning">Delete attribute aborted</div>
<?php
		}

	} else {
?>
		<div class="warning">Invocation error, no attribute ID selected</div>
<?php
	}
}
printFooter();


# vim: ts=4
?>