<?php
# Policy member change
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
));


# Display edit screen
if ($_POST['frmaction'] == "edit") {
	# Check a user was selected
	if (isset($_POST['user_id'])) {
		# Prepare statement
		$userID = $_POST['user_id'];
		$sql = "SELECT Password, FirstName, LastName, Location, Email, Phone, IPAddress, PoolName, GroupName, AddressList FROM wispusers WHERE ID = $userID";
		$res = $db->query($sql); 
		$row = $res->fetchObject();

?>

		<p class="pageheader">Edit User Information</p>

		<form action="wisp-user-edit.php" method="post">
			<input type="hidden" name="frmaction" value="edit2" />
			<input type="hidden" name="user_id" value="<?php echo $_POST['user_id']; ?>" />
			<table class="entry">
				<tr>
					<td class="entrytitle textcenter" colspan="3">Account Information</td>
				</tr>
				<tr>
					<td><div></div></td>
					<td>Old Value</td>
					<td>New Value</td>
				</tr>
				<tr>
					<td class="entrytitle texttop">Password</td>
					<td class="oldval texttop"><?php echo $row->password ?></td>
					<td><input type="password" name="new_password" /></td>
				</tr>
				<tr>
					<td class="entrytitle textcenter" colspan="3">Private Information</td>
				</tr>
				<tr>
					<td><div></div></td>
					<td>Old Value</td>
					<td>New Value</td>
				</tr>
				<tr>
					<td class="entrytitle texttop">First Name</td>
					<td class="oldval texttop"><?php echo $row->firstname ?></td>
					<td><input type="text" name="new_firstname" /></td>
				</tr>
				<tr>
					<td class="entrytitle texttop">Last Name</td>
					<td class="oldval texttop"><?php echo $row->lastname ?></td>
					<td><input type="text" name="new_lastname" /></td>
				</tr>
				<tr>
					<td class="entrytitle texttop">Location</td>
					<td class="oldval texttop"><?php echo $row->location ?></td>
					<td><input type="text" name="new_location" /></td>
				</tr>
				<tr>
					<td class="entrytitle texttop">Email</td>
					<td class="oldval texttop"><?php echo $row->email ?></td>
					<td><input type="text" name="new_email" /></td>
				</tr>
				<tr>
					<td class="entrytitle texttop">Phone</td>
					<td class="oldval texttop"><?php echo $row->phone ?></td>
					<td><input type="text" name="new_phone" /></td>
				</tr>
				<tr>
					<td class="entrytitle texttop">IPAddress</td>
					<td class="oldval texttop"><?php echo $row->ipaddress ?></td>
					<td><input type="text" name="new_ipaddress" /></td>
				</tr>
				<tr>
					<td class="entrytitle texttop">Pool Name</td>
					<td class="oldval texttop"><?php echo $row->poolname ?></td>
					<td><input type="text" name="new_poolname" /></td>
				</tr>
				<tr>
					<td class="entrytitle texttop">Group Name</td>
					<td class="oldval texttop"><?php echo $row->groupname ?></td>
					<td><input type="text" name="new_groupname" /></td>
				</tr>
				<tr>
					<td class="entrytitle texttop">Address List</td>
					<td class="oldval texttop"><?php echo $row->addresslist ?></td>
					<td><input type="text" name="new_addresslist" /></td>
				</tr>
			</table>

			<p />

			<div class="textcenter">
				<input type="submit" />
			</div>
		</form>

<?php

	$res->closeCursor();
	} else {

?>

		<div class="warning">No user selected</div>

<?php

	}
# SQL Updates
} elseif ($_POST['frmaction'] == "edit2") {

?>

	<p class="pageheader">User Edit Results</p>

<?php

	# Check a user was selected
	if (isset($_POST['user_id'])) {

		$updates = array();

		if (!empty($_POST['new_password'])) {
			array_push($updates,"Password = ".$db->quote($_POST['new_password']));
		}
		if (!empty($_POST['new_firstname'])) {
			array_push($updates,"FirstName = ".$db->quote($_POST['new_firstname']));
		}
		if (!empty($_POST['new_lastname'])) {
			array_push($updates,"LastName = ".$db->quote($_POST['new_lastname']));
		}
		if (!empty($_POST['new_location'])) {
			array_push($updates,"Location = ".$db->quote($_POST['new_location']));
		}
		if (!empty($_POST['new_email'])) {
			array_push($updates,"Email = ".$db->quote($_POST['new_email']));
		}
		if (!empty($_POST['new_phone'])) {
			array_push($updates,"Phone = ".$db->quote($_POST['new_phone']));
		}
		if (!empty($_POST['new_ipaddress'])) {
			array_push($updates,"IPAddress = ".$db->quote($_POST['new_ipaddress']));
		}
		if (!empty($_POST['new_poolname'])) {
			array_push($updates,"PoolName = ".$db->quote($_POST['new_poolname']));
		}
		if (!empty($_POST['new_groupname'])) {
			array_push($updates,"GroupName = ".$db->quote($_POST['new_groupname']));
		}
		if (!empty($_POST['new_addresslist'])) {
			array_push($updates,"AddressList = ".$db->quote($_POST['new_addresslist']));
		}

		# Check if we have updates
		if (sizeof($updates) > 0) {
			$updateStr = implode(', ',$updates);

			$res = $db->exec("UPDATE wispusers SET $updateStr WHERE ID = ".$db->quote($_POST['user_id']));
			if ($res) {

?>

				<div class="notice">User updated</div>

<?php

			} else {

?>

				<div class="warning">Error updating user</div>
				<div class="warning"><?php print_r($db->errorInfo()) ?></div>

<?php

			}

		# Warn
		} else {

?>

			<div class="warning">No user updates</div>

<?php

		}

	# Warn
	} else {

?>

		<div class="error">No user data available</div>

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

