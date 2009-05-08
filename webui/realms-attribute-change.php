<?php
# Radius Realms Attribute Change
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
		"Tabs" => array(
			"Back to realm list" => "realms-main.php",
		),
));

# Display change screen
if (isset($_POST['frmaction']) && $_POST['frmaction'] == "change") {

	# Check an attribute was selected
	if (isset($_POST['attr_id'])) {
		# Prepare statement
		$sql = "SELECT ID, Name, Operator, Value, Disabled FROM ${DB_TABLE_PREFIX}realm_attributes WHERE ID = ".$db->quote($_POST['attr_id']);
		$res = $db->query($sql); 
		$row = $res->fetchObject();
?>
		<p class="pageheader">Update Realm Attribute</p>

		<form action="realms-attribute-change.php" method="post">
			<input type="hidden" name="frmaction" value="change2" />
			<input type="hidden" name="attr_id" value="<?php echo $_POST['attr_id']; ?>" />
			<table class="entry" style="width: 75%;">
				<tr>
					<td></td>
					<td class="entrytitle textcenter">Old Value</td>
					<td class="entrytitle textcenter">New Value</td>
				</tr>
				<tr>
					<td class="entrytitle texttop">
						Name
					</td>
					<td class="oldval texttop"><?php echo $row->name; ?></td>
					<td><textarea name="realm_attributes_name" cols="40" rows="1"></textarea></td>
				</tr>
				<tr>
					<td class="entrytitle texttop">
						Operator
					</td>
					<td class="oldval texttop"><?php echo $row->operator; ?></td>
					<td>
						<select name="realm_attributes_operator">
							<option value="=">=</option>
							<option value="==">==</option>
							<option value=":=">:=</option>
							<option value="+=">+=</option>
							<option value="!=">!=</option>
							<option value=">">&gt;</option>
							<option value="<">&lt;</option>
							<option value=">=">&gt;=</option>
							<option value="<=">&lt;=</option>
							<option value="=~">=~</option>
							<option value="!~">!~</option>
							<option value="=*">=*</option>
							<option value="!*">!*</option>
							<option value="||=">||=</option>
							<option value="||==">||==</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="entrytitle texttop">Value</td>
					<td class="oldval texttop"><?php echo $row->value; ?></td>
					<td><textarea name="realm_attributes_value" cols="40" rows="5"></textarea></td>
				</tr>
				<tr>
					<td class="entrytitle">Disabled</td>
					<td class="oldval"><?php echo $row->disabled ? 'yes' : 'no'; ?></td>
					<td>
						<select name="realm_attributes_disabled" />
							<option value="">--</option>
							<option value="0">No</option>
							<option value="1">Yes</option>
						</select>
					</td>
				</tr>
			</table>

			<p />

			<div class="textcenter">
				<input type="submit" />
			</div>
		</form>
<?php

		$res->closeCursor();

	} else {
?>
		<div class="warning">No attribute selected</div>
<?php
	}

# SQL Updates
} elseif (isset($_POST['frmaction']) && $_POST['frmaction'] == "change2") {

?>
	<p class="pageheader">Attribute Update Results</p>
<?php

	# Check an attribute was selected
	if (isset($_POST['attr_id'])) {

		$updates = array();

		if (!empty($_POST['realm_attributes_name'])) {
			array_push($updates,"Name = ".$db->quote($_POST['realm_attributes_name']));
		}
		if (isset($_POST['realm_attributes_operator']) && $_POST['realm_attributes_operator'] != "") {
			array_push($updates,"Operator = ".$db->quote($_POST['realm_attributes_operator']));
		}
		if (!empty($_POST['realm_attributes_value'])) {
			array_push($updates,"Value = ".$db->quote($_POST['realm_attributes_value']));
		}
		if (isset($_POST['realm_attributes_disabled']) && $_POST['realm_attributes_disabled'] != "") {
			array_push($updates ,"Disabled = ".$db->quote($_POST['realm_attributes_disabled']));
		}

		# Check if we have updates
		if (sizeof($updates) > 0) {
			$updateStr = implode(', ',$updates);

			$res = $db->exec("
				UPDATE 
					${DB_TABLE_PREFIX}realm_attributes 
				SET 
					$updateStr 
				WHERE 
					ID = ".$db->quote($_POST['attr_id']."
			"));
			if ($res !== FALSE) {
?>
				<div class="notice">Attribute updated</div>
<?php
			} else {
?>
				<div class="warning">Error updating attribute</div>
				<div class="warning"><?php print_r($db->errorInfo()) ?></div>
<?php
			}

		# Warn
		} else {
?>
			<div class="warning">No attribute updates</div>
<?php
		}

	# Warn
	} else {
?>
		<div class="error">No attribute data available</div>
<?php
	}

} else {
?>
	<div class="warning">Invalid invocation</div>
<?php
}


printFooter();


# vim: ts=4
?>