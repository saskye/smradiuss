<?php

include_once("include/db.php");

# Add group attribute
function addAdminGroupMember($params) {
	global $db;

	$res = DBDo("INSERT INTO group_attributes (GroupID,Name) VALUES (?,?)",array($params[0]['GroupID'],$params[0]['Name']));
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# Remove group attribute
function removeAdminGroupMember($params) {
	global $db;

	$res = DBDo("DELETE FROM users_to_groups WHERE ID = ?",array($params[0]));
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# Edit attribute
function updateAdminGroupMember($params) {
	global $db;

	$res = DBDo("UPDATE group_attributes SET Name = ? WHERE ID = ?",array($params[0]['Name'],$params[0]['ID']));
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# Return specific attribute row
function getAdminGroupMember($params) {
	global $db;


	$res = DBSelect("SELECT ID, Name FROM group_attributes WHERE ID = ?",array($params[0]));
	if (!is_object($res)) {
		return $res;
	}

	$resultArray = array();

	$row = $res->fetchObject();

	$resultArray['ID'] = $row->id;
	$resultArray['Name'] = $row->name;

	return $resultArray;
}

# Return list of attributes
function getAdminGroupMembers($params) {
	global $db;

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => 'users_to_groups.ID',
		'Name' => 'group_attributes.Name',
		'Disabled' => 'group_attributes.Disabled'
	);

	$res = DBSelectSearch("
			SELECT 
				ID, Name, Operator, Value, Disabled 
			FROM 
				group_attributes 
			WHERE 
				GroupID = ".DBQuote($params[0])."
		",$params[1],$filtersorts,$filtersorts);

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

?>
