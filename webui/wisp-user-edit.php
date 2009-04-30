<?php
# WiSP user edit
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


# Display edit screen
if (isset($_POST['frmaction']) && $_POST['frmaction'] == "edit") {
	# Check a user was selected
	if (isset($_POST['user_id'])) {

		$userID = $_POST['user_id'];
		$sql = "SELECT 
					wisp_userdata.FirstName, 
					wisp_userdata.LastName, 
					wisp_userdata.Email, 
					wisp_userdata.Phone,
					wisp_userdata.LocationID,
					wisp_locations.ID,
					wisp_locations.Name
				FROM 
					wisp_userdata, wisp_locations 
				WHERE 
					wisp_userdata.UserID = ".$db->quote($userID)."
				AND
					wisp_userdata.LocationID = 'wisp_locations.ID'
				";

		$userDataResult = $db->query($sql); 
		print_r("NUMBER OF ROWS: ".$userDataResult->rowCount());
		$userDataRow = $userDataResult->fetchObject();

		$sql = "SELECT
					Value
				FROM
					user_attributes
				WHERE
					UserID = ".$db->quote($userID)."
				AND
					Name = 'Framed-IP-Address'
				";

		$framedIPResult = $db->query($sql);
		$framedIPRow = $framedIPResult->fetchObject();

		$sql = "SELECT
					Value
				FROM
					user_attributes
				WHERE
					UserID = ".$db->quote($userID)."
				AND
					Name = 'Calling-Station-Id'
				";

		$callingStationResult = $db->query($sql);
		$callingStationRow = $callingStationResult->fetchObject();


		$sql = "SELECT
					Value
				FROM
					user_attributes
				WHERE
					UserID = ".$db->quote($userID)."
				AND
					Name = 'User-Password'
				";

		$userPasswordResult = $db->query($sql);
		$userPasswordRow = $userPasswordResult->fetchObject();


		$sql = "SELECT
					Value
				FROM
					user_attributes
				WHERE
					UserID = ".$db->quote($userID)."
				AND
					Name = 'SMRadius-Capping-Traffic-Limit'
				";

		$dataLimitResult = $db->query($sql);
		$dataLimitRow = $dataLimitResult->fetchObject();
		$dataLimit = $dataLimitRow->value;

		$sql = "SELECT
					Value
				FROM
					user_attributes
				WHERE
					UserID = ".$db->quote($userID)."
				AND
					Name = 'SMRadius-Capping-UpTime-Limit'
				";

		$timeLimitResult = $db->query($sql);
		$timeLimitRow = $timeLimitResult->fetchObject();
		$timeLimit = $timeLimitRow->value;

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
					<td class="oldval texttop"><?php echo $userPasswordRow->value; ?></td>
					<td><input type="password" name="new_password" /></td>
				</tr>
				<tr>
					<td class="entrytitle texttop">Data Limit</td>
					<td class="oldval texttop"><?php echo $dataLimit; ?> MB</td>
					<td><input type="text" name="new_data_limit" /></td>
				</tr>
				<tr>
					<td class="entrytitle texttop">Time Limit</td>
					<td class="oldval texttop"><?php echo $timeLimit; ?> Min</td>
					<td><input type="text" name="new_time_limit" /></td>
				</tr>
				<tr>
					<td class="entrytitle texttop">MAC Address</td>
					<td class="oldval texttop"><?php echo $callingStationRow->value; ?></td>
					<td><input type="text" name="new_mac_address" /></td>
				</tr>
				<tr>
					<td class="entrytitle texttop">IP Address</td>
					<td class="oldval texttop"><?php echo $framedIPRow->value; ?></td>
					<td><input type="text" name="new_ip_address" /></td>
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
					<td class="oldval texttop"><?php echo $userDataRow->firstname; ?></td>
					<td><input type="text" name="new_firstname" /></td>
				</tr>
				<tr>
					<td class="entrytitle texttop">Last Name</td>
					<td class="oldval texttop"><?php echo $userDataRow->lastname; ?></td>
					<td><input type="text" name="new_lastname" /></td>
				</tr>
				<tr>
					<td class="entrytitle texttop">Location</td>
					<td class="oldval texttop"><?php echo $userDataRow->name; ?></td>
					<td>
						<select name="new_location">
							<option selected="selected" value="<?php echo $userDataRow->id; ?>">Unchanged</option>
<?php
								$sql = "SELECT
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
					<td class="entrytitle texttop">Email</td>
					<td class="oldval texttop"><?php echo $userDataRow->email; ?></td>
					<td><input type="text" name="new_email" /></td>
				</tr>
				<tr>
					<td class="entrytitle texttop">Phone</td>
					<td class="oldval texttop"><?php echo $userDataRow->phone; ?></td>
					<td><input type="text" name="new_phone" /></td>
				</tr>
			</table>

			<p />

			<div class="textcenter">
				<input type="submit" />
			</div>
		</form>

<?php

	$userDataResult->closeCursor();
	$framedIPResult->closeCursor();
	$dataLimitResult->closeCursor();
	$timeLimitResult->closeCursor();
	$callingStationResult->closeCursor();

	} else {

?>

		<div class="warning">No user selected</div>

<?php

	}


# SQL Updates
} elseif (isset($_POST['frmaction']) && $_POST['frmaction'] == "edit2") {

?>

	<p class="pageheader">User Edit Results</p>

<?php

	# Check a user was selected
	if (isset($_POST['user_id'])) {

		$userDataUpdates = array();

		if (!empty($_POST['new_firstname'])) {
			array_push($userDataUpdates,"FirstName = ".$db->quote($_POST['new_firstname']));
		}
		if (!empty($_POST['new_lastname'])) {
			array_push($userDataUpdates,"LastName = ".$db->quote($_POST['new_lastname']));
		}
		if (!empty($_POST['new_location'])) {
			array_push($userDataUpdates,"Location = ".$db->quote($_POST['new_location']));
		}
		if (!empty($_POST['new_email'])) {
			array_push($userDataUpdates,"Email = ".$db->quote($_POST['new_email']));
		}
		if (!empty($_POST['new_phone'])) {
			array_push($userDataUpdates,"Phone = ".$db->quote($_POST['new_phone']));
		}

		$numUserAttributesUpdates = 0;
		if (!empty($_POST['new_data_limit'])) {
			$dataLimitResult = $db->exec("	UPDATE 
												user_attributes 
											SET 
												Value = ".$db->quote($_POST['new_data_limit'])." 
											WHERE 
												UserID = ".$db->quote($_POST['user_id'])."
											AND
												Name = 'SMRadius-Capping-Traffic-Limit'
										");
			$numUserAttributesUpdates++;
		}
		if (!empty($_POST['new_time_limit'])) {
			$timeLimitResult = $db->exec("	UPDATE 
												user_attributes 
											SET 
												Value = ".$db->quote($_POST['new_time_limit'])." 
											WHERE 
												UserID = ".$db->quote($_POST['user_id'])."
											AND
												Name = 'SMRadius-Capping-Traffic-Limit'
										");
			$numUserAttributesUpdates++;
		}
		if (!empty($_POST['new_password'])) {
			$setUserPasswordResult = $db->exec("	UPDATE 
														user_attributes 
													SET 
														Value = ".$db->quote($_POST['new_password'])." 
													WHERE 
														UserID = ".$db->quote($_POST['user_id'])."
													AND
														Name = 'User-Password'
													");
			$numUserAttributesUpdates++;
		}
		if (!empty($_POST['new_ip_address'])) {
			$ipAddressResult = $db->exec("	UPDATE 
												user_attributes 
											SET 
												Value = ".$db->quote($_POST['new_ip_address'])." 
											WHERE 
												UserID = ".$db->quote($_POST['user_id'])."
											AND
												Name = 'Framed-IP-Address'
										");
			$numUserAttributesUpdates++;
		}
		if (!empty($_POST['new_mac_address'])) {
			$macAddressResult = $db->exec("	UPDATE 
												user_attributes 
											SET 
												Value = ".$db->quote($_POST['new_mac_address'])." 
											WHERE 
												UserID = ".$db->quote($_POST['user_id'])."
											AND
												Name = 'Calling-Station-Id'
										");
			$numUserAttributesUpdates++;
		}
		if (!empty($_POST['new_location'])) {
			$locationResult = $db->exec("	UPDATE
												wisp_userdata
											SET
												LocationID = ".$db->quote($_POST['new_location'])."
											WHERE
												UserID = ".$db->quote($_POST['user_id'])."
										");
		}
												

		# Check if we have wisp_userdata table updates
		if (sizeof($userDataUpdates) > 0) {
			$userDataUpdateString = implode(', ',$userDataUpdates);

			$res = $db->exec("UPDATE wisp_userdata SET $userDataUpdateString WHERE UserID = ".$db->quote($_POST['user_id']));
			if ($res) {

?>

				<div class="notice">User private data updated</div>

<?php

			} else {

?>

				<div class="warning">Error updating user private data</div>
				<div class="warning"><?php print_r($db->errorInfo()) ?></div>

<?php

			}

		# Warn
		} else {

?>

			<div class="warning">User private data not updated</div>

<?php

		}
		if ($numUserAttributesUpdates > 0) {

?>
			<div class="notice">User account data updated</div>

<?php

		} else {

?>

			<div class="notice">User account data not updated</div>

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

