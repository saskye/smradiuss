<?php
# Main User Control Panel Page
# Copyright (c) 2007-20011, AllWorldIT
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


# Displays error
function webuiError($msg) {
	echo isset($msg) ? $msg : "Unknown error";
}


# Display details
function displayDetails() { 
	global $db;
	global $DB_TABLE_PREFIX;

	# Get user's ID
	$sql = "
		SELECT
			ID, Username
		FROM
			${DB_TABLE_PREFIX}users
		WHERE
			Username = ".$db->quote($_SESSION['username'])."
	";
	$res = $db->query($sql);
	if (!(is_object($res))) {
		webuiError("Error fetching user information");
	}

	$row = $res->fetchObject();

	# Set user ID
	$userID = $row->id;
	$username = $row->username;

	# Get accounting data
	$currentMonth = date("Y-m");

	$sql = "
		SELECT
			SUM(AcctSessionTime) / 60 AS AcctSessionTime,
			SUM(AcctInputOctets) / 1024 / 1024 +
			SUM(AcctInputGigawords) * 4096 +
			SUM(AcctOutputOctets) / 1024 / 1024 +
			SUM(AcctOutputGigawords) * 4096 AS TotalTraffic
		FROM
			${DB_TABLE_PREFIX}accounting
		WHERE
			Username = ".$db->quote($username)."
		AND
			PeriodKey = ".$db->quote($currentMonth)."
	";
	$res = $db->query($sql);
	if (!(is_object($res))) {
		webuiError("Error fetching user accounting");
	}

	# Set total traffic and uptime used
	$totalTraffic = 0;
	$totalUptime = 0;

	# Pull in row
	$row = $res->fetchObject();

	# Traffic
	if (isset($row->totaltraffic) && $row->totaltraffic > 0) {
		$totalTraffic += $row->totaltraffic;
	}
	# Uptime
	if (isset($row->acctsessiontime) && $row->acctsessiontime > 0) {
		$totalUptime += $row->acctsessiontime;
	}

	# Fetch user uptime and traffic cap (group attributes)
	$sql = "
		SELECT
			${DB_TABLE_PREFIX}group_attributes.Name, ${DB_TABLE_PREFIX}group_attributes.Value
		FROM
			${DB_TABLE_PREFIX}group_attributes, ${DB_TABLE_PREFIX}users_to_groups
		WHERE
			${DB_TABLE_PREFIX}users_to_groups.GroupID = ${DB_TABLE_PREFIX}group_attributes.GroupID
			AND ${DB_TABLE_PREFIX}users_to_groups.UserID = ".$db->quote($userID)."
			AND ${DB_TABLE_PREFIX}group_attributes.Disabled = 0
	";
	$res = $db->query($sql);
	if (!(is_object($res))) {
		webuiError("Error fetching user attributes");
	}

	# Initial values
	$trafficCap = "Prepaid";
	$uptimeCap = "Prepaid";
	while ($row = $res->fetchObject()) {
		if ($row->name === "SMRadius-Capping-Traffic-Limit") {
			$trafficCap = (int)$row->value;
		}
		if ($row->name === "SMRadius-Capping-Uptime-Limit") {
			$uptimeCap = (int)$row->value;
		}
	}

	# Fetch user uptime and traffic cap (user attributes)
	$sql = "
		SELECT
			Name, Value
		FROM
			${DB_TABLE_PREFIX}user_attributes
		WHERE
			UserID = ".$db->quote($userID)."
			AND Disabled = 0
	";
	$res = $db->query($sql);
	if (!(is_object($res))) {
		webuiError("Error fetching user attributes");
	}

	# Override group_attributes with user attributes
	while ($row = $res->fetchObject()) {
		if ($row->name === "SMRadius-Capping-Traffic-Limit") {
			$trafficCap = (int)$row->value;
		}
		if ($row->name === "SMRadius-Capping-Uptime-Limit") {
			$uptimeCap = (int)$row->value;
		}
	}

	# Fetch user uptime and traffic summary
	$sql = "
		SELECT
			${DB_TABLE_PREFIX}topups_summary.Balance,
			${DB_TABLE_PREFIX}topups.Type,
			${DB_TABLE_PREFIX}topups.Value,
			${DB_TABLE_PREFIX}topups.ValidFrom,
			${DB_TABLE_PREFIX}topups.ValidTo
		FROM
			${DB_TABLE_PREFIX}topups_summary,
			${DB_TABLE_PREFIX}topups
		WHERE
			${DB_TABLE_PREFIX}topups_summary.TopupID = ${DB_TABLE_PREFIX}topups.ID
			AND ${DB_TABLE_PREFIX}topups.UserID = ".$db->quote($userID)."
			AND ${DB_TABLE_PREFIX}topups_summary.PeriodKey = ".$db->quote($currentMonth)."
			AND ${DB_TABLE_PREFIX}topups_summary.Depleted = 0
		ORDER BY
			${DB_TABLE_PREFIX}topups.Timestamp ASC
	";
	$res = $db->query($sql);
	if (!(is_object($res))) {
		webuiError("Error fetching topup summaries");
	}

	# Store summary topups
	$topups = array();
	$i = 0;
	while ($row = $res->fetchObject()) {

		$topups[$i] = array();

		$topups[$i]['Type'] = $row->type;
		$topups[$i]['CurrentLimit'] = $row->balance;
		$topups[$i]['Limit'] = $row->value;
		$topups[$i]['ValidFrom'] = $row->validfrom;
		$topups[$i]['Expires'] = $row->validto;

		$i++;
	}

	# Fetch user uptime and traffic topups
	$thisMonthTimestamp = date("Y-m").'-01';
	$now = date("Y-m-d");
	$sql = "
		SELECT
			Value, Type, ValidFrom, ValidTo
		FROM
			topups
		WHERE
			UserID = ".$db->quote($userID)."
			AND ValidFrom = ".$db->quote($thisMonthTimestamp)."
			AND ValidTo >= ".$db->quote($now)."
			AND Depleted = 0
		ORDER BY
			Timestamp ASC
	";
	$res = $db->query($sql);
	if (!(is_object($res))) {
		webuiError("Error fetching topup");
	}

	# Store normal topups
	while ($row = $res->fetchObject()) {
		$topups[$i] = array();
		$topups[$i]['Type'] = $row->type;
		$topups[$i]['Limit'] = $row->value;
		$topups[$i]['ValidFrom'] = $row->validfrom;
		$topups[$i]['Expires'] = $row->validto;

		$i++;
	}

	# Calculate topup usage for prepaid and normal users
	$totalTrafficTopupsAvail = 0;
	if (!(is_numeric($trafficCap) && $trafficCap == 0)) {

		# Excess usage
		$excess = 0;
		if ($trafficCap === "Prepaid") {
			$excess = $totalTraffic;
		} else {
			$excess = $totalTraffic > $trafficCap ? ($totalTraffic - $trafficCap) : 0;
		}

		# Loop through all valid topups
		$trafficRows = array();
		$i = 0;
		foreach ($topups as $topup) {

			# Traffic topups
			if ($topup['Type'] == 1) {

				# Topup not currently in use
				if ($excess <= 0) {
					$trafficRows[$i] = array();

					$trafficRows[$i]['Cap'] = $topup['Limit'];
					$trafficRows[$i]['Used'] = isset($topup['CurrentLimit']) ? ($topup['Limit'] - $topup['CurrentLimit']) : 0;
					$trafficRows[$i]['ValidFrom'] = $topup['ValidFrom'];
					$trafficRows[$i]['Expires'] = $topup['Expires'];

					# Set total available topups
					$totalTrafficTopupsAvail += isset($topup['CurrentLimit']) ? $topup['CurrentLimit'] : $topup['Limit'];

					$i++;

				# Topup currently in use
				} elseif (!isset($topup['CurrentLimit']) && $excess < $topup['Limit']) {
					$trafficRows[$i] = array();

					$trafficRows[$i]['Cap'] = $topup['Limit'];
					$trafficRows[$i]['Used'] = $excess;
					$trafficRows[$i]['ValidFrom'] = $topup['ValidFrom'];
					$trafficRows[$i]['Expires'] = $topup['Expires'];

					# Set total available topups
					$totalTrafficTopupsAvail += $topup['Limit'];

					# Set current topup
					$currentTrafficTopup = array();
					$currentTrafficTopup['Used'] = $excess;
					$currentTrafficTopup['Cap'] = $topup['Limit'];

					# If we hit this topup then all the rest of them are available
					$excess = 0;

					$i++;

				} elseif (isset($topup['CurrentLimit']) && $excess < $topup['CurrentLimit']) {
					$trafficRows[$i] = array();

					$trafficRows[$i]['Cap'] = $topup['Limit'];
					$trafficRows[$i]['Expires'] = $topup['Expires'];
					$trafficRows[$i]['ValidFrom'] = $topup['ValidFrom'];

					$trafficRows[$i]['Used'] = ($topup['Limit'] - $topup['CurrentLimit']) + $excess;

					# Set total available topups
					$totalTrafficTopupsAvail += $topup['CurrentLimit'];

					# Set current topup
					$currentTrafficTopup = array();
					$currentTrafficTopup['Used'] = ($topup['Limit'] - $topup['CurrentLimit']) + $excess;
					$currentTrafficTopup['Cap'] = $topup['Limit'];

					# If we hit this topup then all the rest of them are available
					$excess = 0;

					$i++;

				# Topup has been used up
				} else {
					$trafficRows[$i] = array();

					$trafficRows[$i]['Cap'] = $topup['Limit'];
					$trafficRows[$i]['Used'] = $topup['Limit'];
					$trafficRows[$i]['ValidFrom'] = $topup['ValidFrom'];
					$trafficRows[$i]['Expires'] = $topup['Expires'];

					# Subtract this topup from excess usage
					$excess -= isset($topup['CurrentLimit']) ? $topup['CurrentLimit'] : $topup['Limit'];

					$i++;
				}
			}
		}
	}

	# Calculate topup usage for prepaid and normal users
	$totalUptimeTopupsAvail = 0;
	if (!(is_numeric($uptimeCap) && $uptimeCap == 0)) {

		# Excess usage
		$excess = 0;
		if ($uptimeCap === "Prepaid") {
			$excess = $totalUptime;
		} else {
			$excess = $totalUptime > $uptimeCap ? ($totalUptime - $uptimeCap) : 0;
		}

		# Loop through all valid topups
		$uptimeRows = array();
		$i = 0;
		foreach ($topups as $topup) {

			# Uptime topups
			if ($topup['Type'] == 2) {

				# Topup not currently in use
				if ($excess <= 0) {
					$uptimeRows[$i] = array();

					$uptimeRows[$i]['Cap'] = $topup['Limit'];
					$uptimeRows[$i]['Used'] = isset($topup['CurrentLimit']) ? ($topup['Limit'] - $topup['CurrentLimit']) : 0;
					$uptimeRows[$i]['ValidFrom'] = $topup['ValidFrom'];
					$uptimeRows[$i]['Expires'] = $topup['Expires'];

					# Set total available topups
					$totalUptimeTopupsAvail += isset($topup['CurrentLimit']) ? $topup['CurrentLimit'] : $topup['Limit'];

					$i++;

				# Topup currently in use
				} elseif (!isset($topup['CurrentLimit']) && $excess < $topup['Limit']) {
					$uptimeRows[$i] = array();

					$uptimeRows[$i]['Cap'] = $topup['Limit'];
					$uptimeRows[$i]['Used'] = $excess;
					$uptimeRows[$i]['ValidFrom'] = $topup['ValidFrom'];
					$uptimeRows[$i]['Expires'] = $topup['Expires'];

					# Set total available topups
					$totalUptimeTopupsAvail += $topup['Limit'];

					# Set current topup
					$currentUptimeTopup = array();
					$currentUptimeTopup['Used'] = $excess;
					$currentUptimeTopup['Cap'] = $topup['Limit'];

					# If we hit this topup then all the rest of them are available
					$excess = 0;

					$i++;

				} elseif (isset($topup['CurrentLimit']) && $excess < $topup['CurrentLimit']) {
					$uptimeRows[$i] = array();

					$uptimeRows[$i]['Cap'] = $topup['Limit'];
					$uptimeRows[$i]['Expires'] = $topup['Expires'];
					$uptimeRows[$i]['ValidFrom'] = $topup['ValidFrom'];

					$uptimeRows[$i]['Used'] = ($topup['Limit'] - $topup['CurrentLimit']) + $excess;

					# Set total available topups
					$totalUptimeTopupsAvail += $topup['CurrentLimit'];

					# Set current topup
					$currentUptimeTopup = array();
					$currentUptimeTopup['Used'] = ($topup['Limit'] - $topup['CurrentLimit']) + $excess;
					$currentUptimeTopup['Cap'] = $topup['Limit'];

					# If we hit this topup then all the rest of them are available
					$excess = 0;

					$i++;

				# Topup has been used up
				} else {
					$uptimeRows[$i] = array();

					$uptimeRows[$i]['Cap'] = $topup['Limit'];
					$uptimeRows[$i]['Used'] = $topup['Limit'];
					$uptimeRows[$i]['ValidFrom'] = $topup['ValidFrom'];
					$uptimeRows[$i]['Expires'] = $topup['Expires'];

					# Subtract this topup from excess usage
					$excess -= isset($topup['CurrentLimit']) ? $topup['CurrentLimit'] : $topup['Limit'];

					$i++;
				}
			}
		}
	}

	# HTML
?>
	<table class="blockcenter">
		<tr>
			<td width="500" colspan="4" class="section">Account Information</td>
		</tr>
		<tr>
			<td align="center" class="title">Username</td>
			<td align="center" class="title">Traffic Cap</td>
			<td align="center" class="title">Uptime Cap</td>
		</tr>
		<tr>
			<td align="center" class="value"><?php echo $username; ?></td>
			<td align="center" class="value">
				<?php
					if (is_numeric($trafficCap) && $trafficCap == 0) {
						echo "Unlimited";
					} elseif (is_string($trafficCap) && $trafficCap === "Prepaid") {
						echo $trafficCap;
					} else {
						echo $trafficCap." MB";
					}
				?>
			</td>
			<td align="center" class="value">
				<?php
					if (is_numeric($uptimeCap) && $uptimeCap == 0) {
						echo "Unlimited";
					} elseif (is_string($uptimeCap) && $uptimeCap === "Prepaid") {
						echo $uptimeCap;
					} else {
						echo $uptimeCap." MB";
					}
				?>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td colspan="4" class="section">Traffic Usage</td>
		</tr>
		<tr>
			<td align="center" class="title">Active Topup</td>
			<td align="center" class="title">Total Topup</td>
			<td align="center" class="title">Total Usage</td>
		</tr>
			<td align="center" class="value">
				<?php
					if (isset($currentTrafficTopup) && (!(is_numeric($trafficCap) && $trafficCap == 0))) {
						echo sprintf("%.2f",$currentTrafficTopup['Used'])."/".sprintf($currentTrafficTopup['Cap'])." MB";
					} else {
						echo "None";
					}
				?>
			</td>
			<td align="center" class="value"><?php echo $totalTrafficTopupsAvail." MB"; ?></td>
			<td align="center" class="value"><?php echo sprintf("%.2f",$totalTraffic)." MB"; ?></td>
		<tr>
		</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td colspan="4" class="section">Uptime Usage</td>
		</tr>
		<tr>
			<td align="center" class="title">Active Topup</td>
			<td align="center" class="title">Total Topup</td>
			<td align="center" class="title">Total Usage</td>
		</tr>
		<tr>
			<td align="center" class="value">
				<?php
					if (isset($currentUptimeTopup) && (!(is_numeric($uptimeCap) && $uptimeCap == 0))) {
						echo sprintf("%.2f",$currentUptimeTopup['Used'])."/".sprintf($currentUptimeTopup['Cap'])." MB";
					} else {
						echo "None";
					}
				?>
			</td>
			<td align="center" class="value"><?php echo $totalUptimeTopupsAvail." MB"; ?></td>
			<td align="center" class="value"><?php echo sprintf("%.2f",$totalUptime)." Min"; ?></td>
		</tr>
	</table>
	<p>&nbsp;</p>
<?php
	# Dont display if we unlimited
	if (!(is_numeric($trafficCap) && $trafficCap == 0)) {
?>
		<table class="blockcenter">
			<tr>
				<td width="500" colspan="3" class="section">Topup Overview: Traffic</td>
			</tr>
			<tr>
				<td align="center" class="title">Used</td>
				<td align="center" class="title">Valid From</td>
				<td align="center" class="title">Valid To</td>
			</tr>
<?php
			foreach ($trafficRows as $trafficRow) {
?>
				<tr>
					<td align="center" class="value">
<?php
							echo sprintf("%.2f",$trafficRow['Used'])."/".sprintf($trafficRow['Cap'])." MB";
?>
					</td>
					<td align="center" class="value"><?php $validFrom = strtotime($trafficRow['ValidFrom']); echo date("Y-m-d",$validFrom);?></td>
					<td align="center" class="value"><?php $validTo = strtotime($trafficRow['Expires']); echo date("Y-m-d",$validTo);?></td>
				</tr>
<?php
			}
?>
		</table>
<?php
	}

	# Dont display if we unlimited
	if (!(is_numeric($uptimeCap) && $uptimeCap == 0)) {
?>
		<p>&nbsp;</p>
		<table class="blockcenter">
			<tr>
				<td width="500" colspan="3" class="section">Topup Overview: Uptime</td>
			</tr>
			<tr>
				<td align="center" class="title">Used</td>
				<td align="center" class="title">Valid From</td>
				<td align="center" class="title">Valid To</td>
			</tr>
<?php
			foreach ($uptimeRows as $uptimeRow) {
?>
				<tr>
					<td align="center" class="value">
<?php
						echo sprintf("%.2f",$uptimeRow['Used'])."/".sprintf($uptimeRow['Cap'])." MB";
?>
					</td>
					<td align="center" class="value"><?php $validFrom = strtotime($uptimeRow['ValidFrom']); echo date("Y-m-d",$validFrom);?></td>
					<td align="center" class="value"><?php $validTo = strtotime($uptimeRow['Expires']); echo date("Y-m-d",$validTo);?></td>
				</tr>
<?php
			}
?>
		</table>
<?php
	}
?>
	<p>&nbsp;</p>
	<p align="center"><a href="logs.php">Usage Logs</a></p>
<?php
}

displayDetails();

# Footer
include("include/footer.php");

# vim: ts=4
?>
