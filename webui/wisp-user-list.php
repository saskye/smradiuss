<?php
# Radius User List
# Copyright (C) 2008-2009, AllWorldIT
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

# If we have nothing to do - display search
if (!isset($_POST['frmaction'])) {

?>

	<p class="pageheader">User List</p>

	<form id="main_form" action="wisp-user-list.php" method="post">
		<input type="hidden" name="frmaction" value="dofilter" />
		<table class="entry" style="width: 80%;">
			<tr>
				<td>Sort by:</td>
				<td colspan="4">
					<input type="radio" name="sortby" value="id">ID</input>
					<input type="radio" name="sortby" value="fname">First Name</input>
					<input type="radio" name="sortby" value="lname">Last Name</input>
					<input type="radio" name="sortby" value="uname">Username</input>
				</td>
			</tr>
			<tr>
				<td>Username:</td>
				<td><input type="text" name="username" /></td>
				<td>First Name:</td>
				<td><input type="text" name="firstname" /></td>
			</tr>
			<tr>
				<td>Last Name:</td>
				<td><input type="text" name="lastname" /></td>
				<td>Phone:</td>
				<td><input type="text" name="phone" /></td>
			</tr>
			<tr>
				<td>Location:</td>
				<td><input type="text" name="location" /></td>
				<td>Email:</td>
				<td><input type="text" name="email" /></td>
			</tr>
			<tr>
				<td>Pool Name:</td>
				<td><input type="text" name="poolname" /></td>
				<td>Group:</td>
				<td><input type="text" name="group" /></td>
			</tr>
			<tr>
				<td class="textcenter" colspan="5"><input type="submit" value="Submit" /></td>
			</tr>
		</table>
	</form>

<?php

}

