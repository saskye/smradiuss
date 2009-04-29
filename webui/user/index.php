<?php
# Main User Control Panel Page
# Copyright (c) 2007-2009, AllWorldIT
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


# pre takes care of authentication and creates soap object we need
include("include/pre.php");
# Page header
include("include/header.php");

# NB: We will only end up here if we authenticated!


# Display details
function displayDetails() { 
	global $db;
	global $DB_TABLE_PREFIX;

	$userName = $_SESSION['username'];

	# Get user's ID
	$sql = "
		SELECT
				ID
		FROM
				${DB_TABLE_PREFIX}users
		WHERE
				Username = '$userName'
		";

	$res = $db->query($sql);
	$row = $res->fetchObject();
	$userID = $row->id;

	# Get accounting data
	$currentMonth = date("Y-m");

	$sql = "
		SELECT
				AcctSessionTime,
				AcctInputOctets,
				AcctInputGigawords,
				AcctOutputOctets,
				AcctOutputGigawords
		FROM
				${DB_TABLE_PREFIX}accounting
		WHERE
				Username = '$userName'
		AND
				EventTimestamp >= '$currentMonth'
		ORDER BY
				EventTimestamp
		DESC
		";

	$res = $db->query($sql);

	$totalData = 0;
	$totalInputData = 0;
	$totalOutputData = 0;
	$totalSessionTime = 0;

	while ($row = $res->fetchObject()) {

		# Input
		$inputDataItem = 0;

		if (!isset($row->acctinputoctets) && $row->acctinputoctets > 0) {
			$inputDataItem += ($row->accinputoctets / 1024 / 1024);
		}
		if (!empty($row->acctinputgigawords) && $row->inputgigawords > 0) {
			$inputDataItem += ($row->acctinputgigawords * 4096);
		}

		$totalInputData += $inputDataItem;

		# Output
		$outputDataItem = 0;

		if (!empty($row->acctoutputoctets) && $row->acctoutputoctets > 0) {
			$outputDataItem += ($row->acctoutputoctets / 1024 / 1024);
		}
		if (!empty($row->acctoutputgigawords) && $row->acctoutputgigawords > 0) {
			$outputDataItem += ($row->acctoutputgigawords * 4096);
		}

		$totalOutputData += $outputDataItem;

		$totalData += $totalInputData + $totalOutputData;

		# Time calculation
		$sessionTimeItem = 0;
		if (!empty($row->acctsessiontime) && $row->acctsessiontime > 0) {
			$sessionTimeItem += ($row->acctsessiontime - ($row->acctsessiontime % 60)) / 60;
		}

		$totalSessionTime += $sessionTimeItem;

	}

	$sql = "
			SELECT
					Name, Value
			FROM
					${DB_TABLE_PREFIX}user_attributes
			WHERE
					UserID = '$userID'
			";

	$res = $db->query($sql);

	$userPhone = "Unavailable";
	$userEmail = "Unavailable";
	$userCap = "Unavailable";
	$dataCap = "Unavailable";
	$timeCap = "Unavailable";
	$userService = "Unavailable";

	while ($row = $res->fetchObject()) {
		if ($row->name == "SMRadius-Notify-Phone") {
			$userPhone = $row->value;
		}
		if ($row->name == "SMRadius-Notify-Email") {
			$userEmail = $row->value;
		}
		if ($row->name == "SMRadius-Capping-Traffic-Limit") {
			$dataCap = $row->value;
		}
		if ($row->name == "SMRadius-Capping-UpTime-Limit") {
			$timeCap = $row->value;
		}
		if ($row->name == "SMRadius-User-Service") {
			$userService = $row->value;
		}
	}

	$isDialup = 0;

?>

	<table class="blockcenter">
		<tr>
			<td colspan="2" class="section">Account Information</td>
		</tr>
		<tr>
			<td class="title">Username</td>
			<td class="value"><?php echo $userName; ?></td>
		</tr>
		<tr>
			<td class="title">Service</td>
			<td class="value"><?php echo $userService; ?></td>
		</tr>

<?php

		# Only display cap for DSL users
		if (!$isDialup) {

?>

			<tr>
				<td colspan="2" class="section">Usage Info</td>
			</tr>
			<tr>
				<td class="title">Bandwidth Cap</td>
				<td class="title">Used This Month</td>
			</tr>
			<tr>
				<td class="value"><?php echo $dataCap; ?> MB</td>
				<td class="value"><?php printf('%.2f', $totalData); ?> MB</td>
			</tr>
			<tr>
				<td class="title">Time Cap</td>
				<td class="title">Used This Month</td>
			</tr>
			<tr>
				<td class="value"><?php echo $timeCap; ?> Min</td>
				<td class="value"><?php echo $totalSessionTime; ?> Min</td>
			</tr>
			<tr>
				<td colspan="2" class="section">Notifications</td>
			</tr>
			<form method="post">
			<tr>
				<td class="title">Email Address</td>
				<td class="value">
					<input type="text" name="notifyMethodEmail"><?php echo $userEmail; ?></input>
				</td>
			</tr>
			<tr>
				<td class="title">Cell Number</td>
				<td class="value">
					<input type="text" name="notifyMethodCell"><?php echo $userPhone; ?></input>
				</td>
			</tr>
			</form>

<?php

		}

?>

		<tr>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td colspan="2" align="center">
				<a href="logs.php">Usage Logs</a>
			</td>
		</tr>
	</table>

	<br><br>

	<font size="-1">
		Note:
		<li>Please contact your ISP if you have any problem using this interface.</li>
	</font>

<?php

}

