<?php
# Policy group member screen
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
			"Back to groups" => "group-main.php"
		),
));


# Check a policy group was selected
if (isset($_POST['group_id'])) {

?>

	<p class="pageheader">Group Members</p>

<?php

	# Get group name
	$group_stmt = $db->prepare("SELECT Name FROM ${DB_TABLE_PREFIX}groups WHERE ID = ?");
	$group_stmt->execute(array($_POST['group_id']));
	$row = $group_stmt->fetchObject();
	$group_stmt->closeCursor();

?>

		<table class="results" style="width: 75%;">
			<tr class="resultstitle">
				<td class="textcenter">ID</td>
				<td class="textcenter">Member</td>
				<td class="textcenter">Disabled</td>
			</tr>

<?php

			# Get list of members belonging to this group
			$stmt = $db->prepare("SELECT UserID FROM ${DB_TABLE_PREFIX}users_to_groups WHERE GroupID = ?");
			$res = $stmt->execute(array($_REQUEST['group_id']));

			$rownums = 0;
			# Loop with rows
			while ($row = $stmt->fetchObject()) {
				if ($row->userid != NULL) {
					$rownums = $rownums + 1;
				} else {
					$rownums = $rownums - 1;
				}

				$sql = "SELECT ID, Username, Disabled FROM ${DB_TABLE_PREFIX}users WHERE ID = ".$row->userid;
				$res = $db->query($sql);

				# List users
				while ($row = $res->fetchObject()) {

?>

					<tr class="resultsitem">
						<td><?php echo $row->id ?></td>
						<td><?php echo $row->username ?></td>
						<td class="textcenter"><?php echo $row->disabled ? 'yes' : 'no' ?></td>
					</tr>

<?php

				}
				$res->closeCursor();
			}
			$stmt->closeCursor();

			# Did we get any results?
			if ($rownums <= 0) {

?>

				<p />
				<tr>
					<td colspan="3" class="textcenter">Group has no users</td>
				</tr>

<?php

			}
			unset($rownums);

?>

		</table>

<?php

} else {

?>

	<div class="warning">Invalid invocation</div>

<?php

}
printFooter();


# vim: ts=4
?>
