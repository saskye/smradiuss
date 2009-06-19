<?php

include_once("include/db.php");


# Return list of locations
function getWiSPLocations($params) {
	global $db;

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => 'wisp_locations.ID',
		'Name' => 'wisp_locations.Name'
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

# Return specific location row
function getWiSPLocation($params) {
	global $db;


	$res = DBSelect("SELECT ID, Name FROM wisp_locations WHERE ID = ?",array($params[0]));
	if (!is_object($res)) {
		return $res;
	}

	$resultArray = array();

	$row = $res->fetchObject();

	$resultArray['ID'] = $row->id;
	$resultArray['Name'] = $row->name;

	return $resultArray;
}

# Remove admin group
function removeWiSPLocation($params) {
	global $db;

	# Begin transaction
	DBBegin();

	# Unlink users from this location
	$res = DBDo("UPDATE wisp_userdata SET LocationID = NULL WHERE LocationID = ?",array($params[0][0]));

	# Delete location
	if ($res !== FALSE) {
		$res = DBDo("DELETE FROM wisp_locations WHERE ID = ?",array($params[0][0]));
	}

	# Commit changes if successful
	if ($res !== FALSE) {
		DBCommit();
		return $res;
	# Rollback database if error
	} else {
		DBRollback();
	}

	return NULL;
}

# Add admin group
function createWiSPLocation($params) {
	global $db;

	$res = DBDo("INSERT INTO wisp_locations (Name) VALUES (?)",array($params[0]['Name']));
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# Edit admin group
function updateWiSPLocation($params) {
	global $db;

	$res = DBDo("UPDATE wisp_locations SET Name = ? WHERE ID = ?",array($params[0]['Name'],$params[0]['ID']));
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# vim: ts=4
