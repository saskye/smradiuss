<?php
# Radius user logs
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

# pre takes care of authentication and creates soap object we need
include("include/pre.php");
# Page header
include("include/header.php");
# Database
include_once("include/db.php");
# Radius functions
require_once("include/radiuscodes.php");


# NB: We will only end up here if we authenticated!


# Display settings
function displayLogs() {

	global $db;
	global $DB_TABLE_PREFIX;

	$username = $_SESSION['username'];

?>

	<table class="blockcenter" width="750">
		<tr>
			<td colspan="4" class="title">
				<form method="POST">
					<p class="middle center">
					Display logs between
<?php
					if (isset($_POST['searchFrom'])) {
?>
						<input type="text" name="searchFrom" size="11" value="<?php echo $_POST['searchFrom'] ?>"/> 
<?php
					} else {
?>
						<input type="text" name="searchFrom" size="11"/> 
<?php
					}
?>
					and 
<?php
					if (isset($_POST['searchTo'])) {
?>
						<input type="text" name="searchTo" size="11" value="<?php echo $_POST['searchTo'] ?>"/> 
<?php
					} else {
?>
						<input type="text" name="searchTo"  size="11"/>
<?php
					}
?>
					<input type="submit" value="search">
					</p>
				</form>
			</td>
			<td colspan="2" class="section">Connect Speed</td>
			<td colspan="2" class="section">Traffic Usage<br> (Mbyte)</td>
		</tr>
		<tr>
			<td class="section">Timestamp</td>
			<td class="section">Duration<br> (Min)</td>
			<td class="section">Caller ID</td>
			<td class="section">Term Reason</td>
			<td class="section">Receive</td>
			<td class="section">Transmit</td>
			<td class="section">Upload</td>
			<td class="section">Download</td>
		</tr>

<?php

		# Extra SQL
		$extraSQL = "";
		$extraSQLVals = array();
		$limitSQL = "";

		if (isset($_POST['searchFrom']) && isset($_POST['searchTo'])) {

			$extraSQL .= " AND EventTimestamp >= ?";
			array_push($extraSQLVals,$_POST['searchFrom']);
			$extraSQL .= " AND EventTimestamp <= ?";
			array_push($extraSQLVals,$_POST['searchTo']);

			# Query to get all default data
			$sql = "
				SELECT
						EventTimestamp, 
						CallingStationID, 
						AcctSessionTime, 
						AcctInputOctets, 
						AcctInputGigawords, 
						AcctOutputOctets, 
						AcctOutputGigawords, 
						AcctTerminateCause 
				FROM 
						${DB_TABLE_PREFIX}accounting 
				WHERE 
						Username = '$username'
						$extraSQL
				ORDER BY
						EventTimestamp
				DESC
				";

			$res = $db->prepare($sql);
			$res->execute($extraSQLVals);

			# Define totals:
			$totalData = 0;
			$totalInputData = 0;
			$totalOutputData = 0;
			$totalSessionTime = 0;

			while ($row = $res->fetchObject()) {

				# Input data calculation
				$inputDataItem = 0;

				if (!empty($row->acctinputoctets) && $row->acctinputoctets > 0) {
					$inputDataItem += ($row->acctinputoctets / 1024) / 1024;
				}
				if (!empty($row->acctinputgigawords) && $row->inputgigawords > 0) {
					$inputDataItem += ($row->acctinputgigawords * 4096);
				}
				$totalInputData += $inputDataItem;


				# Output data calculation
				$outputDataItem = 0;

				if (!empty($row->acctoutputoctets) && $row->acctoutputoctets > 0) {
					$outputDataItem += ($row->acctoutputoctets / 1024) / 1024;
				}
				if (!empty($row->acctoutputgigawords) && $row->acctoutputgigawords > 0) {
					$outputDataItem += ($row->acctoutputgigawords * 4096);
				}
				$totalOutputData += $outputDataItem;

				$totalData += $totalOutputData + $totalInputData;


				# Time calculation
				$sessionTimeItem = 0;
				if (!empty($row->acctsessiontime) && $row->acctsessiontime > 0) {
					$sessionTimeItem += ($row->acctsessiontime - ($row->acctsessiontime % 60)) / 60;
				}

				$totalSessionTime += $sessionTimeItem;

?>

				<tr>
					<td class="desc"><?php echo $row->eventtimestamp; ?></td>
					<td class="desc"><?php echo $row->acctsessiontime; ?></td>
					<td class="desc"><?php echo $row->callingstationid; ?></td>
					<td class="center desc"><?php echo strRadiusTermCode($row->acctterminatecause); ?></td>
					<td class="center desc"><?php echo "NASTransmitRate"; ?></td>
					<td class="center desc"><?php echo "NASReceiveRate"; ?></td>
					<td class="right desc"><?php printf('%.2f',$inputDataItem); ?></td>
					<td class="right desc"><?php printf('%.2f',$outputDataItem); ?></td>
				</tr>

<?php

			}
			if ($res->rowCount() == 0) {

?>

				<tr>
					<td colspan="8" class="info">There are no logs for the selected dates</td>
				</tr>	

<?php

			} else {

?>

				<tr>
					<td colspan="6" class="right">Sub Total:</td>
					<td class="right desc"><?php printf('%.2f',$totalInputData); ?></td>
					<td class="right desc"><?php printf('%.2f',$totalOutputData); ?></td>
				</tr>
				<tr>
					<td colspan="6" class="right">Total:</td>
					<td colspan="2" class="center desc"><?php printf('%.2f',$totalData); ?></td>
				</tr>

<?php

			}
		} else {

?>

			<tr>
				<td colspan="8" class="info">Please specify dates above in YYYY-MM-DD format and click "search".</td>
			</tr>

<?php

		}

?>

	</table>

<?php

}

?>

	<a href=".">Back</a><br>

<?php

displayLogs();

?>

	<a href=".">Back</a><br><br>

<?php


# Footer
include("include/footer.php");

# vim: ts=4
?>
