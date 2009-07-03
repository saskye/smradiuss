<?php

include_once("include/db.php");

# Add group attribute
function addAdminGroupAttribute($params) {

	$res = DBDo("
				INSERT INTO 
						group_attributes (GroupID,Name,Operator,Value,Disabled) 
				VALUES 
						(?,?,?,?,?)",
				array(	$params[0]['GroupID'],
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

# Remove group attribute
function removeAdminGroupAttribute($params) {

	$res = DBDo("DELETE FROM group_attributes WHERE ID = ?",array($params[0]));
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# Edit group attribute
function updateAdminGroupAttribute($params) {

	$res = DBDo("UPDATE group_attributes SET Name = ?, Operator = ?, Value = ?, Disabled = ? WHERE ID = ?",
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
function getAdminGroupAttribute($params) {

	$res = DBSelect("SELECT ID, Name, Operator, Value, Disabled FROM group_attributes WHERE ID = ?",array($params[0]));
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
function getAdminGroupAttributes($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => 'group_attributes.ID',
		'Name' => 'group_attributes.Name',
		'Operator' => 'group_attributes.Operator',
		'Value' => 'group_attributes.Value',
		'Disabled' => 'group_attributes.Disabled'
	);

	# Fetch attributes
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

	# Loop through rows
	while ($row = $sth->fetchObject()) {
		$item = array();

		$item['ID'] = $row->id;
		$item['Name'] = $row->name;
		$item['Operator'] = $row->operator;
		$item['Value'] = $row->value;
		$item['Disabled'] = $row->disabled;

		# Push this row onto array
		array_push($resultArray,$item);
	}

	# Return results
	return array($resultArray,$numResults);
}

# vim: ts=4
