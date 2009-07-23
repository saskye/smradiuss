<?php

include_once("include/db.php");


# Return list of clients
function getAdminClients($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => 'clients.ID',
		'Name' => 'clients.Name',
		'AccessList' => 'clients.AccessList'
	);

	# Perform query
	$res = DBSelectSearch("SELECT ID, Name, AccessList FROM clients",$params[1],$filtersorts,$filtersorts);
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
		$item['AccessList'] = $row->accesslist;

		# Push this row onto array
		array_push($resultArray,$item);
	}

	# Return results
	return array($resultArray,$numResults);
}

# Return specific client row
function getAdminClient($params) {

	# Perform query
	$res = DBSelect("SELECT ID, Name, AccessList FROM clients WHERE ID = ?",array($params[0]));

	# Return error if failed
	if (!is_object($res)) {
		return $res;
	}

	# Build array of results
	$resultArray = array();
	$row = $res->fetchObject();

	$resultArray['ID'] = $row->id;
	$resultArray['Name'] = $row->name;
	$resultArray['AccessList'] = $row->accesslist;

	# Return results
	return $resultArray;
}

# Remove admin client
function removeAdminClient($params) {

	# Begin transaction
	DBBegin();

	# Perform query
	$res = DBDo("DELETE FROM client_attributes WHERE ClientID = ?",array($params[0]));

	# Remove client from realms
	if ($res !== FALSE) {
		$res = DBDo("DELETE FROM clients_to_realms WHERE ClientID = ?",array($params[0]));
	}

	# Perform next query if successful
	if ($res !== FALSE) {
		$res = DBDo("DELETE FROM clients WHERE ID = ?",array($params[0]));
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

# Add admin client
function createAdminClient($params) {

	# Perform query
	$res = DBDo("INSERT INTO clients (Name,AccessList) VALUES (?,?)",array($params[0]['Name'],$params[0]['AccessList']));

	# Return result
	if (is_bool($res)) {
		return $res;
	}

	return NULL;
}

# Edit admin client
function updateAdminClient($params) {

	# Perform query
	$res = DBDo("UPDATE clients SET Name = ?, AccessList = ? WHERE ID = ?",
			array($params[0]['Name'],$params[0]['AccessList'],$params[0]['ID']));

	# Return result
	if (is_bool($res)) {
		return $res;
	}

	return NULL;
}


# vim: ts=4
