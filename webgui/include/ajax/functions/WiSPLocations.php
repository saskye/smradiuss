<?php

include_once("include/db.php");


# Return list of locations
function getWiSPLocations($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => 'wisp_locations.ID',
		'Name' => 'wisp_locations.Name'
	);

	# Perform query
	$res = DBSelectSearch("SELECT ID, Name FROM wisp_locations",$params[1],$filtersorts,$filtersorts);
	$sth = $res[0]; $numResults = $res[1];

	# If STH is blank, return the error back to whoever requested the data
	if (!isset($sth)) {
		return $res;
	}

	# Loop through rows
	$resultArray = array();
	while ($row = $sth->fetchObject()) {

		# Build array for this row
		$item = array();

		$item['ID'] = $row->id;
		$item['Name'] = $row->name;

		# Push this row onto array
		array_push($resultArray,$item);
	}

	# Return results
	return array($resultArray,$numResults);
}

# Return specific location row
function getWiSPLocation($params) {

	# Perform query
	$res = DBSelect("SELECT ID, Name FROM wisp_locations WHERE ID = ?",array($params[0]));

	# Return if error or nothing to return
	if (!is_object($res)) {
		return $res;
	}

	# Build array of results
	$resultArray = array();
	$row = $res->fetchObject();

	$resultArray['ID'] = $row->id;
	$resultArray['Name'] = $row->name;

	# Return results
	return $resultArray;
}

# Remove wisp location
function removeWiSPLocation($params) {

	# Begin transaction
	DBBegin();

	# Unlink users from this location
	$res = DBDo("UPDATE wisp_userdata SET LocationID = NULL WHERE LocationID = ?",array($params[0]));

	# Delete location
	if ($res !== FALSE) {
		$res = DBDo("DELETE FROM wisp_locations WHERE ID = ?",array($params[0]));
	}

	# Return result
	if ($res !== TRUE) {
		DBRollback();
		return $res;
	} else {
		DBCommit();
	}

	return NULL;
}

# Add wisp location
function createWiSPLocation($params) {

	# Perform query
	$res = DBDo("INSERT INTO wisp_locations (Name) VALUES (?)",array($params[0]['Name']));

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# Edit wisp location
function updateWiSPLocation($params) {

	# Perform query
	$res = DBDo("UPDATE wisp_locations SET Name = ? WHERE ID = ?",array($params[0]['Name'],$params[0]['ID']));

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# vim: ts=4
