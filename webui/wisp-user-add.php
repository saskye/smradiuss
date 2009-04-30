<?php
# WiSP User Add
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
));


if (!isset($_POST['frmaction'])) {

?>

	<p class="pageheader">Add WiSP User</p>

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
				<td class="entrytitle">Group</td>
				<td>
				<select name="user_group">
						<option selected="selected" value="NULL">No group</option>
<?php
							$sql = "
								SELECT
									ID, Name
								FROM
									${DB_TABLE_PREFIX}groups
								ORDER BY
									Name
								DESC
							";

							$res = $db->query($sql);

							# If there are any result rows, list items
							if ($res->rowCount() > 0) {
								while ($row = $res->fetchObject()) {
?>
									<option value="<?php echo $row->id; ?>"><?php echo $row->name; ?></option>
<?php
								}
							}
?>
					</select>
				</td>
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
				<td>
				<select name="user_location">
						<option selected="selected" value="NULL">No location</option>
<?php
							$sql = "
								SELECT
									ID, Name
								FROM
									${DB_TABLE_PREFIX}wisp_locations
								ORDER BY
									Name
								DESC
							";

							$res = $db->query($sql);

							# If there are any result rows, list items
							if ($res->rowCount() > 0) {

								while ($row = $res->fetchObject()) {
?>
									<option value="<?php echo $row->id; ?>"><?php echo $row->name; ?></option>
<?php
								}
							}
?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="entrytitle">Email Address</td>
				<td><input type="text" name="user_email" /></td>
			</tr>
			<tr>
				<td class="entrytitle">MAC Address</td>
				<td><input type="text" name="user_mac_address" /></td>
			</tr>
			<tr>
				<td class="entrytitle">IP Address</td>
				<td><input type="text" name="user_ip_address" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Data Usage Limit (MB)</td>
				<td><input type="text" name="user_data_limit" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Time Limit (Min)</td>
				<td><input type="text" name="user_time_limit" /></td>
			</tr>
			<tr>
				<td class="textcenter" colspan="2"><input type="submit" value="Submit" /></td>
			</tr>
		</table>
	</form>

<?php

}
	
