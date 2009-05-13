<?php

include_once("include/db.php");

# Add user attribute
function addAdminUserAttribute($params) {
	global $db;

	$res = DBDo("INSERT INTO user_attributes (UserID,Name) VALUES (?,?)",array($params[0]['UserID'],$params[0]['Name']));
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# Remove user attribute
function removeAdminUserAttribute($params) {
	global $db;

	$res = DBDo("DELETE FROM user_attributes WHERE ID = ?",array($params[0]));
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# Edit attribute
function updateAdminUserAttribute($params) {
	global $db;

	$res = DBDo("UPDATE user_attributes SET Name = ? WHERE ID = ?",array($params[0]['Name'],$params[0]['ID']));
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# Return specific attribute row
function getAdminUserAttribute($params) {
	global $db;


	$res = DBSelect("SELECT ID, Name FROM user_attributes WHERE ID = ?",array($params[0]));
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
function getAdminUserAttributes($params) {
	global $db;

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => 'user_attributes.ID',
		'Name' => 'user_attributes.Name',
		'Operator' => 'user_attributes.Operator',
		'Value' => 'user_attributes.Value',
		'Disabled' => 'user_attributes.Disabled'
	);

	$res = DBSelectSearch("
			SELECT 
				ID, Name, Operator, Value, Disabled 
			FROM 
				user_attributes 
			WHERE 
				UserID = ".DBQuote($params[0])."
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
		$item['Operator'] = $row->operator;
		$item['Value'] = $row->value;
		$item['Disabled'] = $row->disabled;

		# push this row onto array
		array_push($resultArray,$item);
	}

	return array($resultArray,$numResults);
}

?>
