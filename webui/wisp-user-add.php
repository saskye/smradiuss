<?php
# Policy add
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


if (!isset($_POST['frmaction'])) {

?>

	<p class="pageheader">Add user</p>

	<!-- Add user input fields -->
	<form method="post" action="wisp-user-add.php">
		<div>
			<input type="hidden" name="frmaction" value="insert" />
		</div>
		<table class="entry">
			<tr>
				<td class="textcenter" colspan="2">Account Information</td>
			</tr>
			<tr>
				<td><div></div><td>
			</tr>
			<tr>
				<td class="entrytitle">User Name</td>
				<td><input type="text" name="user_name" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Password</td>
				<td><input type="password" name="user_password" /></td>
			</tr>
			<tr>
				<td><div></div><td>
			</tr>
			<tr>
				<td class="textcenter" colspan="2">Private Information</td>
			</tr>
			<tr>
				<td><div></div><td>
			</tr>
			<tr>
				<td class="entrytitle">First Name</td>
				<td><input type="text" name="user_first_name" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Last Name</td>
				<td><input type="text" name="user_last_name" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Phone</td>
				<td><input type="text" name="user_phone" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Location</td>
				<td><input type="text" name="user_location" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Email Address</td>
				<td><input type="text" name="user_email" /></td>
			</tr>
			<tr>
				<td class="entrytitle">IP Address</td>
				<td><input type="text" name="user_ip_address" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Pool Name</td>
				<td><input type="text" name="pool_name" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Group Name</td>
				<td><input type="text" name="group_name" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Address List</td>
				<td><input type="text" name="address_list" /></td>
			</tr>
			<tr>
				<td class="textcenter" colspan="2"><input type="submit" value="Submit" /></td>
			</tr>
		</table>
	</form>

<?php

}
	
if ($_POST['frmaction'] == "insert") {

?>

	<p class="pageheader">Add user</p>

<?php

	# Check for empty values
	$emptyItem = 0;
	foreach ($_POST as $key => $value) {
		if (empty($value)) {
			$emptyItem = 1;
		}
	}
	
	if ($emptyItem == 1) {

?>

		<div class="warning">One or more fields have been left empty</div>

<?php

	} else {

		$stmt = $db->prepare("
							INSERT INTO ${DB_TABLE_PREFIX}wispusers 

								(		
									Username, 
									Password, 
									FirstName, 
									LastName, 
									Location, 
									Email,
									Phone, 
									IPAddress, 
									PoolName, 
									GroupName, 
									AddressList, 
								)

							VALUES 

								(?,?,?,?,?,?,?,?,?,?,?)
							");

		$res = $stmt->execute(array(
				$_POST['user_name'],
				$_POST['user_password'],
				$_POST['user_first_name'],
				$_POST['user_last_name'],
				$_POST['user_location'],
				$_POST['user_email'],
				$_POST['user_phone'],
				$_POST['user_ip_address'],
				$_POST['pool_name'],
				$_POST['group_name'],
				$_POST['address_list'],
				));

		# Was it successful?
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
}


printFooter();

# vim: ts=4
?>
