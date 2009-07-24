<?php

include_once("include/db.php");

# Remove realm member
function removeAdminRealmMember($params) {

	$res = DBDo("DELETE FROM clients_to_realms WHERE ID = ?",array($params[0]));

	# Return result
	if (is_bool($res)) {
		return $res;
	}

	return NULL;
}

# Return list of members
function getAdminRealmMembers($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => 'clients_to_realms.ID',
		'Name' => 'realm_attributes.Name'
	);

	# Fetch members
	$res = DBSelectSearch("
			SELECT 
				clients_to_realms.ID, clients.Name
			FROM 
				clients_to_realms, clients
			WHERE 
				clients.ID = clients_to_realms.ClientID
			AND
				clients_to_realms.RealmID = ".DBQuote($params[0])."
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

	return array($resultArray,$numResults);
}

# vim: ts=4
