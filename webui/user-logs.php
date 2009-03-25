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

<form id="main_form" action="user-attributes.php" method="post">

<p />

	<table class="results" style="width: 75%;">
		<tr class="resultstitle">
			<td class="textcenter">ServiceType</td>
			<td class="textcenter">FramedProtocol</td>
			<td class="textcenter">NASPort</td>
			<td class="textcenter">NASPortType</td>
			<td class="textcenter">CallingSationID</td>
			<td class="textcenter">CalledStationID</td>
			<td class="textcenter">NASPortID</td>
			<td class="textcenter">AcctSessionID</td>
			<td class="textcenter">FramedIPAddress</td>
			<td class="textcenter">ActAuthentic</td>
			<td class="textcenter">EventTimestamp</td>
			<td class="textcenter">NASIdentifier</td>
			<td class="textcenter">NASIPAddress</td>
			<td class="textcenter">AcctDelayTime</td>
			<td class="textcenter">AcctSessionTime</td>
			<td class="textcenter">AcctInputOctets</td>
			<td class="textcenter">AcctInputGigawords</td>
			<td class="textcenter">AcctInputPackets</td>
			<td class="textcenter">AcctOutputOctets</td>
			<td class="textcenter">AcctOutputGigawords</td>
			<td class="textcenter">AcctOutputPackets</td>
			<td class="textcenter">AcctStatusType</td>
			<td class="textcenter">AcctTerminateCause</td>
		</tr>
<?php
	if (isset($_POST['user_id'])) {

		# Fetch username from id supplied
		$stmt = $db->prepare("SELECT Username FROM ${DB_TABLE_PREFIX}users WHERE ID = ?");
		$stmt->execute(array($_POST['user_id']));
		$row = $stmt->fetchObject();
		$stmt->closeCursor();

		$getuser = $row->username;
		$sql = "SELECT ServiceType, FramedProtocol, NASPort, NASPortType, CallingStationID, CalledStationID, NASPortID, AcctSessionID, FramedIPAddress, AcctAuthentic, EventTimestamp, NASIdentifier, NASIPAddress, AcctDelayTime, AcctSessionTime, AcctInputOctets, AcctInputGigawords, AcctInputPackets, AcctOutputOctets, AcctOutputGigawords, AcctOutputPackets, AcctStatusType, AcctTerminateCause FROM ${DB_TABLE_PREFIX}accounting WHERE Username = '$getuser'";

		$res = $db->query($sql);

		$rownums = 0;
		while ($row = $res->fetchObject()) {
			if ($row->framedipaddress != NULL) {
				$rownums = $rownums + 1;
			} else {
				$rownums = $rownums - 1;
			}
?>
			<tr class="resultsitem">
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
				<td class="textcenter"><?php echo $row->eventtimestamp ?></td>
				<td class="textcenter"><?php echo $row->nasidentifier ?></td>
				<td class="textcenter"><?php echo $row->nasipaddress ?></td>
				<td class="textcenter"><?php echo $row->acctdelaytime ?></td>
				<td class="textcenter"><?php echo $row->acctsessiontime ?></td>
				<td class="textcenter"><?php echo $row->acctinputoctets ?></td>
				<td class="textcenter"><?php echo $row->acctinputgigawords ?></td>
				<td class="textcenter"><?php echo $row->acctinputpackets ?></td>
				<td class="textcenter"><?php echo $row->acctoutputoctets ?></td>
				<td class="textcenter"><?php echo $row->acctoutputgigawords ?></td>
				<td class="textcenter"><?php echo $row->acctoutputpackets ?></td>
				<td class="textcenter"><?php echo $row->acctstatustype ?></td>
				<td class="textcenter"><?php echo $row->acctterminatecause ?></td>
			</tr>
<?php
		}
		$res->closeCursor();
		if ($rownums <= 0) {
?>
			<p />
			<tr>
				<td colspan="23" class="textcenter">No logs found for user: <?php echo $getuser ?></td>
			</tr>
<?php
		}
		unset($rownums);
	} else {
?>
		<tr class="resultitem">
			<td colspan="5" class="textcenter">No User ID selected</td>
		</tr>
<?php
	}
?>
	</table>
</form>
<?php

printFooter();


# vim: ts=4
?>
