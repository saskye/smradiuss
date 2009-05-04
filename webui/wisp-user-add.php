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
	<script language="javascript">

		function changeAttribute(rowid) {
			var select_box = document.getElementById("s"+rowid);
			var input_area = document.getElementById("i"+rowid);
		
			// Check what we going to display	
			switch (select_box.value) {

				case "group":
					input_area.innerHTML = ' \
						<select name="user_item['+rowid+'][value]"> \
							<option value="1">test group 1</option> \
							<option value="2">test group 2</option> \
							<option value="3">test group 3</option> \
						</select> \
					';
					break;

				case "location":
					input_area.innerHTML = ' \
						<select name="user_item['+rowid+'][value]"> \
							<option value="1">test location 1</option> \
							<option value="2">test location 2</option> \
							<option value="3">test location 3</option> \
						</select> \
					';
					break;

				case "first_name":
					input_area.innerHTML = ' \
						<input type="text" name="user_item['+rowid+'][value]" /> \
					';
					break;

				case "last_name":
					input_area.innerHTML = ' \
						<input type="text" name="user_item['+rowid+'][value]" /> \
					';
					break;

				case "phone":
					input_area.innerHTML = ' \
						<input type="text" name="user_item['+rowid+'][value]" /> \
					';
					break;

				case "email_address":
					input_area.innerHTML = ' \
						<input type="text" name="user_item['+rowid+'][value]" /> \
					';
					break;

				case "mac_address":
					input_area.innerHTML = ' \
						<input type="text" name="user_item['+rowid+'][value]" /> \
					';
					break;

				case "ip_address":
					input_area.innerHTML = ' \
						<input type="text" name="user_item['+rowid+'][value]" /> \
					';
					break;

				case "data_limit":
					input_area.innerHTML = ' \
						<input type="text" name="user_item['+rowid+'][value]" size="5" /> \
						<select name="user_item['+rowid+'][modifier]"> \
							<option value="1">Mbyte</option> \
							<option value="2">Gbyte</option> \
						</select> \
					';
					break;

				case "uptime_limit":
					input_area.innerHTML = ' \
						<input type="text" name="user_item['+rowid+'][value]" size="5" /> \
						<select name="user_item['+rowid+'][modifier]"> \
							<option value="1">Seconds</option> \
							<option value="2">Minutes</option> \
							<option value="3">Hours</option> \
							<option value="4">Days</option> \
							<option value="5">Weeks</option> \
							<option value="6">Months</option> \
							<option value="6">Years</option> \
						</select> \
					';
					break;
			}

		}

		function addAttributeRow(area) {
			// Prevent older browsers from getting any further
			if(!document.getElementById) return;

			// Grab the dynamic table
			var dynamic_table = document.getElementById(area);

			// Create the row
			var new_row_num = dynamic_table.rows.length - 2;
			var e_tr = dynamic_table.insertRow( new_row_num );

			var rowid = "attr" + new_row_num + rand(9999999);
			e_tr.id = rowid;

			// Create the cells
			var e_td1 = e_tr.insertCell(0);
			var e_td2 = e_tr.insertCell(1);
			var e_td3 = e_tr.insertCell(2);

			e_td1.innerHTML = ' \
				<select id="s'+rowid+'" name="user_item['+rowid+'][select]" onchange=" \
							changeAttribute('+"'"+rowid+"'"+'); \
						"> \
					<option value="">--</option> \
					<option value="group">Group</option> \
					<option value="location">Location</option> \
					<option value="first_name">First Name</option> \
					<option value="last_name">Last Name</option> \
					<option value="phone">Phone</option> \
					<option value="email_address">Email Address</option> \
					<option value="mac_address">MAC Address</option> \
					<option value="ip_address">IP Address</option> \
					<option value="data_limit">Data Limit</option> \
					<option value="uptime_limit">Uptime Limit</option> \
				</select> \
			';

			e_td2.innerHTML = ' \
				<div id="i'+rowid+'"></div> \
			';

			e_td3.innerHTML = ' \
				<a onclick=" \
					var this_table = document.getElementById('+"'"+area+"'"+ '); \
					var this_row = document.getElementById('+"'"+rowid+"'"+ '); \
					this_table.deleteRow(this_row.rowIndex); \
				">remove</a> \
			';
		}
	</script>

	<p class="pageheader">Add WiSP User</p>

	<!-- Add user input fields -->
	<form method="post" action="wisp-user-add.php">
		<div>
			<input type="hidden" name="frmaction" value="insert" />
		</div>

		<table id="dynamic_table" class="entry">
			<tr>
				<td class="textcenter" colspan="2">Account Information</td>
			</tr>
			<tr>
				<td><div /><td/>
				<td><div /><td/>
				<td><div /><td/>
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
				<td class="textcenter" colspan="3">
					<input type="button" value="Add Attribute" onclick=" addAttributeRow('dynamic_table'); " />
				</td>
			</tr>

			<tr>
				<td class="textcenter" colspan="3"><input type="submit" value="Submit" /></td>
			</tr>
		</table>
	</form>

	<script language="javascript"> addAttributeRow('dynamic_table'); </script>
<?php

}
	
if (isset($_POST['frmaction']) && $_POST['frmaction'] == "insert") {
?>
	<p class="pageheader">Add user</p>
<?php


	print_r($_POST);

	return;


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

		$res = $stmt->execute(array($_POST['user_uptime_limit']));
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
}


printFooter();

# vim: ts=4
?>
