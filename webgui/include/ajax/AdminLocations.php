<?php

include_once("include/db.php");


# Return list of users
function getAdminLocations($params) {

	$db = connect_db();

	$sql = "SELECT ID, Name FROM wisp_locations";
	$res = $db->query($sql);

	$resultArray = array();

		# loop through rows
		while ($row = $res->fetchObject()) {
			$item = array();

			$item['ID'] = $row->id;
			$item['Name'] = $row->name;

			# push this row onto array
			array_push($resultArray,$item);
		}

	# get number of rows
	$sql = "SELECT count(*) FROM wisp_locations";
	$res = $db->query($sql);
	$numResults = $res->fetchColumn();

	return array($numResults,$resultArray);
}

?>
