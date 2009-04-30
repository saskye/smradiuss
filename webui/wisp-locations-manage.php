<?php
# Radius Location List
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

# If we have no action, display list
if (!isset($_POST['frmaction']))
{
?>
	<p class="pageheader">Location List</p>

	<form id="main_form" action="wisp-locations-manage.php" method="post">
		<div class="textcenter">
			Action
			<select id="main_form_action" name="frmaction" 
					onchange="
						var myform = document.getElementById('main_form');
						var myobj = document.getElementById('main_form_action');

						if (myobj.selectedIndex == 2) {
							myform.action = 'wisp-locations-add.php';
						} else if (myobj.selectedIndex == 3) {
							myform.action = 'wisp-locations-delete.php';
						} else if (myobj.selectedIndex == 5) {
							myform.action = 'wisp-locations-members.php';
						}

						myform.submit();
					">
				<option selected="selected">select action</option>
				<option disabled="disabled"> - - - - - - - - - - - </option>
				<option value="add">Add Location</option>
				<option value="delete">Delete Location</option>
				<option disabled="disabled"> - - - - - - - - - - - </option>
				<option value="useratts">List Location Members</option>
			</select> 
		</div>

		<p />

		<table class="results" style="width: 75%;">
			<tr class="resultstitle">
				<td class="textcenter">ID</td>
				<td class="textcenter">Location</td>
			</tr>

<?php

			$sql = "SELECT Name FROM ${DB_TABLE_PREFIX}wisp_locations ORDER BY Name ASC";
			$res = $db->query($sql);

			# List users
			while ($row = $res->fetchObject()) {

?>

					<tr class="resultsitem">
						<td><input type="radio" name="location_id" value="<?php echo $row->id; ?>"/></td>
						<td><?php echo $row->name; ?></td>
					</tr>
<?php
			}
			if ($res->rowCount() == 0) {
?>
				<p />
				<tr>
					<td colspan="3" class="textcenter">Location list is empty</td>
				</tr>
<?php
			}
			$res->closeCursor();
?>
		</table>
	</form>
<?php
}
printFooter();

# vim: ts=4
?>
