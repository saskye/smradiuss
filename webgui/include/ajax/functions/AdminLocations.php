<?php

include_once("include/db.php");


# Return list of users
function getAdminLocations($params) {
	global $db;

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => 'wisp_locations.ID',
		'Name' => 'wisp_locations.Name',
	);

	$res = DBSelectSearch("SELECT ID, Name FROM wisp_locations",$params[1],$filtersorts,$filtersorts);
	$sth = $res[0]; $numResults = $res[1];
	# If STH is blank, return the error back to whoever requested the data
	if (!isset($sth)) {
		return $res;
	}

	$resultArray = array();

	# loop through rows
	while ($row = $sth->fetchObject()) {
		$item = array();

		$item['ID'] = $row->id;
		$item['Name'] = $row->name;

		# push this row onto array
		array_push($resultArray,$item);
	}

	return array($resultArray,$numResults);
}

?>
