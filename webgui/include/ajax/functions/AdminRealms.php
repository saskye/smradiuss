<?php

include_once("include/db.php");


# Return list of realms
function getAdminRealms($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => '@TP@realms.ID',
		'Name' => '@TP@realms.Name',
		'Disabled' => '@TP@realms.Disabled'
	);

	# Perform query
	$res = DBSelectSearch("SELECT ID, Name, Disabled FROM @TP@realms",$params[1],$filtersorts,$filtersorts);
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
		$item['Name'] = htmlspecialchars($row->name);
		$item['Disabled'] = $row->disabled;

		# Push this row onto array
		array_push($resultArray,$item);
	}

	# Return results
	return array($resultArray,$numResults);
}

# Return specific realm row
function getAdminRealm($params) {

	# Perform query
	$res = DBSelect("SELECT ID, Name, Disabled FROM @TP@realms WHERE ID = ?",array($params[0]));

	# Return error if failed
	if (!is_object($res)) {
		return $res;
	}

	# Build array of results
	$resultArray = array();
	$row = $res->fetchObject();

	$resultArray['ID'] = $row->id;
	$resultArray['Name'] = $row->name;
	$resultArray['Disabled'] = $row->disabled;

	# Return results
	return $resultArray;
}

# Remove admin realm
function removeAdminRealm($params) {

	# Begin transaction
	DBBegin();

	# Perform query
	$res = DBDo("DELETE FROM @TP@realm_attributes WHERE RealmID = ?",array($params[0]));

	# Perform next query if successful
	if ($res !== FALSE) {
		$res = DBDo("DELETE FROM @TP@realms WHERE ID = ?",array($params[0]));
	}

	# Return result
	if ($res !== TRUE) {
		DBRollback();
		return $res;
	} else {
		DBCommit();
	}

	return NULL;
}

# Add admin realm
function createAdminRealm($params) {

	# Perform query
	$res = DBDo("INSERT INTO @TP@realms (Name) VALUES (?)",array($params[0]['Name']));

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# Edit admin realm
function updateAdminRealm($params) {

	# Perform query
	$res = DBDo("UPDATE @TP@realms SET Name = ? WHERE ID = ?",array($params[0]['Name'],$params[0]['ID']));

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# vim: ts=4
