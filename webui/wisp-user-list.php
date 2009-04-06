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
						} else if (myobj.selectedIndex == 5) {
							myform.action = 'wisp-user-logs.php';
						}

						myform.submit();
					">
				<option selected="selected">select action</option>
				<option disabled="disabled"> - - - - - - - - - - - </option>
				<option value="edit">Edit User</option>
				<option value="delete">Remove User</option>
				<option disabled="disabled"> - - - - - - - - - - - </option>
				<option value="viewlogs">View User Logs</option>
			</select> 
		</div>

		<p />

		<table class="results">
			<tr class="resultstitle">
				<td class="textcenter">ID</td>
				<td class="textcenter">Username</td>
				<td class="textcenter">FirstName</td>
				<td class="textcenter">LastName</td>
				<td class="textcenter">Email</td>
				<td class="textcenter">Phone</td>
				<td class="textcenter">Location</td>
				<td class="textcenter">Data Cap</td>
				<td class="textcenter">Time Cap</td>
				<td class="textcenter">IP Address</td>
			</tr>

<?php

			# Additions to the SQL statement
			$extraSQLVals = array();
			$extraSQL = "";
			$orderSQL = "";

			# What searches are we going to do?
			if ($_POST['username']) {
				$extraSQL = " AND users.Username LIKE ?";
				array_push($extraSQLVals,"%".$_POST['username']."%");
			}
			if ($_POST['firstname']) {
				$extraSQL = " AND userdata.FirstName LIKE ?";
				array_push($extraSQLVals,"%".$_POST['firstname']."%");
			}
			if ($_POST['lastname']) {
				$extraSQL = " AND userdata.LastName LIKE ?";
				array_push($extraSQLVals,"%".$_POST['lastname']."%");
			}
			if ($_POST['phone']) {
				$extraSQL = " AND userdata.Phone LIKE ?";
				array_push($extraSQLVals,"%".$_POST['phone']."%");
			}
			if ($_POST['location']) {
				$extraSQL = " AND userdata.Location LIKE ?";
				array_push($extraSQLVals,"%".$_POST['location']."%");
			}
			if ($_POST['email']) {
				$extraSQL = " AND userdata.Email LIKE ?";
				array_push($extraSQLVals,"%".$_POST['email']."%");
			}

			# How are we sorting the results?
			switch ($_POST['sortby']) {
				case "id":
					$sortSQL = " ORDER BY users.ID";
					break;
				case "fname":
					$sortSQL = " ORDER BY userdata.FirstName";
					break;
				case "lname":
					$sortSQL = " ORDER BY userdata.LastName";
					break;
				case "uname":
					$sortSQL = " ORDER BY users.Username";
					break;
			}

			# Query based on user input
			$sql = "
				SELECT
						users.ID, 
						users.Username,
						userdata.UserID,
						userdata.FirstName,
						userdata.LastName,
						userdata.Email, 
						userdata.Phone,
						userdata.Location
				FROM 
						users, userdata
				WHERE 
						users.ID = userdata.UserID
						$extraSQL
						$sortSQL
				";

			$res = $db->prepare($sql);
			$res->execute($extraSQLVals);

			# List users
			$rownums = 0;
			while ($row = $res->fetchObject()) {
				
				# If there was nothing returned we want to know about it
				if ($row->id != NULL) {
					$rownums = $rownums + 1;
				} else {
					$rownums = $rownums - 1;
				}


				# Second dirty query to get user's attributes
				$tempUserID = $row->id;
				$attrQuery = "
						SELECT
								Name,
								Value
						FROM
								user_attributes
						WHERE
								UserID = $tempUserID
						";
				
				$dataCap = NULL;
				$timeCap = NULL;
				$userIP = NULL;
				$attrResult = $db->query($attrQuery);
				while ($attrRow = $attrResult->fetchObject()) {
					# Is it the data cap attribute
					if ($attrRow->name == "SMRadius-Capping-Traffic-Limit") {
						$dataCap = $attrRow->value;
					}
					# Or the time cap attribute
					if ($attrRow->name == "SMRadius-Capping-Time-Limit") {
						$timeCap = $attrRow->value;
					}
					# Or the user IP attribute
					if ($attrRow->name == "Framed-IP-Address") {
						$userIP = $attrRow->value;
					}
				}
				$attrResult->closeCursor();

?>		

				<tr class="resultsitem">
					<td><input type="radio" name="user_id" value="<?php echo $row->id ?>"/><?php echo $row->id ?></td>
					<td><?php echo $row->username ?></td>
					<td><?php echo $row->firstname ?></td>
					<td><?php echo $row->lastname ?></td>
					<td><?php echo $row->email ?></td>
					<td><?php echo $row->phone ?></td>
					<td><?php echo $row->location ?></td>
					<td><?php echo $dataCap ?> MB</td>
					<td><?php echo $timeCap ?> Min</td>
					<td><?php echo $userIP ?></td>
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
