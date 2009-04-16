<?php
# Policy add
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
include_once("includes/tooltips.php");


$db = connect_db();


printHeader(array(
		"Tabs" => array(
			"Back to user list" => "group-main.php"
		),
));


if (isset($_POST['frmaction']) && $_POST['frmaction'] == "add") {

?>

	<p class="pageheader">Add attribute</p>

	<form method="post" action="group-attribute-add.php">
		<input type="hidden" name="frmaction" value="add2" />
		<table class="entry">
			<tr>
				<td class="entrytitle">Attribute Name</td>
				<td><input type="text" name="attr_name" /></td>
			</tr>
			<tr>
				<td class="entrytitle">Operator</td>
				<td>
					<select name="attr_operator">
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
				<td class="entrytitle">Value</td>
				<td><input type="text" name="attr_value" /></td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="submit" />
				</td>
			</tr>
		</table>
	</form>
<?php

# Check we have all params
} elseif (isset($_POST['frmaction']) && $_POST['frmaction'] == "add2") {

?>

	<p class="pageheader">Attribute Add Results</p>

<?php

	# Check for empty values
	if (empty($_POST['attr_name']) || empty($_POST['attr_operator']) || empty($_POST['attr_value'])) {

?>

		<div class="warning">Submission cannot have empty value</div>

<?php

	} else {
		$stmt = $db->prepare("INSERT INTO ${DB_TABLE_PREFIX}group_attributes (GroupID,Name,Operator,Value) VALUES (?,?,?,?)");
		# Which user am I working with?
		$attr_group_id = $_SESSION['attr_group_id']; 

		$res = $stmt->execute(array(
			$attr_group_id,
			$_POST['attr_name'],
			$_POST['attr_operator'],
			$_POST['attr_value'],
		));
		if ($res) {

?>

			<div class="notice">Attribute added</div>

<?php

			session_destroy();
		} else {

?>

			<div class="warning">Failed to add attribute</div>
			<div class="warning"><?php print_r($stmt->errorInfo()) ?></div>

<?php

		}
	}
} else {

?>

	<div class="warning">Invalid invocation</div>

<?php

}
printFooter();

# vim: ts=4
?>
