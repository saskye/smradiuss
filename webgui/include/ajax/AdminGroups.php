<?php

include_once("include/db.php");


# Return list of users
function getAdminGroups($params) {

	$db = connect_db();

	$sql = "SELECT ID, Name, Priority, Disabled, Comment FROM groups";
	$res = $db->query($sql);

	$resultArray = array();

		# loop through rows
		while ($row = $res->fetchObject()) {
			$item = array();

			$item['ID'] = $row->id;
			$item['Name'] = $row->name;
			$item['Priority'] = $row->priority;
			$item['Disabled'] = $row->disabled;
			$item['Comment'] = $row->comment;

			# push this row onto array
			array_push($resultArray,$item);
		}

	# get number of rows
	$sql = "SELECT count(*) FROM groups";
	$res = $db->query($sql);
	$numResults = $res->fetchColumn();

	return array($numResults,$resultArray);
}

?>
