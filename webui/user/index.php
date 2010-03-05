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

	# Get user's ID
	$sql = "
		SELECT
			ID
		FROM
			${DB_TABLE_PREFIX}users
		WHERE
			Username = ".$db->quote($_SESSION['username'])."
	";
	$res = $db->query($sql);
	$row = $res->fetchObject();

	# Set user ID
	$userID = $row->id;

	# Get accounting data
	$currentMonth = date("Y-m");

	$sql = "
		SELECT
			SUM(AcctSessionTime) / 60 AS AcctSessionTime,
			SUM(AcctInputOctets) / 1024 / 1024 +
			SUM(AcctInputGigawords) * 4096 AS AcctInputTraffic,
			SUM(AcctOutputOctets) / 1024 / 1024 +
			SUM(AcctOutputGigawords) * 4096 AS AcctOutputTraffic
		FROM
			${DB_TABLE_PREFIX}accounting
		WHERE
			Username = ".$db->quote($_SESSION['username'])."
		AND
			PeriodKey = ".$db->quote($currentMonth)."
	";
	$res = $db->query($sql);

	# Set total traffic and uptime used
	$totalTraffic = 0;
	$totalUptime = 0;

	# Pull in row
	$row = $res->fetchObject();

	# Traffic in
	if (isset($row->acctinputtraffic) && $row->acctinputtraffic > 0) {
		$totalTraffic += $row->acctinputtraffic;
	}
	# Traffic out
	if (isset($row->acctoutputtraffic) && $row->acctoutputtraffic > 0) {
		$totalTraffic += $row->acctoutputtraffic;
	}

	# Uptime
	if (isset($row->acctsessiontime) && $row->acctsessiontime > 0) {
		$totalUptime += $row->acctsessiontime;
	}

	# Fetch user uptime and traffic cap
	$sql = "
		SELECT
			Name, Value
		FROM
			${DB_TABLE_PREFIX}user_attributes
		WHERE
			UserID = ".$db->quote($userID)."
	";
	$res = $db->query($sql);

	# Set uptime and traffic cap
	$trafficCap = "Prepaid";
	$uptimeCap = "Prepaid";
	while ($row = $res->fetchObject()) {
		if ($row->name == "SMRadius-Capping-Traffic-Limit") {
			$trafficCap = (int)$row->value;
		}
		if ($row->name == "SMRadius-Capping-Uptime-Limit") {
			$uptimeCap = (int)$row->value;
		}
	}

	# Fetch user uptime and traffic summary
	$sql = "
		SELECT
			${DB_TABLE_PREFIX}topups_summary.TopupID,
			${DB_TABLE_PREFIX}topups_summary.Balance,
			${DB_TABLE_PREFIX}topups.Type,
			${DB_TABLE_PREFIX}topups.Value
		FROM
			${DB_TABLE_PREFIX}topups_summary,
			${DB_TABLE_PREFIX}topups
		WHERE
			${DB_TABLE_PREFIX}topups_summary.TopupID = ${DB_TABLE_PREFIX}topups.ID
			AND ${DB_TABLE_PREFIX}topups.UserID = ".$db->quote($userID)."
			AND ${DB_TABLE_PREFIX}topups_summary.PeriodKey = ".$db->quote($currentMonth)."
			AND ${DB_TABLE_PREFIX}topups_summary.Depleted = 0
		ORDER BY
			${DB_TABLE_PREFIX}topups.Timestamp
	";
	$res = $db->query($sql);

	# Store summary topups
	$topups = array();
	while ($row = $res->fetchObject()) {
		$id = $row->topupid;
		$topups[$id] = array();
		$topups[$id]['Type'] = $row->type;
		$topups[$id]['Limit'] = $row->balance;
		$topups[$id]['OriginalLimit'] = $row->value;
	}

	# Fetch user uptime and traffic topups
	$thisMonthUnixTime = strtotime($currentMonth);
	$now = time();
	$sql = "
		SELECT
			ID, Value, Type
		FROM
			${DB_TABLE_PREFIX}topups
		WHERE
			${DB_TABLE_PREFIX}topups.UserID = ".$db->quote($userID)."
			AND ${DB_TABLE_PREFIX}topups.ValidFrom >= ".$db->quote($thisMonthUnixTime)."
			AND ${DB_TABLE_PREFIX}topups.ValidTo > ".$db->quote($now)."
			AND ${DB_TABLE_PREFIX}topups.Depleted = 0
		ORDER BY
			${DB_TABLE_PREFIX}topups.Timestamp
	";
	$res = $db->query($sql);

	# Store normal topups
	while ($row = $res->fetchObject()) {
		$id = $row->id;

		if (!isset($topups[$id])) {
			$topups[$id] = array();
			$topups[$id]['Type'] = $row->type;
			$topups[$id]['Limit'] = $row->value;
		}
	}

	# Set excess traffic usage
	$excessTraffic = 0;
	if (is_numeric($trafficCap) && $trafficCap > 0) {
		$excessTraffic += $totalTraffic - $trafficCap;
	} elseif (is_string($trafficCap)) {
		$excessTraffic += $totalTraffic;
	}

	# Set excess uptime usage
	$excessUptime = 0;
	if (is_numeric($uptimeCap) && $uptimeCap > 0) {
		$excessUptime += $totalUptime - $uptimeCap;
	} elseif (is_string($uptimeCap)) {
		$excessUptime += $totalUptime;
	}

	# Loop through traffic topups and check for current topup, total topups not being used
	if (is_string($trafficCap) || $trafficCap != 0) {
		$currentTrafficTopup = array();
		$topupTrafficRemaining = 0;
		$i = 0;
		# User is using traffic from topups
		if ($excessTraffic > 0) {
			foreach ($topups as $topupItem) {
				if ($topupItem['Type'] == 1) {
					if ($excessTraffic <= 0) {
						$topupTrafficRemaining += $topupItem['Limit'];
						next($topupItem);
					} elseif ($excessTraffic >= $topupItem['Limit']) {
						$excessTraffic -= $topupItem['Limit'];
					} else {
						if (isset($topupItem['OriginalLimit'])) {
							$currentTrafficTopup['Cap'] = $topupItem['OriginalLimit'];
						} else {
							$currentTrafficTopup['Cap'] = $topupItem['Limit'];
						}
						$currentTrafficTopup['Used'] = $excessTraffic;
						$excessTraffic -= $topupItem['Limit'];
					}
				}
			}
		# User has not used traffic topups yet
		} else {
			foreach ($topups as $topupItem) {
				if ($topupItem['Type'] == 1) {
					if ($i == 0) {
						if (isset($topupItem['OriginalLimit'])) {
							$currentTrafficTopup['Cap'] = $topupItem['OriginalLimit'];
						} else {
							$currentTrafficTopup['Cap'] = $topupItem['Limit'];
						}
						$i = 1;
							$currentTrafficTopup['Used'] = 0;
					} else {
						$topupTrafficRemaining += $topupItem['Limit'];
					}
				}
			}
		}
	}

	# Loop through uptime topups and check for current topup, total topups not being used
	if (is_string($uptimeCap) || $uptimeCap != 0) {
		$currentUptimeTopup = array();
		$topupUptimeRemaining = 0;
		$i = 0;
		# User is using uptime from topups
		if ($excessUptime > 0) {
			foreach ($topups as $topupItem) {
				if ($topupItem['Type'] == 2) {
					if ($excessUptime <= 0) {
						$topupUptimeRemaining += $topupItem['Limit'];
						next($topupItem);
					} elseif ($excessUptime >= $topupItem['Limit']) {
						$excessUptime -= $topupItem['Limit'];
					} else {
						if (isset($topupItem['OriginalLimit'])) {
							$currentUptimeTopup['Cap'] = $topupItem['OriginalLimit'];
						} else {
							$currentUptimeTopup['Cap'] = $topupItem['Limit'];
						}
						$currentUptimeTopup['Used'] = $excessUptime;
						$excessUptime -= $topupItem['Limit'];
					}
				}
			}
		# User has not used uptime topups yet
		} else {
			foreach ($topups as $topupItem) {
				if ($topupItem['Type'] == 2) {
					if ($i == 0) {
						if (isset($topupItem['OriginalLimit'])) {
							$currentUptimeTopup['Cap'] = $topupItem['OriginalLimit'];
						} else {
							$currentUptimeTopup['Cap'] = $topupItem['Limit'];
						}
						$i = 1;
							$currentUptimeTopup['Used'] = 0;
					} else {
						$topupUptimeRemaining += $topupItem['Limit'];
					}
				}
			}
		}
	}

