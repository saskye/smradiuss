<?php

include_once("include/db.php");

# Add realm attribute
function addAdminRealmAttribute($params) {

	# Perform query
	$res = DBDo("
				INSERT INTO 
						@TP@realm_attributes (RealmID,Name,Operator,Value,Disabled) 
				VALUES 
						(?,?,?,?,?)",
				array(	$params[0]['RealmID'],
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

# Remove realm attribute
function removeAdminRealmAttribute($params) {

	# Perform query
	$res = DBDo("DELETE FROM @TP@realm_attributes WHERE ID = ?",array($params[0]));

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# Edit realm attribute
function updateAdminRealmAttribute($params) {

	# Perform query
	$res = DBDo("UPDATE @TP@realm_attributes SET Name = ?, Operator = ?, Value = ?, Disabled = ? WHERE ID = ?",
				array($params[0]['Name'],
				$params[0]['Operator'],
				$params[0]['Value'],
				$params[0]['Disabled'],
				$params[0]['ID'])
	);

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# Return specific attribute row
function getAdminRealmAttribute($params) {

	# Perform query
	$res = DBSelect("SELECT ID, Name, Operator, Value, Disabled FROM @TP@realm_attributes WHERE ID = ?",array($params[0]));

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
function getAdminRealmAttributes($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => '@TP@realm_attributes.ID',
		'Name' => '@TP@realm_attributes.Name',
		'Operator' => '@TP@realm_attributes.Operator',
		'Value' => '@TP@realm_attributes.Value',
		'Disabled' => '@TP@realm_attributes.Disabled'
	);

	# Perform query
	$res = DBSelectSearch("
			SELECT 
				ID, Name, Operator, Value, Disabled 
			FROM 
				@TP@realm_attributes 
			WHERE 
				RealmID = ".DBQuote($params[0])."
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
