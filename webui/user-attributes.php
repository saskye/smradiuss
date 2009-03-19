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


session_start();

include_once("includes/header.php");
include_once("includes/footer.php");
include_once("includes/db.php");



$db = connect_db();



printHeader(array(
		"Tabs" => array(
			"Back to user list" => "policy-main.php"
		),
));

?>
<p class="pageheader">Attribute List</p>

<form id="main_form" action="user-main.php" method="post">

	<div class="textcenter">
		Action
		<select id="main_form_action" name="frmaction" 
				onchange="
					var myform = document.getElementById('main_form');
					var myobj = document.getElementById('main_form_action');

					if (myobj.selectedIndex == 2) {
						myform.action = 'attribute-add.php';
					} else if (myobj.selectedIndex == 5) {
						myform.action = 'attribute-change.php';
					} else if (myobj.selectedIndex == 3) {
						myform.action = 'attribute-delete.php';
					}

					myform.submit();
				">
			 
			<option selected="selected">select action</option>
			<option disabled="disabled"> - - - - - - - - - - - </option>
			<option value="add">Add</option>
			<option value="delete">Delete</option>
			<option disabled="disabled"> - - - - - - - - - - - </option>
			<option value="change">Change</option>
		</select> 
	</div>

<p />

	<table class="results" style="width: 75%;">
		<tr class="resultstitle">
			<td class="textcenter">ID</td>
			<td class="textcenter">Name</td>
			<td class="textcenter">Operator</td>
			<td class="textcenter">Value</td>
			<td class="textcenter">Disabled</td>
		</tr>
<?php
$_SESSION['attr_user_id'] = $_POST['user_id']; 
if (isset($_SESSION['attr_user_id'])) {
	
	$temp = $_SESSION['attr_user_id'];
	$sql = "SELECT ID, Name, Operator, Value, Disabled FROM ${DB_TABLE_PREFIX}user_attributes WHERE UserID = $temp ORDER BY ID";
	$res = $db->query($sql);

	while ($row = $res->fetchObject()) {
?>
		<tr class="resultsitem">
			<td><input type="radio" name="attr_id" value="<?php echo $row->id ?>"/><?php echo $row->id ?></td>
			<td><?php echo $row->name ?></td>
			<td><?php echo $row->operator ?></td>
			<td><?php echo $row->value ?></td>
			<td class="textcenter"><?php echo $row->disabled ? 'yes' : 'no' ?></td>
		</tr>
<?php
	}
?>
	</table>
</form>
<?php
	$res->closeCursor();
?>
<?php
} else {
?>
	<div class="warning">Invocation error, no user ID selected</div>
<?php
}

?>
<?php

printFooter();


# vim: ts=4
?>