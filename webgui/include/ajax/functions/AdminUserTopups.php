<?php

include_once("include/db.php");


# Add new topup
function createAdminUserTopup($params) {

	# Get today's date
	$timestamp = date('Y-m-d H:i:s');

	# Perform query
	$res = DBDo("INSERT INTO @TP@topups (UserID,Timestamp,Type,Value,ValidFrom,ValidTo) VALUES (?,?,?,?,?,?)",
			array($params[0]['UserID'],$timestamp,$params[0]['Type'],$params[0]['Value'],$params[0]['ValidFrom'],
					$params[0]['ValidTo'])
	);

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# Edit topup
function updateAdminUserTopup($params) {

	# Perform query
	$res = DBDo("UPDATE @TP@topups SET Value = ?, Type = ?, ValidFrom = ?, ValidTo = ? WHERE ID = ?",
				array($params[0]['Value'],
				$params[0]['Type'],
				$params[0]['ValidFrom'],
				$params[0]['ValidTo'],
				$params[0]['ID'])
	);

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# Delete user topup
function removeAdminUserTopup($params) {

	# Delete topup summary
	$res = DBDo("DELETE FROM @TP@topups_summary WHERE TopupID = ?",array($params[0]));

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	# Delete topup
	$res = DBDo("DELETE FROM @TP@topups WHERE ID = ?",array($params[0]));

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# Return specific topup row
function getAdminUserTopup($params) {

	# Perform query
	$res = DBSelect("SELECT ID, Type, Value, ValidFrom, ValidTo FROM @TP@topups WHERE ID = ?",array($params[0]));

	# Return error if failed
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
function getAdminUserTopups($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => '@TP@topups.ID',
		'Type' => '@TP@topups.Type',
		'Value' => '@TP@topups.Value',
		'ValidFrom' => '@TP@topups.ValidFrom',
		'ValidTo' => '@TP@topups.ValidTo'
	);

	# Perform query
	$res = DBSelectSearch("
			SELECT 
				ID, Timestamp, Type, Value, ValidFrom, ValidTo
			FROM 
				@TP@topups 
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
