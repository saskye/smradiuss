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
	#FIXME
	# Perform checks on input
	if (!empty($_POST['num_users']) && !empty($_POST['session_timeout']) && !empty($_POST['data_limit']) && !empty($_POST['time_limit'])) {
		$db->beginTransaction();

		$numberOfUsers = (int)$_POST['num_users'];
		$sessionTimeout = (int)$_POST['session_timeout'];
		$dataLimit = (int)$_POST['data_limit'];
		$timeLimit = (int)$_POST['time_limit'];
		$loginNamePrefix = $_POST['login_prefix'];

		for ($counter = 0; $counter <= $numberOfUsers; $counter += 1) {

			# Check if user already exists
			$checkUsernameDuplicates = 0;

			do {
				# Generate random username
				$randomString = chr(rand(97,122)).
								chr(rand(97,122)).
								chr(rand(97,122)).
								chr(rand(97,122)).
								chr(rand(97,122)).
								chr(rand(97,122)).
								chr(rand(97,122)).
								chr(rand(97,122));

				# If there is no login name prefix
				if (empty($loginNamePrefix)) {
					$userName = $randomString;

					$lookForUser = $db->query("SELECT ID FROM ${DB_TABLE_PREFIX}users WHERE Username LIKE '%$userName%'");

					# If the user was found
					if ($lookForUser->rowCount() > 0) {
						$checkUsernameDuplicates = 1;
					} else {
						$checkUsernameDuplicates = 0;
					}

				# If there is a login name prefix
				} else {
					$userName = $loginNamePrefix."_".$randomString;

					$lookForUser = $db->query("SELECT ID FROM ${DB_TABLE_PREFIX}users WHERE Username LIKE '%$userName%'");

					# If the user was found
					if ($lookForUser->rowCount() > 0) {
						$checkUsernameDuplicates = 1;
					} else {
						$checkUsernameDuplicates = 0;
					}
				}
			} while ($checkUsernameDuplicates > 0);

			#Insert user into users table
			$userInsert = $db->prepare("INSERT INTO
													${DB_TABLE_PREFIX}users (Username)
										VALUES
													(?)
										");
			$userInsertExec = $userInsert->execute(array($userName));

			$failed = 0;
			# After a user add is successful, continue with inserting the other data
			if ($userInsertExec) {

				# Get user ID to insert into other tables
				$getUserID = $db->query("SELECT ID FROM ${DB_TABLE_PREFIX}users WHERE Username = '$userName'");
				$resultRow = $getUserID->fetchObject();
				$userID = $resultRow->id;

				# Inset UserID into userdata table
				$userDataStatement = $db->prepare("	INSERT INTO
																${DB_TABLE_PREFIX}userdata (UserID)
													VALUES
																(?)
													");

				$userDataResult = $userDataStatement->execute(array($userID));

				# Generate a password
				$userPassword = chr(rand(97,122)).
								chr(rand(97,122)).
								chr(rand(97,122)).
								chr(rand(97,122)).
								chr(rand(97,122)).
								chr(rand(97,122)).
								chr(rand(97,122)).
								chr(rand(97,122));

				# Insert password into user_attributes table
				$userPasswordStatement = $db->prepare("	INSERT INTO
																	${DB_TABLE_PREFIX}user_attributes (UserID,Name,Operator,Value)
														VALUES
																	($userID,'User-Password','==',?)
														");

				$userPasswordResult = $userPasswordStatement->execute(array($userPassword));
				
				# Insert data limit into user_attributes table
				$userDataLimitStatement = $db->prepare("INSERT INTO
																	${DB_TABLE_PREFIX}user_attributes (UserID,Name,Operator,Value)
														VALUES
																	($userID,'SMRadius-Capping-Traffic-Limit',':=',?)
														");

				$userDataLimitResult = $userDataLimitStatement->execute(array($dataLimit,));
				
				# Insert time limit into user_attributes table
				$userTimeStatement = $db->prepare("	INSERT INTO
																${DB_TABLE_PREFIX}user_attributes (UserID,Name,Operator,Value)
													VALUES
																($userID,'SMRadius-Capping-Time-Limit',':=',?)
													");

				$userTimeResult = $userTimeStatement->execute(array($timeLimit,));

				# Insert timeout into user_attributes table
				$userTimeOutStatement = $db->prepare("	INSERT INTO
																	${DB_TABLE_PREFIX}user_attributes (UserID,Name,Operator,Value)
														VALUES
																	($userID,'Session-Timeout','+=',?)
													");

				$userTimeOutResult = $userTimeOutStatement->execute(array($sessionTimeout,));

				if ($userTimeOutResult && $userTimeResult && $userDataResult && $userPasswordResult && $userDataLimitResult) {
					$failed = 0;
				} else {
					$failed = 1;
				}
			# If one was not successful, rollback
			} else {
				print_r($db->errorInfo());
				$db->rollback;
				$failed = 1;
				break;
			}
		}
		if ($failed == 0) {
			$db->commit();

?>

				<div class="notice">Users added</div>

<?php

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
