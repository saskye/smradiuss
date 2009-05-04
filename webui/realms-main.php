<?php
# Radius Realms Main
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

<p class="pageheader">Realms</p>

<form id="main_form" action="realms-main.php" method="post">

	<div class="textcenter">
		Action
		<select id="main_form_action" name="frmaction" 
				onchange="
					var myform = document.getElementById('main_form');
					var myobj = document.getElementById('main_form_action');

					if (myobj.selectedIndex == 2) {
						myform.action = 'realms-add.php';
					} else if (myobj.selectedIndex == 3) {
						myform.action = 'realms-delete.php';
					} else if (myobj.selectedIndex == 5) {
						myform.action = 'realms-attributes.php';
					}

					myform.submit();
				">

			<option selected="selected">select action</option>
			<option disabled="disabled"> - - - - - - - - - - - </option>
			<option value="add">Add Realm</option>
			<option value="delete">Delete Realm</option>
			<option disabled="disabled"> - - - - - - - - - - - </option>
			<option value="members">Attributes</option>
		</select> 
	</div>

	<p />

	<table class="results" style="width: 75%;">
		<tr class="resultstitle">
			<td class="textcenter">ID</td>
			<td class="textcenter">Name</td>
			<td class="textcenter">Disabled</td>
		</tr>

<?php
		$sql = "SELECT ID, Name, Disabled FROM ${DB_TABLE_PREFIX}realms ORDER BY ID";
		$res = $db->query($sql);

		if ($res->rowCount() > 0) {
			while ($row = $res->fetchObject()) {
?>
				<tr class="resultsitem">
					<td><input type="radio" name="realms_id" value="<?php echo $row->id; ?>" /></td>
					<td><?php echo $row->name; ?></td>
					<td class="textcenter"><?php echo $row->disabled ? 'yes' : 'no'; ?></td>
				</tr>
<?php
			}
		} else {

?>
			<p />
			<tr>
				<td colspan="5" class="textcenter">Realms list is empty</td>
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
