<?php
# Radius user logs
#
# Copyright (c) 2005-2008, AllWorldIT
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
# Menu header
include("../shared-php/menu-header.php");
# Functions we need
require_once("../shared-php/miscfuncs.php");



function displaySearch($userID)
{

?>
	<div class="sectiontitle">Search Radious Logs</div>
	<p />

	<form action="radius-user-logs.php" method="post">
		<div>
			<input type="hidden" name="user_id" value="<?php echo $userID ?>" />
			<input type="hidden" name="frmaction" value="logs_final" />
		</div>

		<table class="entrytable">
			<tr>
				<td class="title">From</td>
				<td class="entry">
					<input type="text" name="searchLogsFrom" value="<?php
							if (isset($_SESSION['radiusUserLogs_searchFrom'])) {
								echo $_SESSION['radiusUserLogs_searchFrom'];
							}
					?>" />
				</td>
			</tr>
			<tr>
				<td class="title">To</td>
				<td class="entry">
					<input type="text" name="searchLogsTo" value="<?php
							if (isset($_SESSION['radiusUserLogs_searchTo'])) {
								echo $_SESSION['radiusUserLogs_searchTo'];
							}
					?>" />
				</td>
			</tr>
			<tr>
				<td class="title">Order By</td>
				<td class="entry" colspan="2">
					<input type="radio" name="searchLogsOrderBy" value="date" checked="checked" /> Date
				</td>
			</tr>
		</table>
		<div class="text-center">
			<input type="submit" />
		</div>
	</form>

Note On Searching:
<ul>
	<li>Dates are in the format of YYYY-MM-DD</li>
	<li>Blank search criteria matches last 50 logs</li>
</ul>
<?php
}


# Display radius user logs
function displayLogs($userID,$searchOptions) {
	global $soap;

	# Radius functions
	require_once("radiuscodes.php");

	$userInfo = $soap->getRadiusUser($userID);

	$isDialup = preg_match('/dialup/i',$userInfo->Service);

	$userLogs = $soap->getRadiusUserLogs($userID,$searchOptions);
	if (is_array($userLogs)) {
?>
		<div class="sectiontitle">Radius User Logs</div>
		<br />

		<table class="resulttable">
			<tr>
				<td class="title">Timestamp</td>
				<td class="title">Session ID</td>
				<td class="title">Session Time</td>
<?php
				# Calling/Called station only for dialups
				if ($isDialup) {
?>
					<td class="title">Station</td>
<?php
				} else {
?>
					<td class="title">NAS Port</td>
<?php
				}
?>
				<td class="title">Port Rate</td>
				<td class="title">IP Address</td>
				<td class="title">Uploaded Mb</td>
				<td class="title">Downloaded Mb</td>
				<td class="title">Last Update</td>
				<td class="title">Status</td>
				<td class="title">Term Reason</td>
			</tr>
<?php
			$totalIn = 0;
			$totalOut = 0;
			$totalTime = 0;
			foreach ($userLogs as $entry) {
				$inputMBytes = $entry->AcctInputOctets > 0 ? $entry->AcctInputOctets / 1024 / 1024 : 0;
				$outputMBytes = $entry->AcctOutputOctets > 0 ? $entry->AcctOutputOctets / 1024 / 1024 : 0;
				$inputMBytes += $entry->AcctInputGigawords * 4096;
				$outputMBytes += $entry->AcctOutputGigawords * 4096;
?>
				<tr>
					<td><?php echo $entry->Timestamp; ?></td>
					<td><?php echo $entry->AcctSessionID; ?></td>
					<td class="text-right"><?php echo $entry->AcctSessionTime; ?></td>
<?php
					# Calling/Called station only for dialups
					if ($isDialup) {
?>
						<td><?php echo $entry->CallingStationID."/".$entry->CalledStationID; ?></td>
<?php
					} else {
?>
						<td><?php echo $entry->NASPort; ?></td>
<?php
					}
?>

					<td><?php echo $entry->NASTransmitRate ."/". $entry->NASReceiveRate; ?></td>
					<td><?php echo $entry->FramedIPAddress; ?></td>
					<td><?php echo sprintf('%.2f',$inputMBytes); ?></td>
					<td><?php echo sprintf('%.2f',$outputMBytes); ?></td>
					<td><?php echo $entry->LastAcctUpdate; ?></td>
					<td><?php echo $entry->Status; ?></td>
					<td><?php echo strRadiusTermCode($entry->ConnectTermReason); ?></td>
				</tr>
<?php
				$totalIn += $inputMBytes;
				$totalOut += $outputMBytes;
				$totalTime += $entry->AcctSessionTime;
			}
?>
				<tr>
					<td class="title text-center" colspan="12">Totals</td>
				</tr>
				<tr>
					<td></td>
					<td></td>
					<td class="title2 text-right"><?php echo $totalTime; ?></td>
					<td></td>
					<td></td>
					<td></td>
					<td class="title2 text-right"><?php echo sprintf('%.2f',$totalIn); ?></td>
					<td class="title2 text-right"><?php echo sprintf('%.2f',$totalOut); ?></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				<tr>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td class="title2 text-center" colspan="2"><?php echo sprintf('%.2f',$totalIn + $totalOut); ?></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
		</table>
		<br />
<?php
	} else {
		echo "getRadiusUserLogs: ".strSoapError($userLogs);
	}
}





# Check if we have an action
if (!isset($_REQUEST['frmaction'])) {
	# FIXME : redirect

} elseif ($_REQUEST['frmaction'] == "logs_main") {
	displaySearch($_REQUEST['user_id']);

} elseif ($_REQUEST['frmaction'] == "logs_final") {
	# Process search options
	if (isset($_REQUEST['searchLogsFrom'])) {
		$_SESSION['radiusUserLogs_searchFrom'] = $_REQUEST['searchLogsFrom'];
	}
	if (isset($_REQUEST['searchLogsTo'])) {
		$_SESSION['radiusUserLogs_searchTo'] = $_REQUEST['searchLogsTo'];
	}
	if (isset($_REQUEST['searchLogsOrderBy'])) {
		$_SESSION['radiusUserLogs_searchOrderBy'] = $_REQUEST['searchLogsOrderBy'];
	}

	# Setup search
	$searchOptions->searchFrom = isset($_SESSION['radiusUserLogs_searchFrom']) ? $_SESSION['radiusUserLogs_searchFrom'] : NULL;
	$searchOptions->searchTo = isset($_SESSION['radiusUserLogs_searchTo']) ? $_SESSION['radiusUserLogs_searchTo']  : NULL;
	$searchOptions->searchOrderBy = isset($_SESSION['radiusUserLogs_searchOrderBy']) ? $_SESSION['radiusUserLogs_searchOrderBy']  : NULL;

	displayLogs($_REQUEST['user_id'],$searchOptions);
}


# Menu footer
include("../shared-php/menu-footer.php");
# Footer
include("include/footer.php");

# vim: ts=4
?>