# If this is a post and we're updating
if (isset($_POST['notifyUpdate']) && $_POST['notifyUpdate'] == "update") {

	$userName = $_SESSION['username'];

	# Get user's ID
	$sql = "
		SELECT
				ID
		FROM
				${DB_TABLE_PREFIX}users
		WHERE
				Username = '$userName'
		";

	$res = $db->query($sql);
	$row = $res->fetchObject();
	$userID = $row->id;

	$sql = "
			SELECT
					Name, Value
			FROM
					${DB_TABLE_PREFIX}user_attributes
			WHERE
					UserID = '$userID'
			";

	$res = $db->query($sql);

	$userPhone = "Unavailable";
	$userEmail = "Unavailable";

	while ($row = $res->fetchObject()) {
		if ($row->name == "SMRadius-Notify-Phone") {
			$userPhone = $row->value;
		}
		if ($row->name == "SMRadius-Notify-Email") {
			$userEmail = $row->value;
		}
	}

	# If we want to update email address
	if (isset($_POST['notifyMethodEmail']) && !empty($_POST['notifyMethodEmail'])) {

		$db->beginTransaction();

		# Unavailble if no email address is set yet
		if ($userEmail == "Unavailable") {

			# Prepare to insert email address for the first time
			$emailStatement = $db->prepare("INSERT INTO 
														${DB_TABLE_PREFIX}user_attributes (UserID,Name,Operator,Value)
											VALUES 
														('$userID','SMRadius-Notify-Email','=*',?)
											");

			$emailResult = $emailStatement->execute(array($_POST['notifyMethodEmail'],));

			# If successful, commit
			if ($emailResult) {
				$db->commit();
				echo "<center>Email address updated</center>";
			# Else, rollback changes and give error
			} else {
				$db->rollback();
				echo "<center>Error updating email address, please contact your ISP.</center>";
			}

		} else {
			# Prepare to update existing email address
			$emailStatement = $db->prepare("UPDATE
													${DB_TABLE_PREFIX}user_attributes
											SET
													Value = ? 
											WHERE
													Name = 'SMRadius-Notify-Email'
											AND
													UserID = '$userID'
											");

			$emailResult = $emailStatement->execute(array($_POST['notifyMethodEmail'],));

			# If successful, commit
			if ($emailResult) {
				$db->commit();
				echo "<center>Email address updated</center>";
			# Else, rollback changes and give error
			} else {
				$db->rollback();
				echo "<center>Error updating email address, please contact your ISP.</center>";
			}
		}
	}

	# If we want to update phone number
	if (isset($_POST['notifyMethodCell']) && !empty($_POST['notifyMethodCell'])) {

		$db->beginTransaction();

		# Unavailable if there is none found for this user
		if ($userPhone == "Unavailable") {
			# Prepare to insert first number
			$phoneStatement = $db->prepare("INSERT INTO 
														${DB_TABLE_PREFIX}user_attributes (UserID,Name,Operator,Value)
											VALUES 
														('$userID','SMRadius-Notify-Phone','=*',?)
											");

			$phoneResult = $phoneStatement->execute(array($_POST['notifyMethodCell'],));

			# If successful, commit
			if ($phoneResult) {
				$db->commit();
				echo "<center>Mobile phone number updated</center>";
			# Else, rollback changes and give error
			} else {
				$db->rollback();
				echo "<center>Error updating mobile phone number, please contact your ISP.</center>";
			}

		} else {
			# Prepare to update existing number 
			$phoneStatement = $db->prepare("UPDATE
													${DB_TABLE_PREFIX}user_attributes
											SET
													Value = ? 
											WHERE
													Name = 'SMRadius-Notify-Phone'
											AND
													UserID = '$userID'
											");

			$phoneResult = $phoneStatement->execute(array($_POST['notifyMethodPhone'],));

			# If successful, commit
			if ($emailResult) {
				$db->commit();
				echo "<center>Mobile phone number updated</center>";
			# Else, rollback changes and give error
			} else {
				$db->rollback();
				echo "<center>Error updating mobile phone number, please contact your ISP.</center>";
			}
		}
	}
}

displayDetails();

# Footer
include("include/footer.php");

# vim: ts=4
?>
