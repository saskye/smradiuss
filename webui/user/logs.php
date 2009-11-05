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

?>
	<table class="blockcenter" width="750">
		<tr>
			<td colspan="4" class="title">
				<form method="POST">
					<p class="middle center">
					Display logs between
<?php
					# Validate dates before sending
					if (isset($_POST['searchFrom'])) {
						if (!(preg_match("/^\d{4}\-(0[1-9]|1[0-2])\-(0[1-9]|1[0-9]|2[0-9]|3[0-1])$/",$_POST['searchFrom']))) {
							unset($_POST['searchFrom']);
						}
					}
					if (isset($_POST['searchFrom'])) {
						$searchFrom = date("Y-m-d",strtotime($_POST['searchFrom']));
						$_POST['searchFrom'] = $searchFrom;
					}
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
					# Validate dates before sending
					if (isset($_POST['searchTo'])) {
						if (!(preg_match("/^\d{4}\-(0[1-9]|1[0-2])\-(0[1-9]|1[0-9]|2[0-9]|3[0-1])$/",$_POST['searchTo']))) {
							unset($_POST['searchTo']);
						}
					}
					if (isset($_POST['searchTo'])) {
						$searchFrom = date("Y-m-d",strtotime($_POST['searchTo']));
						$_POST['searchTo'] = $searchFrom;
					}
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

			# Accounting query FIXME nas receive and transmit rates
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
						Username = ".$db->quote($_SESSION['username'])."
						$extraSQL
				ORDER BY
						EventTimestamp
				DESC
			";

			$res = $db->prepare($sql);
			$res->execute($extraSQLVals);

			# Display logs
			$totalInput = 0;
			$totalOutput = 0;
			$totalTime = 0;
			while ($row = $res->fetchObject()) {

				# Input data calculation
				$inputData = 0;
				if (isset($row->acctinputoctets) && $row->acctinputoctets > 0) {
					$inputData += ceil($row->acctinputoctets / 1024 / 1024);
				}
				if (isset($row->acctinputgigawords) && $row->acctinputgigawords > 0) {
					$inputData += ceil($row->acctinputgigawords * 4096);
				}
				$totalInput += $inputData;

				# Output data calculation
				$outputData = 0;
				if (isset($row->acctoutputoctets) && $row->acctoutputoctets > 0) {
					$outputData += ceil($row->acctoutputoctets / 1024 / 1024);
				}
				if (isset($row->acctoutputgigawords) && $row->acctoutputgigawords > 0) {
					$outputData += ceil($row->acctoutputgigawords * 4096);
				}
				$totalOutput += $outputData;

				# Uptime calculation
				$sessionTime = 0;
				if (isset($row->acctsessiontime) && $row->acctsessiontime > 0) {
					$sessionTime += ceil($row->acctsessiontime / 60);
				}
				$totalTime += $sessionTime;
?>
				<tr>
					<td class="desc"><?php echo $row->eventtimestamp; ?></td>
					<td class="desc"><?php echo $sessionTime; ?></td>
					<td class="desc"><?php echo $row->callingstationid; ?></td>
					<td class="center desc"><?php echo strRadiusTermCode($row->acctterminatecause); ?></td>
					<td class="center desc">
						<?php 
							if (isset($row->nastransmitrate)) {
								echo $row->nastransmitrate;
							}
						?>
					</td>
					<td class="center desc">
						<?php 
							if (isset($row->nasreceiverate)) {
								echo $row->nasreceiverate;
							}
						?>
					</td>
					<td class="right desc"><?php echo $inputData; ?></td>
					<td class="right desc"><?php echo $outputData; ?></td>
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
				$totalTraffic = $totalInput + $totalOutput;
?>
				<tr>
					<td colspan="6" class="right">Sub Total:</td>
					<td class="right desc"><?php echo $totalInput; ?></td>
					<td class="right desc"><?php echo $totalOutput; ?></td>
				</tr>
				<tr>
					<td colspan="6" class="right">Total:</td>
					<td colspan="2" class="center desc"><?php echo $totalTraffic; ?></td>
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
