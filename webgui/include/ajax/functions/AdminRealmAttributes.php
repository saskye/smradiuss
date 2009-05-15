<?php

include_once("include/db.php");

# Add user attribute
function addAdminRealmAttribute($params) {
	global $db;

	$res = DBDo("
				INSERT INTO 
						realm_attributes (RealmID,Name,Operator,Value,Disabled) 
				VALUES 
						(?,?,?,?,?)",
				array(	$params[0]['RealmID'],
						$params[0]['Name'],
						$params[0]['Operator'],
						$params[0]['Value'],
						$params[0]['Disabled'])
	);

	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# Remove user attribute
function removeAdminRealmAttribute($params) {
	global $db;

	$res = DBDo("DELETE FROM realm_attributes WHERE ID = ?",array($params[0]));
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# Edit attribute
function updateAdminRealmAttribute($params) {
	global $db;

	$res = DBDo("UPDATE realm_attributes SET Name = ?, Operator = ?, Value = ?, Disabled = ? WHERE ID = ?",
				array($params[0]['Name'],
				$params[0]['Operator'],
				$params[0]['Value'],
				$params[0]['Disabled'],
				$params[0]['ID'])
	);

	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# Return specific attribute row
function getAdminRealmAttribute($params) {
	global $db;


	$res = DBSelect("SELECT ID, Name, Operator, Value, Disabled FROM realm_attributes WHERE ID = ?",array($params[0]));
	if (!is_object($res)) {
		return $res;
	}

	$resultArray = array();

	$row = $res->fetchObject();

	$resultArray['ID'] = $row->id;
	$resultArray['Name'] = $row->name;
	$resultArray['Operator'] = $row->operator;
	$resultArray['Value'] = $row->value;
	$resultArray['Disabled'] = $row->disabled;

	return $resultArray;
}

# Return list of attributes
function getAdminRealmAttributes($params) {
	global $db;

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => 'realm_attributes.ID',
		'Name' => 'realm_attributes.Name',
		'Operator' => 'realm_attributes.Operator',
		'Value' => 'realm_attributes.Value',
		'Disabled' => 'realm_attributes.Disabled'
	);

	$res = DBSelectSearch("
			SELECT 
				ID, Name, Operator, Value, Disabled 
			FROM 
				realm_attributes 
			WHERE 
				RealmID = ".DBQuote($params[0])."
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
