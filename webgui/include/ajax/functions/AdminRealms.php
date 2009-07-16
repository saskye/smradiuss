<?php

include_once("include/db.php");


# Return list of realms
function getAdminRealms($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => 'realms.ID',
		'Name' => 'realms.Name',
		'Disabled' => 'realms.Disabled'
	);

	# Perform query
	$res = DBSelectSearch("SELECT ID, Name, Disabled FROM realms",$params[1],$filtersorts,$filtersorts);
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
	$res = DBSelect("SELECT ID, Name, Disabled FROM realms WHERE ID = ?",array($params[0]));

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
	$res = DBDo("DELETE FROM realm_attributes WHERE RealmID = ?",array($params[0]));

	# Perform next query if successful
	if ($res !== FALSE) {
		$res = DBDo("DELETE FROM realms WHERE ID = ?",array($params[0]));
	}

	# Commit and return if successful
	if (is_bool($res)) {
		DBCommit();
		return $res;
	# Else rollback database
	} else {
		DBRollback();
	}

	return NULL;
}

# Add admin realm
function createAdminRealm($params) {

	# Perform query
	$res = DBDo("INSERT INTO realms (Name) VALUES (?)",array($params[0]['Name']));

	# Return result
	if (is_bool($res)) {
		return $res;
	}

	return NULL;
}

# Edit admin realm
function updateAdminRealm($params) {

	# Perform query
	$res = DBDo("UPDATE realms SET Name = ? WHERE ID = ?",array($params[0]['Name'],$params[0]['ID']));

	# Return result
	if (is_bool($res)) {
		return $res;
	}

	return NULL;
}

# vim: ts=4