if (isset($_POST['frmaction']) && $_POST['frmaction'] == "insert") {

?>

	<p class="pageheader">Add user</p>

<?php

	$db->beginTransaction();

	# Insert into users table
	$stmt = $db->prepare("INSERT INTO ${DB_TABLE_PREFIX}users (Username) VALUES (?)");
	$res = $stmt->execute(array($_POST['user_name']));


	if ($res !== FALSE) {
?>
		<div class="notice">User added</div>
<?php

		# Grab inserted ID
		$userID = $db->lastInsertId();

		# FIXME Check for empty values for certain fields
		# Check if userID is integer and > 0
		if (!isset($userID) || $userID < 1) {
			$db->rollback();
?>
			<div class="warning">Failed to get user ID</div>
<?php			
			$res = FALSE;
		}


	} else {
?>
			<div class="warning">Failed to add user</div>
			<div class="warning"><?php print_r($stmt->errorInfo()) ?></div>
<?php
	}


	if ($res !== FALSE) {
		# Insert MAC Address
		$stmt = $db->prepare("
			INSERT INTO 
				${DB_TABLE_PREFIX}user_attributes (UserID,Name,Operator,Value) 
			VALUES 
				($userID,'Calling-Station-Id','||==',?)
		");

		$res = $stmt->execute(array($_POST['user_mac_address']));

		if ($res !== FALSE) {
?>
			<div class="notice">Added MAC address</div>
<?php
		} else {
?>
			<div class="warning">Failed to add MAC address</div>
			<div class="warning"><?php print_r($stmt->errorInfo()) ?></div>
<?php
		}
	}


	if ($res !== FALSE) {
		if ($_POST['user_group'] !== "NULL") {
			# Insert user group
			$stmt = $db->prepare("
				INSERT INTO 
					${DB_TABLE_PREFIX}users_to_groups (UserID,GroupID) 
				VALUES 
					($userID,?)
			");

			$res = $stmt->execute(array($_POST['user_group']));

			if ($res !== FALSE) {
?>
				<div class="notice">Added user to group</div>
<?php
			} else {
?>
				<div class="warning">Failed to add user to group</div>
				<div class="warning"><?php print_r($stmt->errorInfo()) ?></div>
<?php
			}
		}
	}


	if ($res !== FALSE) {
		# Insert IP Address
		$stmt = $db->prepare("
			INSERT INTO 
				${DB_TABLE_PREFIX}user_attributes (UserID,Name,Operator,Value) 
			VALUES 
				($userID,'Framed-IP-Address','+=',?)
		");

		$res = $stmt->execute(array($_POST['user_ip_address']));
		if ($res !== FALSE) {
?>
			<div class="notice">IP address added</div>
<?php
		} else {
?>
			<div class="warning">Failed to add IP address</div>
			<div class="warning"><?php print_r($stmt->errorInfo()) ?></div>
<?php
		}
	}

	if ($res !== FALSE) {
		# Insert data limit
		$stmt = $db->prepare("
			INSERT INTO 
				${DB_TABLE_PREFIX}user_attributes (UserID,Name,Operator,Value) 
			VALUES 
				($userID,'SMRadius-Capping-Traffic-Limit','==',?)
		");

		$res = $stmt->execute(array($_POST['user_data_limit']));
		if ($res !== FALSE) {
?>
			<div class="notice">Traffic limit added</div>
<?php
		} else {
?>
			<div class="warning">Failed to add traffic limit</div>
			<div class="warning"><?php print_r($stmt->errorInfo()) ?></div>
<?php
		}
	}

	if ($res !== FALSE) {
		# Insert time limit
		$stmt = $db->prepare("
			INSERT INTO 
				${DB_TABLE_PREFIX}user_attributes (UserID,Name,Operator,Value) 
			VALUES 
				($userID,'SMRadius-Capping-UpTime-Limit','==',?)
		");

		$res = $stmt->execute(array($_POST['user_time_limit']));
		if  ($res !== FALSE) {
?>
			<div class="notice">Uptime limit added</div>
<?php
		} else {
?>
			<div class="warning">Failed to add uptime limit</div>
			<div class="warning"><?php print_r($stmt->errorInfo()) ?></div>
<?php
		}
	}

	if ($res !== FALSE) {
		# Insert password 
		$stmt = $db->prepare("
			INSERT INTO 
				${DB_TABLE_PREFIX}user_attributes (UserID,Name,Operator,Value) 
			VALUES 
				($userID,'User-Password','==',?)
		");

		$res = $stmt->execute(array($_POST['user_password']));
		if ($res !== FALSE) {
?>
			<div class="notice">User password added</div>
<?php
		} else {
?>
			<div class="warning">Failed to add up user password</div>
			<div class="warning"><?php print_r($stmt->errorInfo()) ?></div>
<?php
		}
	}


	if ($res !== FALSE) {
		# Insert user data
		$stmt = $db->prepare("
			INSERT INTO 
				${DB_TABLE_PREFIX}wisp_userdata (UserID, FirstName, LastName, Email, Phone, LocationID) 
			VALUES 
				(?,?,?,?,?,?)
		");

		$res = $stmt->execute(array(
			$userID,
			$_POST['user_first_name'],
			$_POST['user_last_name'],
			$_POST['user_email'],
			$_POST['user_phone'],
			$_POST['user_location']
		));
		if ($res !== FALSE) {
?>
			<div class="notice">WiSP user data added</div>
<?php
		} else {
?>
			<div class="warning">Failed to add WiSP user data</div>
			<div class="warning"><?php print_r($stmt->errorInfo()) ?></div>
<?php
		}
	}


	if ($res !== FALSE) {
		$db->commit();
	} else {
		$db->rollback();
	}
}


printFooter();

# vim: ts=4
?>
