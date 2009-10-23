<?php

include_once("include/db.php");

# Add user attribute
function addAdminUserAttribute($params) {

	# Perform query
	$res = DBDo("
				INSERT INTO 
						@TP@user_attributes (UserID,Name,Operator,Value,Disabled) 
				VALUES 
						(?,?,?,?,?)",
				array(	$params[0]['UserID'],
						$params[0]['Name'],
						$params[0]['Operator'],
						$params[0]['Value'],
						$params[0]['Disabled'])
	);

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# Remove user attribute
function removeAdminUserAttribute($params) {

	# Perform query
	$res = DBDo("DELETE FROM @TP@user_attributes WHERE ID = ?",array($params[0]));

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# Edit attribute
function updateAdminUserAttribute($params) {

	# Perform query
	$res = DBDo("UPDATE @TP@user_attributes SET Name = ?, Operator = ?, Value = ?, Disabled = ? WHERE ID = ?",
				array($params[0]['Name'],
				$params[0]['Operator'],
				$params[0]['Value'],
				$params[0]['Disabled'],
				$params[0]['ID'])
	);

	# Return error
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# Return specific attribute row
function getAdminUserAttribute($params) {

	# Perform query
	$res = DBSelect("SELECT ID, Name, Operator, Value, Disabled FROM @TP@user_attributes WHERE ID = ?",array($params[0]));

	# Return error if failed
	if (!is_object($res)) {
		return $res;
	}

	# Build array of results
	$resultArray = array();
	$row = $res->fetchObject();

	$resultArray['ID'] = $row->id;
	$resultArray['Name'] = $row->name;
	$resultArray['Operator'] = $row->operator;
	$resultArray['Value'] = $row->value;
	$resultArray['Disabled'] = $row->disabled;

	# Return results
	return $resultArray;
}

# Return list of attributes
function getAdminUserAttributes($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => '@TP@user_attributes.ID',
		'Name' => '@TP@user_attributes.Name',
		'Operator' => '@TP@user_attributes.Operator',
		'Value' => '@TP@user_attributes.Value',
		'Disabled' => '@TP@user_attributes.Disabled'
	);

	# Perform query
	$res = DBSelectSearch("
			SELECT 
				ID, Name, Operator, Value, Disabled 
			FROM 
				@TP@user_attributes 
			WHERE 
				UserID = ".DBQuote($params[0])."
		",$params[1],$filtersorts,$filtersorts);
	$sth = $res[0]; $numResults = $res[1];

	# If STH is blank, return the error back to whoever requested the data
	if (!isset($sth)) {
		return $res;
	}

	# Loop through rows
	$resultArray = array();
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
