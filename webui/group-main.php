<?php
# Radius Group List
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



include_once("includes/header.php");
include_once("includes/footer.php");
include_once("includes/db.php");



$db = connect_db();



printHeader(array(
));

?>

	<p class="pageheader">User Groups</p>

	<form id="main_form" action="group-main.php" method="post">

		<div class="textcenter">
			Action
			<select id="main_form_action" name="frmaction" 
					onchange="
						var myform = document.getElementById('main_form');
						var myobj = document.getElementById('main_form_action');

						if (myobj.selectedIndex == 2) {
							myform.action = 'group-add.php';
						} else if (myobj.selectedIndex == 3) {
							myform.action = 'group-delete.php';
						} else if (myobj.selectedIndex == 5) {
							myform.action = 'group-users.php';
						} else if (myobj.selectedIndex == 6) {
							myform.action = 'group-attributes.php';
						}

						myform.submit();
					">

				<option selected="selected">select action</option>
				<option disabled="disabled"> - - - - - - - - - - - </option>
				<option value="add">Add Group</option>
				<option value="delete">Delete Group</option>
				<option disabled="disabled"> - - - - - - - - - - - </option>
				<option value="members">List Users</option>
				<option value="members">List Attributes</option>
			</select> 
		</div>

		<p />

		<table class="results" style="width: 75%;">
			<tr class="resultstitle">
				<td class="textcenter">ID</td>
				<td class="textcenter">Name</td>
				<td class="textcenter">Priority</td>
				<td class="textcenter">Disabled</td>
				<td class="textcenter">Comment</td>
			</tr>

<?php

			$sql = "SELECT ID, Name, Priority, Disabled, Comment FROM ${DB_TABLE_PREFIX}groups ORDER BY ID";
			$res = $db->query($sql);

			while ($row = $res->fetchObject()) {

?>
				<tr class="resultsitem">
					<td><input type="radio" name="group_id" value="<?php echo $row->id; ?>" /></td>
					<td><?php echo $row->name; ?></td>
					<td><?php echo $row->priority; ?></td>
					<td class="textcenter"><?php echo $row->disabled ? 'yes' : 'no'; ?></td>
					<td><?php echo $row->comment; ?></td>
				</tr>

<?php

			}
			if ($res->rowCount() == 0) {

?>

				<p />
				<tr>
					<td colspan="5" class="textcenter">Group list is empty</td>
				</tr>

<?php

			}
			$res->closeCursor();

?>

		</table>
	</form>

<?php

printFooter();

# vim: ts=4
?>
