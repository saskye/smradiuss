<?php

include_once("include/db.php");


# Return list of groups
function getAdminGroups($params) {
	global $db;

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => 'groups.ID',
		'Name' => 'groups.Name',
		'Priority' => 'groups.Priority',
		'Disabled' => 'groups.Disabled',
		'Comment' => 'groups.Comment'
	);

	$res = DBSelectSearch("SELECT ID, Name, Priority, Disabled, Comment FROM groups",$params[1],$filtersorts,$filtersorts);
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
		$item['Priority'] = $row->priority;
		$item['Disabled'] = $row->disabled;
		$item['Comment'] = $row->comment;

		# push this row onto array
		array_push($resultArray,$item);
	}

	return array($resultArray,$numResults);
}

# Return specific group row
function getAdminGroup($params) {
	global $db;


	$res = DBSelect("SELECT ID, Name, Priority, Disabled, Comment FROM groups WHERE ID = ?",array($params[0]));
	if (!is_object($res)) {
		return $res;
	}

	$resultArray = array();

	$row = $res->fetchObject();

	$resultArray['ID'] = $row->id;
	$resultArray['Name'] = $row->name;
	$resultArray['Priority'] = $row->priority;
	$resultArray['Disabled'] = $row->disabled;
	$resultArray['Comment'] = $row->comment;

	return $resultArray;
}

# Remove admin group
function removeAdminGroup($params) {
	global $db;

	$res = DBDo("DELETE FROM groups WHERE ID = ?",array($params[0]));
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# Add admin group
function createAdminGroup($params) {
	global $db;

	$res = DBDo("INSERT INTO groups (Name) VALUES (?)",array($params[0]['Name']));
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# Edit admin group
function updateAdminGroup($params) {
	global $db;

	$res = DBDo("UPDATE groups SET Name = ? WHERE ID = ?",array($params[0]['Name'],$params[0]['ID']));
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# vim: ts=4