if ($_POST['frmaction'] == "dofilter") {

?>

	<form id="main_form" action="wisp-user-list.php" method="post">

		<div class="textcenter">
			Action
			<select id="main_form_action" name="frmaction" 
					onchange="
						var myform = document.getElementById('main_form');
						var myobj = document.getElementById('main_form_action');

						if (myobj.selectedIndex == 2) {
							myform.action = 'wisp-user-edit.php';
						} else if (myobj.selectedIndex == 3) {
							myform.action = 'wisp-user-delete.php';
						}

						myform.submit();
					">
				<option selected="selected">select action</option>
				<option disabled="disabled"> - - - - - - - - - - - </option>
				<option value="edit">Edit User</option>
				<option value="delete">Remove User</option>
			</select> 
		</div>

		<p />

		<table class="results" style="width: 75%;">
			<tr class="resultstitle">
				<td class="textcenter">ID</td>
				<td class="textcenter">Username</td>
				<td class="textcenter">FirstName</td>
				<td class="textcenter">LastName</td>
				<td class="textcenter">Data</td>
				<td class="textcenter">Time</td>
				<td class="textcenter">Email</td>
				<td class="textcenter">Phone</td>
			</tr>

<?php

			# Additions to the SQL statement
			$extraSQLVals = array();
			$extraSQL = "";
			$orderSQL = "";

			# What searches are we going to do?
			if ($_POST['username']) {
				$extraSQL = " AND Username LIKE ?";
				array_push($extraSQLVals,"%".$_POST['username']."%");
			}
			if ($_POST['firstname']) {
				$extraSQL = " AND FirstName LIKE ?";
				array_push($extraSQLVals,"%".$_POST['firstname']."%");
			}
			if ($_POST['lastname']) {
				$extraSQL = " AND LastName LIKE ?";
				array_push($extraSQLVals,"%".$_POST['lastname']."%");
			}
			if ($_POST['phone']) {
				$extraSQL = " AND Phone LIKE ?";
				array_push($extraSQLVals,"%".$_POST['phone']."%");
			}
			if ($_POST['location']) {
				$extraSQL = " AND Location LIKE ?";
				array_push($extraSQLVals,"%".$_POST['location']."%");
			}
			if ($_POST['email']) {
				$extraSQL = " AND Email LIKE ?";
				array_push($extraSQLVals,"%".$_POST['email']."%");
			}
			if ($_POST['poolname']) {
				$extraSQL = " AND PoolName LIKE ?";
				array_push($extraSQLVals,"%".$_POST['poolname']."%");
			}
			if ($_POST['group']) {
				$extraSQL = " AND GroupName LIKE ?";
				array_push($extraSQLVals,"%".$_POST['group']."%");
			}

			# How are we sorting the results?
			switch ($_POST['sortby']) {
				case "id":
					$sortSQL = " ORDER BY ID";
					break;
				case "fname":
					$sortSQL = " ORDER BY FirstName";
					break;
				case "lname":
					$sortSQL = " ORDER BY LastName";
					break;
				case "uname":
					$sortSQL = " ORDER BY Username";
					break;
			}

			# Query based on user input
			$sql = "
				SELECT
						ID, 
						Username,
						FirstName,
						LastName,
						Data, 
						Time, 
						Email, 
						Phone 
				FROM 
						wispusers 
				WHERE 
						1 = 1
						$extraSQL
						$sortSQL
				";

			$res = $db->prepare($sql);
			$res->execute($extraSQLVals);

			#$totalInputData = 0;
			#$totalOutputData = 0;
			#$totalSessionTime = 0;

			# List users
			$rownums = 0;
			while ($row = $res->fetchObject()) {

				# If there was nothing returned we want to know about it
				if ($row->id != NULL) {
					$rownums = $rownums + 1;
				} else {
					$rownums = $rownums - 1;
				}

				# Data usage
				# ==========

				# Input
				#$inputDataItem = 0;
				#
				#if (!empty($row->acctinputoctets) && $row->acctinputoctets > 0) {
				#	$inputDataItem = ($row->accinputoctets / 1024 / 1024);
				#}
				#if (!empty($row->acctinputgigawords) && $row->inputgigawords > 0) {
				#	$inputDataItem = ($row->acctinputgigawords * 4096);
				#}
				#if ($inputDataItem != 0) {
				#	$inputDataItemDisplay = ceil($inputDataItem * 100)/100;
				#} else {
				#	$inputDataItemDisplay = 0;
				#}
				#
				#$totalInputData = $totalInputData + $inputDataItem;
				#
				# Output
				#$outputDataItem = 0;
				#
				#if (!empty($row->acctoutputoctets) && $row->acctoutputoctets > 0) {
				#	$outputDataItem = ($row->acctoutputoctets / 1024 / 1024);
				#}
				#if (!empty($row->acctoutputgigawords) && $row->acctoutputgigawords > 0) {
				#	$outputDataItem = ($row->acctoutputgigawords * 4096);
				#}
				#if ($outputDataItem != 0) {
				#	$outputDataItem = ceil($outputDataItem * 100)/100;
				#} else {
				#	$outputDataItem = 0;
				#}
				#
				#$totalOutputData = $totalOutputData + $outputDataItem;
				#
				# Add up time
				#if (!empty($row->acctsessiontime) && $row->acctsessiontime > 0) {
				#	$sessionTimeItem = $row->acctsessiontime / 60;
				#	$sessionTimeItem = ceil($sessionTimeItem * 100)/100;
				#}
				#
				#$totalSessionTime = $totalSessionTime + $sessionTimeItem;
				#$totalSessionTime = ceil($totalSessionTime * 100)/100;

?>		

				<tr class="resultsitem">
					<td><input type="radio" name="user_id" value="<?php echo $row->id ?>"/><?php echo $row->id ?></td>
					<td><?php echo $row->username ?></td>
					<td><?php echo $row->firstname ?></td>
					<td><?php echo $row->lastname ?></td>
					<td><?php echo $row->data ?></td>
					<td><?php echo $row->time ?></td>
					<td><?php echo $row->email ?></td>
					<td><?php echo $row->phone ?></td>
				</tr>

<?php

			}
			$res->closeCursor();

			# If there were no rows, complain
			if ($rownums <= 0) {

?>

				<p />
				<tr>
					<td colspan="3" class="textcenter">No users found</td>
				</tr>

<?php

			}

?>

		</table>
	</form>

<?php

}
printFooter();

# vim: ts=4
?>
