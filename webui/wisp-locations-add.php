<?php
# Radius Location Add
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
			"Back to locations list" => "wisp-locations-manage.php"
		),
));

if (isset($_POST['frmaction']) && $_POST['frmaction'] == "add") {
?>

	<p class="pageheader">Add location</p>
	<form method="post" action="wisp-locations-add.php">
		<div>
			<input type="hidden" name="frmaction" value="add2" />
		</div>
		<table class="entry">
			<tr>
				<td class="entrytitle">Location</td>
				<td><input type="text" name="location" /></td>
			</tr>
			<tr>
				<td class="textcenter" colspan="2">
					<input type="submit" />
				</td>
			</tr>
		</table>
	</form>

<?php

# Check we have all params
} elseif (isset($_POST['frmaction']) && $_POST['frmaction'] == "add2") {
?>
	<p class="pageheader">Location Add Results</p>
<?php

	# Check name
	if (empty($_POST['location'])) {
?>
		<div class="warning">Location cannot be empty</div>
<?php

	# Add to database
	} else {
		$stmt = $db->prepare("INSERT INTO ${DB_TABLE_PREFIX}wisp_locations (Location) VALUES (?)");
		$res = $stmt->execute(array(
			$_POST['location'],
		));

		# Was it successful?
		if ($res !== FALSE) {
?>
			<div class="notice">Location added</div>
<?php
		} else {
?>
			<div class="warning">Failed to add location</div>
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
