<?php
# Policy member change
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
include_once("includes/tooltips.php");



$db = connect_db();



printHeader(array(
		"Tabs" => array(
			"Back to members" => "policy-main.php",
		),
));


# Display change screen
if ($_POST['frmaction'] == "change") {

	# Check a policy member was selected
	if (isset($_POST['attr_id'])) {
		# Prepare statement
		$temp = $_POST['attr_id'];
		$sql = "SELECT ID, Name, Operator, Value, Disabled FROM ${DB_TABLE_PREFIX}user_attributes WHERE ID = $temp";
		$res = $db->query($sql); 
		$row = $res->fetchObject();
?>
		<p class="pageheader">Update User</p>

		<form action="user-change.php" method="post">
			<div>
				<input type="hidden" name="frmaction" value="change2" />
				<input type="hidden" name="attr_id" value="<?php echo $_POST['attr_id']; ?>" />
			</div>
			<table class="entry" style="width: 75%;">
				<tr>
					<td></td>
					<td class="entrytitle textcenter">Old Value</td>
					<td class="entrytitle textcenter">New Value</td>
				</tr>
				<tr>
					<td class="entrytitle texttop">
						Name
						<?php tooltip('user_attributes_name'); ?>
					</td>
					<td class="oldval texttop"><?php echo $row->name ?></td>
					<td><textarea name="user_attributes_name" cols="40" rows="5"></textarea></td>
				</tr>
				<tr>
					<td class="entrytitle texttop">
						Operator
						<?php tooltip('user_attributes_operator'); ?>
					</td>
					<td class="oldval texttop"><?php echo $row->operator ?></td>
					<td><textarea name="user_attributes_operator" cols="40" rows="1"></textarea></td>
				</tr>
				<tr>
					<td class="entrytitle texttop">Value</td>
					<td class="oldval texttop"><?php echo $row->value ?></td>
					<td><textarea name="user_attributes_value" cols="40" rows="5"></textarea></td>
				</tr>
				<tr>
					<td class="entrytitle">Disabled</td>
					<td class="oldval"><?php echo $row->disabled ? 'yes' : 'no' ?></td>
					<td>
						<select name="user_attributes_disabled" />
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
} elseif ($_POST['frmaction'] == "change2") {
?>
	<p class="pageheader">Attribute Update Results</p>
<?php
	# Check a policy was selected
	if (isset($_POST['attr_id'])) {
		
		$updates = array();

		if (!empty($_POST['user_attributes_name'])) {
			array_push($updates,"Name = ".$db->quote($_POST['user_attributes_name']));
		}
		if (isset($_POST['user_attributes_operator']) && $_POST['user_attributes_operator'] != "") {
			array_push($updates,"Operator = ".$db->quote($_POST['user_attributes_operator']));
		}
		if (!empty($_POST['user_attributes_value'])) {
			array_push($updates,"Value = ".$db->quote($_POST['user_attributes_value']));
		}
		if (isset($_POST['user_attributes_disabled']) && $_POST['user_attributes_disabled'] != "") {
			array_push($updates ,"Disabled = ".$db->quote($_POST['user_attributes_disabled']));
		}

		# Check if we have updates
		if (sizeof($updates) > 0) {
			$updateStr = implode(', ',$updates);
	
			$res = $db->exec("UPDATE ${DB_TABLE_PREFIX}user_attributes SET $updateStr WHERE ID = ".$db->quote($_POST['attr_id']));
			if ($res) {
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

