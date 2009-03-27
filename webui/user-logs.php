<?php
# Module: Policy delete
# Copyright (C) 2008, LinuxRulz
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
		"Tabs" => array(
			"Back to user list" => "user-main.php"
		),
));



?>
<p class="pageheader">User Log</p>
<?php
if (isset($_POST['user_id'])) {

		# Which user in the accounting table should we look for?
		$stmt = $db->prepare("SELECT Username FROM ${DB_TABLE_PREFIX}users WHERE ID = ?");
		$stmt->execute(array($_POST['user_id']));
		$row = $stmt->fetchObject();
		$stmt->closeCursor();
		$getuser = $row->username;

?>
		<form id="main_form" action="user-logs.php" method="post">
			<!-- Search things -->
			<div>
				<table>
					<tr>
						<td>From (yyyy-mm-dd)</td>
					</tr>
					<tr>
						<td><input type="text" name="date_from" /></td>
					</tr>
					<tr>
						<td>To (yyyy-mm-dd)</td>
					</tr>
					<tr>
						<td><input type="text" name="date_to" /></td>
					</tr>
					<tr>
						<input type="hidden" name="user_id" value=<?php echo $_POST['user_id']; ?> />
						<td><input type="submit" value="Get results" /></td>
					</tr>
				</table>
			</div>
		</form>

		<p />

		<!-- Tables headings -->
		<table class="results" style="width: 75%;">
			<tr class="resultstitle">
				<td class="textcenter">EventTimestamp</td>
				<td class="textcenter">ServiceType</td>
				<td class="textcenter">FramedProtocol</td>
				<td class="textcenter">NASPort</td>
				<td class="textcenter">NASPortType</td>
				<td class="textcenter">CallingSationID</td>
				<td class="textcenter">CalledStationID</td>
				<td class="textcenter">NASPortID</td>
				<td class="textcenter">AcctSessionID</td>
				<td class="textcenter">FramedIPAddress</td>
				<td class="textcenter">AcctAuthentic</td>
				<td class="textcenter">NASIdentifier</td>
				<td class="textcenter">NASIPAddress</td>
				<td class="textcenter">AcctDelayTime</td>
				<td class="textcenter">AcctSessionTime</td>
				<td class="textcenter">Data-Input</td>
				<td class="textcenter">Data-Output</td>
				<td class="textcenter">AcctStatusType</td>
				<td class="textcenter">AcctTerminateCause</td>
			</tr>
<?php
			# Extra SQL
			$extraSQL = "";
			$extraSQLVals = array();
			$limitSQL = "";

			# Do we have a from date?, if so add it to our query
			if (isset($_POST['date_from'])) {
				$extraSQL .= " AND EventTimestamp >= ?";
				array_push($extraSQLVals,$_POST['date_from']);
			}
			# Do we have a from date?, if so add it to our query
			if (isset($_POST['date_to'])) {
				$extraSQL .= " AND EventTimestamp <= ?";
				array_push($extraSQLVals,$_POST['date_to']);
			}

			# Modify if we had a partial search or no search
			if (count($extraSQLVals) < 2) {
				$limitSQL = "LIMIT 50";
			}

				# Query to get all default data
				$sql = "
					SELECT
							EventTimestamp, 
							ServiceType,
							FramedProtocol,
							NASPort,
							NASPortType, 
							CallingStationID, 
							CalledStationID, 
							NASPortID, 
							AcctSessionID, 
							FramedIPAddress, 
							AcctAuthentic, 
							NASIdentifier, 
							NASIPAddress, 
							AcctDelayTime, 
							AcctSessionTime, 
							AcctInputOctets, 
							AcctInputGigawords, 
							AcctOutputOctets, 
							AcctOutputGigawords, 
							AcctStatusType, 
							AcctTerminateCause 
					FROM 
							${DB_TABLE_PREFIX}accounting 
					WHERE 
							Username = '$getuser'
							$extraSQL
					ORDER BY
							EventTimestamp
					DESC
						$limitSQL
				";

				$res = $db->prepare($sql);
				$res->execute($extraSQLVals);

				$rownums = 0;
				while ($row = $res->fetchObject()) {

					if ($row->eventtimestamp != NULL) {
						$rownums = $rownums + 1;
					} else {
						$rownums = $rownums - 1;
					}

					# Data usage
					# ==========

					# Input
					$inputData = 0;

					if (!empty($row->acctinputoctets) && $row->acctinputoctets > 0) {
						$inputData = ($row->accinputoctets / 1024 / 1024);
					}
					if (!empty($row->acctinputgigawords) && $row->inputgigawords > 0) {
						$inputData = ($row->acctinputgigawords * 4096);
					}
					if ($inputData != 0) {
						$inputDataDisplay = ceil($inputData * 100)/100;
					} else {
						$inputDataDisplay = 0;
					}

					$totalInputData = $totalInputData + $inputData;

					# Output
					$outputData = 0;

					if (!empty($row->acctoutputoctets) && $row->acctoutputoctets > 0) {
						$outputData = ($row->acctoutputoctets / 1024 / 1024);
					}
					if (!empty($row->acctoutputgigawords) && $row->acctoutputgigawords > 0) {
						$outputData = ($row->acctoutputgigawords * 4096);
					}
					if ($outputData != 0) {
						$outputDataDisplay = ceil($outputData * 100)/100;
					} else {
						$outputDataDisplay = 0;
					}

					$totalOutputData = $totalOutputData + $outputData;

?>
			<tr class="resultsitem">
				<td class="textcenter"><?php echo $row->eventtimestamp ?></td>
				<td class="textcenter"><?php echo $row->servicetype ?></td>
				<td class="textcenter"><?php echo $row->framedprotocol ?></td>
				<td class="textcenter"><?php echo $row->nasport ?></td>
				<td class="textcenter"><?php echo $row->nasporttype ?></td>
				<td class="textcenter"><?php echo $row->callingstationid ?></td>
				<td class="textcenter"><?php echo $row->calledstationid ?></td>
				<td class="textcenter"><?php echo $row->nasportid ?></td>
				<td class="textcenter"><?php echo $row->acctsessionid ?></td>
				<td class="textcenter"><?php echo $row->framedipaddress ?></td>
				<td class="textcenter"><?php echo $row->acctauthentic ?></td>
				<td class="textcenter"><?php echo $row->nasidentifier ?></td>
				<td class="textcenter"><?php echo $row->nasipaddress ?></td>
				<td class="textcenter"><?php echo $row->acctdelaytime ?></td>
				<td class="textcenter"><?php echo $row->acctsessiontime ?></td>
				<td class="textcenter"><?php echo $inputDataDisplay ?> Mbytes</td>
				<td class="textcenter"><?php echo $outputDataDisplay ?> Mbytes</td>
				<td class="textcenter"><?php echo $row->acctstatustype ?></td>
				<td class="textcenter"><?php echo $row->acctterminatecause ?></td>
			</tr>
<?php
			}
			$res->closeCursor();

			if ($rownums <= 0) {
?>
			<tr>
				<td colspan="23" class="textcenter">No logs found for user: <?php echo $getuser ?></td>
			</tr>
<?php
			}
				unset($rownums);


} else {
?>
			<tr>
				<td class="warning">No user ID selected</td>
			</tr>
<?php
}


?>
		</table>
<?php
printFooter();


# vim: ts=4
?>