/*
	# Fetch user phone and email info
	$sql = "
		SELECT
				Phone, Email
		FROM
				${DB_TABLE_PREFIX}wisp_userdata
		WHERE
				UserID = '$userID'
	";

	$res = $db->query($sql);

	$userPhone = "Not set";
	$userEmail = "Not set";
	if ($res->rowCount() > 0) {
		$row = $res->fetchObject();
		$userPhone = $row->phone;
		$userEmail = $row->email;
	}
*/

	# These two items need fixing
	$isDialup = 0;
	$userService = "Not set";

?>
	<table class="blockcenter">
		<tr>
			<td colspan="4" class="section">Account Information</td>
		</tr>
		<tr>
			<td colspan="2" class="title">Username</td>
			<td colspan="2" class="title">Service</td>
		</tr>
		<tr>
			<td colspan="2" class="value"><?php echo $_SESSION['username']; ?></td>
			<td colspan="2" class="value"><?php echo $userService; ?></td>
		</tr>
<?php
		# Only display cap for DSL users
		if (!$isDialup) {
?>
			<tr>
				<td colspan="4" class="section">Traffic Usage</td>
			</tr>
			<tr>
				<td class="title">Cap</td>
				<td class="title">Unused Topup</td>
				<td class="title">Current Topup</td>
				<td class="title">Used This Month</td>
			</tr>
			<tr>
<?php
				if (is_numeric($trafficCap) && $trafficCap > 0) {
?>
					<td class="value"><?php echo $trafficCap; ?> MB</td>
<?php
				} elseif (is_numeric($trafficCap) && $trafficCap == 0) {
?>
					<td class="value">Uncapped</td>
<?php
				} else {
?>
					<td class="value"><?php echo $trafficCap; ?></td>
<?php
				}
				if (is_numeric($trafficCap) && $trafficCap == 0) {
?>
					<td class="value">N/A</td>
<?php
				} else {
?>
					<td class="value"><?php printf("%.2f",$topupTrafficRemaining); ?> MB</td>
<?php
				}
				if (isset($currentTrafficTopup['Used']) && isset($currentTrafficTopup['Cap'])) {
?>
					<td class="value">
						<?php 
							printf("%.2f",$currentTrafficTopup['Used']);
							print("/").$currentTrafficTopup['Cap'];
						?> MB
					</td>
<?php
				} else {
?>
					<td class="value">N/A</td>
<?php
				}
?>
				<td class="value"><?php printf("%.2f",$totalTraffic); ?> MB</td>
			</tr>
			<tr>
				<td colspan="4" class="section">Uptime Usage</td>
			</tr>
			<tr>
				<td class="title">Cap</td>
				<td class="title">Unused Topup</td>
				<td class="title">Current Topup</td>
				<td class="title">Used This Month</td>
			</tr>
			<tr>
<?php
				if (is_numeric($uptimeCap) && $uptimeCap > 0) {
?>
					<td class="value"><?php echo $uptimeCap; ?> Min</td>
<?php
				} elseif (is_numeric($uptimeCap) && $uptimeCap == 0) {
?>
					<td class="value">Uncapped</td>
<?php
				} else {
?>
					<td class="value"><?php echo $uptimeCap; ?></td>
<?php
				}
				if (is_numeric($uptimeCap) && $uptimeCap == 0) {
?>
					<td class="value">N/A</td>
<?php
				} else {
?>
					<td class="value"><?php printf("%.2f",$topupUptimeRemaining); ?> Min</td>
<?php
				}
				if (isset($currentUptimeTopup['Used']) && isset($currentUptimeTopup['Cap'])) {
?>
					<td class="value">
						<?php
							printf("%.2f",$currentUptimeTopup['Used']);
							print("/").$currentUptimeTopup['Cap'];
						?> Min
					</td>
<?php
				} else {
?>
					<td class="value">N/A</td>
<?php
				}
?>
				<td class="value"><?php printf("%.2f",$totalUptime); ?> Min</td>
			</tr>
<!--
			<tr>
				<td colspan="2" class="section">Notifications</td>
			</tr>
			<form method="post">
			<tr>
				<td class="title">Email Address</td>
				<td class="value">
					<input type="text" name="notifyMethodEmail" value="php echo $userEmail; "></input>
				</td>
			</tr>
			<tr>
				<td class="title">Cell Number</td>
				<td class="value">
					<input type="text" name="notifyMethodCell" value="php echo $userPhone; "></input>
				</td>
			</tr>
			</form>
--!>

<?php
		}
?>
		<tr>
			<td></td>
		</tr>
		<tr>
			<td></td>
		</tr>
		<tr>
			<td colspan="4" align="center">
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

/*
# If this is a post and we're updating
if (isset($_POST['notifyUpdate']) && $_POST['notifyUpdate'] == "update") {

	$username = $_SESSION['username'];

	# Get user's ID
	$sql = "
		SELECT
				ID
		FROM
				${DB_TABLE_PREFIX}users
		WHERE
				Username = '$username'
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
*/

displayDetails();

# Footer
include("include/footer.php");

# vim: ts=4
?>
