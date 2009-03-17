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

# Soap functions
require_once("soapfuncs.php");

# Radius functions
require_once("radiuscodes.php");



# NB: We will only end up here if we authenticated!


# Display settings
function displayLogs() {
	global $soap;


	# Check if we should search
	if (isset($_POST['searchFrom']) || isset($_POST['searchFrom'])) {

		if (isset($_POST['searchFrom'])) {
			$search['searchFrom'] = isset($_POST['searchFrom']) ? $_POST['searchFrom'] : '';
			$_SESSION['radiusLogs_searchFrom'] = $search['searchFrom'];
		}
		if (isset($_POST['searchTo'])) {
			$search['searchTo'] = isset($_POST['searchTo']) ? $_POST['searchTo'] : '';
			$_SESSION['radiusLogs_searchTo'] = $search['searchTo'];
		}

		$results = $soap->getRadiusUserLogs($search);
	}
	
	$userDetails = $soap->getRadiusUserDetails();
	$isDialup = preg_match('/dialup/i',$userDetails->Service);
?>

	<table class="blockcenter" width="750">
		<tr>
			<td colspan="4" class="title">
				<form method="POST">
					<p class="middle center">
					Display logs between
					<input type="text" name="searchFrom" value="<?php 
							if (isset($_SESSION['radiusLogs_searchFrom'])) {
								echo $_SESSION['radiusLogs_searchFrom'];
							}
					?>" size="11"> 
					and 
					<input type="text" name="searchTo" value="<?php 
							if (isset($_SESSION['radiusLogs_searchTo'])) {
								echo $_SESSION['radiusLogs_searchTo'];
							}
					?>" size="11">

					<input type="submit" value="search">
					</p>
				</form>
			</td>
			<td colspan="2" class="section">Connect Speed</td>
			<td colspan="2" class="section">Traffic Usage<br> (Mbyte)</td>
		</tr>
		<tr>
			<td class="section">Timestamp</td>
			<td class="section">Duration</td>
<?php
			if (!$isDialup) {
?>
				<td class="section">Port</td>
<?php
			} else {
?>
				<td class="section">Caller ID</td>
<?php
			}
?>
			<td class="section">Term Reason</td>
			<td class="section">Receive</td>
			<td class="section">Transmit</td>
			<td class="section">Upload</td>
			<td class="section">Download</td>
		</tr>
<?php
		if (isset($results) && is_array($results)) {
			$total = 0;
			$totalUpload = 0;
			$totalDownload = 0;

			# Loop with log entries
			foreach ($results as $item) {
				$inputMBytes = $item->AcctInputOctets > 0 ? $item->AcctInputOctets / 1024 / 1024 : 0;
				$outputMBytes = $item->AcctOutputOctets > 0 ? $item->AcctOutputOctets / 1024 / 1024 : 0;
				$inputMBytes += $item->AcctInputGigawords * 4096;
				$outputMBytes += $item->AcctOutputGigawords * 4096;
?>
				<tr>
					<td class="desc"><?php echo $item->Timestamp; ?>
					<td class="desc"><?php echo $item->AcctSessionTime; ?></td>
<?php
					if (!$isDialup) {
?>
						<td class="desc"><?php echo $item->NASPort; ?></td>
<?php
					} else {
?>
						<td class="desc"><?php echo $item->CallingStationID; ?></td>
<?php
					}
?>
					<td class="center desc"><?php echo strRadiusTermCode($item->ConnectTermReason); ?></td>
					<td class="center desc"><?php echo $item->NASTransmitRate; ?></td>
					<td class="center desc"><?php echo $item->NASReceiveRate; ?></td>
					<td class="right desc"><?php echo sprintf('%.2f',$inputMBytes); ?></td>
					<td class="right desc"><?php echo sprintf('%.2f',$outputMBytes); ?></td>
				</tr>
<?php
				$totalUpload += $inputMBytes;
				$totalDownload += $outputMBytes;
			}

			$total = $totalUpload + $totalDownload;
?>
		<tr>
			<td colspan="6" class="right">Sub Total:</td>
			<td class="right desc"><?php echo sprintf('%.2f',$totalUpload); ?></td>
			<td class="right desc"><?php echo sprintf('%.2f',$totalDownload); ?></td>
		</tr>
		<tr>
			<td colspan="6" class="right">Total:</td>
			<td colspan="2" class="center desc"><?php echo sprintf('%.2f',$total); ?></td>
		</tr>
<?php
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
?>
