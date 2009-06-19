<?php

include_once("include/db.php");


# Return list of users
function getAdminUsers($params) {
	global $db;

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => 'users.ID',
		'Username' => 'users.Username',
		'Disabled' => 'users.Disabled',
	);

	$res = DBSelectSearch("SELECT ID, Username, Disabled FROM users",$params[1],$filtersorts,$filtersorts);
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
		$item['Username'] = $row->username;
		$item['Disabled'] = $row->disabled;

		# push this row onto array
		array_push($resultArray,$item);
	}

	return array($resultArray,$numResults);
}

# Return specific group row
function getAdminUser($params) {
	global $db;


	$res = DBSelect("SELECT ID, Username, Disabled FROM users WHERE ID = ?",array($params[0]));
	if (!is_object($res)) {
		return $res;
	}

	$resultArray = array();

	$row = $res->fetchObject();

	$resultArray['ID'] = $row->id;
	$resultArray['Username'] = $row->username;
	$resultArray['Disabled'] = $row->disabled;

	return $resultArray;
}

# Remove admin group
function removeAdminUser($params) {
	global $db;

	$res = DBDo("DELETE FROM users WHERE ID = ?",array($params[0]));
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# Add admin group
function createAdminUser($params) {
	global $db;

	$res = DBDo("INSERT INTO users (Username) VALUES (?)",array($params[0]['Username']));
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# Edit admin group
function updateAdminUser($params) {
	global $db;

	$res = DBDo("UPDATE users SET Username = ? WHERE ID = ?",array($params[0]['Username'],$params[0]['ID']));
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# vim: ts=4
