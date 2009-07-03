<?php

include_once("include/db.php");


# Add new topup
function createWiSPUserTopup($params) {

	# Current datetime
	$timestamp = date('Y-m-d H:i:s');

	# Perform query
	$res = DBDo("INSERT INTO topups (UserID,Timestamp,Type,Value,ValidFrom,ValidTo) VALUES (?,?,?,?,?,?)",
			array($params[0]['UserID'],$timestamp,$params[0]['Type'],$params[0]['Value'],$params[0]['ValidFrom'],
					$params[0]['ValidTo'])
	);

	# Return result
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# Edit topup
function updateWiSPUserTopup($params) {

	# Perform query
	$res = DBDo("UPDATE topups SET Value = ?, Type = ?, ValidFrom = ?, ValidTo = ? WHERE ID = ?",
				array($params[0]['Value'],
				$params[0]['Type'],
				$params[0]['ValidFrom'],
				$params[0]['ValidTo'],
				$params[0]['ID'])
	);

	# Return result
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# Delete user topup
function removeWiSPUserTopup($params) {

	# Perform query
	$res = DBDo("DELETE FROM topups WHERE ID = ?",array($params[0]));

	# Return result
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# Return specific topup row
function getWiSPUserTopup($params) {

	# Perform query
	$res = DBSelect("SELECT ID, Type, Value, ValidFrom, ValidTo FROM topups WHERE ID = ?",array($params[0]));

	# Return if error or no result
	if (!is_object($res)) {
		return $res;
	}

	# Build array of results
	$resultArray = array();
	$row = $res->fetchObject();

	$resultArray['ID'] = $row->id;
	$resultArray['Type'] = $row->type;
	$resultArray['Value'] = $row->value;
	# Convert to ISO format
	$date = new DateTime($row->validfrom);
	$value = $date->format("Y-m-d");
	$resultArray['ValidFrom'] = $value;
	# Convert to ISO format
	$date = new DateTime($row->validto);
	$value = $date->format("Y-m-d");
	$resultArray['ValidTo'] = $value;

	# Return results
	return $resultArray;
}

# Return list of topups
function getWiSPUserTopups($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => 'topups.ID',
		'Type' => 'topups.Type',
		'Value' => 'topups.Value',
		'ValidFrom' => 'topups.ValidFrom',
		'ValidTo' => 'topups.ValidTo'
	);

	# Perform query
	$res = DBSelectSearch("
			SELECT 
				ID, Timestamp, Type, Value, ValidFrom, ValidTo
			FROM 
				topups 
			WHERE 
				Depleted = 0
			AND
				UserID = ".DBQuote($params[0]['UserID'])."
			ORDER BY
				Timestamp
			DESC
		",$params[1],$filtersorts,$filtersorts);
	$sth = $res[0]; $numResults = $res[1];

	# If STH is blank, return the error back to whoever requested the data
	if (!isset($sth)) {
		return $res;
	}

	# Loop through rows
	$resultArray = array();
	while ($row = $sth->fetchObject()) {

		# Array for this row
		$item = array();

		$item['ID'] = $row->id;
		$item['Timestamp'] = $row->timestamp;
		$item['Type'] = $row->type;
		$item['Value'] = $row->value;
		# Convert to ISO format
		$date = new DateTime($row->validfrom);
		$value = $date->format("Y-m-d");
		$item['ValidFrom'] = $value;
		# Convert to ISO format
		$date = new DateTime($row->validto);
		$value = $date->format("Y-m-d");
		$item['ValidTo'] = $value;

		# Push this row onto array
		array_push($resultArray,$item);
	}

	# Return results
	return array($resultArray,$numResults);
}

# vim: ts=4
