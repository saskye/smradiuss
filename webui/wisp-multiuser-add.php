<?php
# WiSP multi-user add
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
	<p class="pageheader">Add WiSP Users</p>

	<!-- Add user input fields -->
	<form method="post" action="wisp-multiuser-add.php">
		<div>
			<input type="hidden" name="frmaction" value="insert" />
		</div>
		<table class="entry">
			<tr>
				<td class="textcenter" colspan="2">Add multiple users</td>
			</tr>
			<tr>
				<td><div></div><td>
			</tr>
			<tr>
				<td class="entrytitle">Number of users</td>
				<td><input type="text" name="num_users" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Login Prefix</td>
				<td><input type="text" name="login_prefix" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Uptime Limit</td>
				<td><input type="text" name="session_timeout" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Data Limit</td>
				<td><input type="text" name="data_limit" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Time Limit</td>
				<td><input type="text" name="time_limit" /></td>
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
	<p class="pageheader">Add WiSP Users</p>
<?php
	# Perform checks on input
	if (!empty($_POST['num_users']) && !empty($_POST['session_timeout']) && !empty($_POST['data_limit']) 
			&& !empty($_POST['time_limit'])) {

		$numberOfUsers = (int)$_POST['num_users'];
		$sessionTimeout = (int)$_POST['session_timeout'];
		$dataLimit = (int)$_POST['data_limit'];
		$timeLimit = (int)$_POST['time_limit'];
		$loginNamePrefix = $_POST['login_prefix'];

		$userList = array();

		# FIXME
		for ($counter = 0; $counter <= $numberOfUsers; $counter++) {

			# Loop and try add user, maybe its duplicate?
			do {
				# Generate random username
				$randomString = "";
				for ($i = 0; $i < 8; $i++) { $randomString .= chr(rand(97,122)); }

				# If there is a login name prefix
				if (isset($loginNamePrefix) && $loginNamePrefix != "") {
					$userName = $loginNamePrefix."_".$randomString;
				# If there is no login name prefix
				} else {
					$userName = $randomString;
				}

				$stmt = $db->query("
					SELECT 
						COUNT(*) AS Duplicate
					FROM 
						${DB_TABLE_PREFIX}users 
					WHERE 
						Username = ".$db->quote($userName)."
				");

				$row = $stmt->fetchObject();

			} while ($row->duplicate != 0);

			array_push($userList,$userName);
		}

		$db->beginTransaction();

		foreach ($userList as $userName) {

			#Insert user into users table
			$stmt = $db->prepare("
				INSERT INTO
					${DB_TABLE_PREFIX}users (Username)
				VALUES
					(?)
			");
			$res = $stmt->execute(array($userName));

			# After a user add is successful, continue with inserting the other data
			if ($res !== FALSE) {

				# Get user ID to insert into other tables
				$userID = $db->lastInsertId();

				if (isset($userID)) {
					# Inset UserID into wisp_userdata table
					$stmt = $db->prepare("
									INSERT INTO
										${DB_TABLE_PREFIX}wisp_userdata (UserID)
									VALUES
										(?)
					");

					$res = $stmt->execute(array($userID));
					if ($res !== FALSE) {
?>
						<div class="notice">Userdata added</div>
<?php
					} else {
						$res = 0;
?>
						<div class="warning">Failed to create user</div>
<?php
					}
				}


				if ($res !== FALSE) {
					# Generate password
					$userPassword = "";
					for ($passCount = 0; $passCount < 8; $passCount++) {
						$userPassword .= chr(rand(97,122));
					}

					# Insert password into user_attributes table
					$stmt = $db->prepare("
						INSERT INTO
							${DB_TABLE_PREFIX}user_attributes (UserID,Name,Operator,Value)
						VALUES
							($userID,'User-Password','==',?)
					");

					$res = $stmt->execute(array($userPassword));
					if ($res !== FALSE) {
?>
						<div class="notice">User password added</div>
<?php
					} else {
?>
						<div class="warning">Failed to add user password</div>
						<div class="warning"><?php print_r($stmt->errorInfo()); ?></div>
<?php
					}
				}
				

				if ($res !== FALSE) {
					# Insert data limit into user_attributes table
					$stmt = $db->prepare("
						INSERT INTO
							${DB_TABLE_PREFIX}user_attributes (UserID,Name,Operator,Value)
						VALUES
							($userID,'SMRadius-Capping-Traffic-Limit',':=',?)
					");

					$res = $stmt->execute(array($dataLimit));
					if ($res !== FALSE) {
?>
						<div class="notice">Data cap added</div>
<?php
					} else {
?>
						<div class="warning">Failed to add data cap</div>
						<div class="warning"><?php print_r($stmt->errorInfo()); ?></div>
<?php
					}
				}

				
				if ($res !== FALSE) {
					# Insert time limit into user_attributes table
					$stmt = $db->prepare("
						INSERT INTO
							${DB_TABLE_PREFIX}user_attributes (UserID,Name,Operator,Value)
						VALUES
							($userID,'SMRadius-Capping-UpTime-Limit',':=',?)
					");

					$res = $stmt->execute(array($timeLimit));
					if ($res !== FALSE) {
?>
						<div class="notice">Uptime limit added</div>
<?php
					} else {
?>
						<div class="warning">Failed to add uptime limit</div>
						<div class="warning"><?php print_r($stmt->errorInfo()); ?></div>
<?php
					}
				}


				if ($res !== FALSE) {
					# Insert timeout into user_attributes table
					$stmt = $db->prepare("
						INSERT INTO
							${DB_TABLE_PREFIX}user_attributes (UserID,Name,Operator,Value)
						VALUES
							($userID,'Session-Timeout','+=',?)
					");

					$res = $stmt->execute(array($sessionTimeout));
					if ($res !== FALSE) {
?>
						<div class="notice">User timeout added</div>
<?php
					} else {
?>
						<div class="warning">Failed to add user timeout</div>
						<div class="warning"><?php print_r($stmt->errorInfo()); ?></div>
<?php
					}
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
	} else {
?>
		<div class="warning">One or more fields have been left empty</div>
<?php
	}
}

printFooter();

# vim: ts=4
?>
