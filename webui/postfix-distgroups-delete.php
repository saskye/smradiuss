<?php
# Distribution group delete
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



$db = connect_postfix_db();



printHeader(array(
		"Tabs" => array(
			"Back to groups" => "postfix-distgroups-main.php",
		),
));



# Display delete confirm screen
if ($_POST['frmaction'] == "delete") {

	# Check a postfix group was selected
	if (isset($_POST['postfix_group_id'])) {
?>
		<p class="pageheader">Delete Distribution Group</p>

		<form action="postfix-distgroups-delete.php" method="post">
			<div>
				<input type="hidden" name="frmaction" value="delete2" />
				<input type="hidden" name="postfix_group_id" value="<?php echo $_POST['postfix_group_id']; ?>" />
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
		<div class="warning">No distribution group selected</div>
<?php
	}
	
	
	
# SQL Updates
} elseif ($_POST['frmaction'] == "delete2") {
?>
	<p class="pageheader">Distribution Group Delete Results</p>
<?php
	if (isset($_POST['postfix_group_id'])) {

		if ($_POST['confirm'] == "yes") {	
			
			$res = $db->exec("
				DELETE FROM 
					${DB_TABLE_PREFIX}distribution_group_members 
				WHERE 
					DistributionGroupID = ".$db->quote($_POST['postfix_group_id'])
			);
			if ($res !== FALSE) {
?>
				<div class="notice">Distribution group members deleted</div>
<?php
			} else {
?>
				<div class="warning">Error deleting distribution group members!</div>
				<div class="warning"><?php print_r($db->errorInfo()) ?></div>
<?php
				$db->rollback();
			}

			if ($res !== FALSE) {
				$res = $db->exec("DELETE FROM ${DB_TABLE_PREFIX}distribution_groups WHERE ID = ".$db->quote($_POST['postfix_group_id']));
				if ($res) {
?>
					<div class="notice">Distribution group deleted</div>
<?php
				} else {
?>
					<div class="warning">Error deleting distribution group!</div>
					<div class="warning"><?php print_r($db->errorInfo()) ?></div>
<?php
				}
			}
		} else {
?>
			<div class="notice">Distribution group not deleted, aborted by user</div>
<?php
		}

	# Warn
	} else {
?>
		<div class="warning">Invocation error, no distribution group ID</div>
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

