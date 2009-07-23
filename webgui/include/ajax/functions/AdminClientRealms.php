<?php

include_once("include/db.php");


# Link client to realm
function addAdminClientRealm($params) {

	# Perform query
	$res = DBDo("INSERT INTO clients_to_realms (ClientID,RealmID) VALUES (?,?)",array($params[0]['ClientID'],$params[0]['RealmID']));

	# Return result
	if (is_bool($res)) {
		return $res;
	}

	return NULL;
}

# Unlink client from realm
function removeAdminClientRealm($params) {

	# Perform query
	$res = DBDo("DELETE FROM clients_to_realms WHERE ID = ?",array($params[0]));

	# Return result
	if (is_bool($res)) {
		return $res;
	}

	return NULL;
}
 
# Return list of realms 
function getAdminClientRealms($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => 'clients_to_realms.ID',
		'Name' => 'realms.Name'
	);

	# Perform query
	$res = DBSelectSearch("
			SELECT 
				clients_to_realms.ID, realms.Name 
			FROM 
				clients_to_realms, realms 
			WHERE 
				clients_to_realms.RealmID = realms.ID
				AND clients_to_realms.ClientID = ".DBQuote($params[0])."
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

		# Push this row onto array
		array_push($resultArray,$item);
	}

	# Return results
	return array($resultArray,$numResults);
}

# vim: ts=4
