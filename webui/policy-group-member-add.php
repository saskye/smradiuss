<?php
# Policy group member add
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
			"Back to groups" => "policy-group-main.php",
			"Back to members" => "policy-group-member-main.php?policy_group_id=".$_POST['policy_group_id'],
		),
));


if ($_POST['frmaction'] == "add")  {
?>
	<p class="pageheader">Add Policy Group Member</p>
<?php
?>
		<form method="post" action="policy-group-member-add.php">
			<div>
				<input type="hidden" name="frmaction" value="add2" />
				<input type="hidden" name="policy_group_id" value="<?php echo $_POST['policy_group_id'] ?>" />
			</div>
			<table class="entry">
				<tr>
					<td class="entrytitle">
						Member
						<?php tooltip('policy_group_member'); ?>
					</td>
					<td><input type="text" name="policy_group_member_member" /></td>
				</tr>
				<tr>
					<td class="entrytitle">Comment</td>
					<td><textarea name="policy_group_member_comment" cols="40" rows="5"></textarea></td>
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
	<p class="pageheader">Policy Group Member Add Results</p>

<?php

	$stmt = $db->prepare("INSERT INTO ${DB_TABLE_PREFIX}policy_group_members (PolicyGroupID,Member,Comment,Disabled) VALUES (?,?,?,1)");
	
	$res = $stmt->execute(array(
		$_POST['policy_group_id'],
		$_POST['policy_group_member_member'],
		$_POST['policy_group_member_comment']
	));
	if ($res) {
?>
		<div class="notice">Policy group member created</div>
<?php
	} else {
?>
		<div class="warning">Failed to create policy group member</div>
		<div class="warning"><?php print_r($stmt->errorInfo()) ?></div>
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
