<?php
# Policy member add
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
			"Back to policies" => "policy-main.php",
			"Back to members" => "policy-member-main.php?policy_id=".$_REQUEST['username'],
		),
));


if ($_POST['frmaction'] == "add")  {
?>
	<p class="pageheader">Add user</p>
<?php
	if (!empty($_POST['username'])) {
?>
		<form method="post" action="policy-member-add.php">
			<div>
				<input type="hidden" name="frmaction" value="add2" />
				<input type="hidden" name="policy_id" value="<?php echo $_POST['username'] ?>" />
			</div>
			<table class="entry">
				<tr>
					<td class="entrytitle texttop">
						Username
						<?php tooltip('Username'); ?>
					</td>
					<td><textarea name="member_source" cols="40" rows="5"/></textarea></td>
				</tr>
				<tr>
					<td colspan="2">
						<input type="submit" />
					</td>
				</tr>
			</table>
		</form>
<?php
	} else {
?>
		<div class="warning">Invalid user name</div>
<?php
	}
	
	
	
# Check we have all params
} elseif ($_POST['frmaction'] == "add2") {
?>
	<p class="pageheader">Policy Member Add Results</p>

<?php
	# Check source and dest are not blank
	if (empty($_POST['username'])) {
?>
		<div class="warning">A blank member is useless?</div>
<?php


	} else {
		$stmt = $db->prepare("INSERT INTO ${DB_TABLE_PREFIX}users (Username) VALUES (?)");
		
		$res = $stmt->execute(array(
			$_POST['username'],
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
