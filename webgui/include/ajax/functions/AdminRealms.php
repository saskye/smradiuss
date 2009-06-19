<?php

include_once("include/db.php");


# Return list of realms
function getAdminRealms($params) {
	global $db;

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => 'realms.ID',
		'Name' => 'realms.Name',
		'Disabled' => 'realms.Disabled'
	);

	$res = DBSelectSearch("SELECT ID, Name, Disabled FROM realms",$params[1],$filtersorts,$filtersorts);
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
		$item['Disabled'] = $row->disabled;

		# push this row onto array
		array_push($resultArray,$item);
	}

	return array($resultArray,$numResults);
}

# Return specific realm row
function getAdminRealm($params) {
	global $db;


	$res = DBSelect("SELECT ID, Name, Disabled FROM realms WHERE ID = ?",array($params[0]));
	if (!is_object($res)) {
		return $res;
	}

	$resultArray = array();

	$row = $res->fetchObject();

	$resultArray['ID'] = $row->id;
	$resultArray['Name'] = $row->name;
	$resultArray['Disabled'] = $row->disabled;

	return $resultArray;
}

# Remove admin realm
function removeAdminRealm($params) {
	global $db;

	$res = DBDo("DELETE FROM realms WHERE ID = ?",array($params[0]));
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# Add admin realm
function createAdminRealm($params) {
	global $db;

	$res = DBDo("INSERT INTO realms (Name) VALUES (?)",array($params[0]['Name']));
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# Edit admin realm
function updateAdminRealm($params) {
	global $db;

	$res = DBDo("UPDATE realms SET Name = ? WHERE ID = ?",array($params[0]['Name'],$params[0]['ID']));
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# vim: ts=4
