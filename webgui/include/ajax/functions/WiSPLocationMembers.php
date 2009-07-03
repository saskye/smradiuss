<?php

include_once("include/db.php");

# Remove group attribute
function removeWiSPLocationMember($params) {

	$res = DBDo("UPDATE wisp_userdata SET LocationID = NULL WHERE UserID = ?",array($params[0]));
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# Return list of attributes
function getWiSPLocationMembers($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => 'users.ID',
		'Username' => 'users.Username'
	);

	$res = DBSelectSearch("
			SELECT 
				users.ID, users.Username 
			FROM 
				wisp_userdata, users 
			WHERE 
				wisp_userdata.LocationID = ".DBQuote($params[0])."
			AND
				users.ID = wisp_userdata.UserID 
		",$params[1],$filtersorts,$filtersorts);

	$sth = $res[0]; $numResults = $res[1];
	# If STH is blank, return the error back to whoever requested the data
	if (!isset($sth)) {
		return $res;
	}

	$resultArray = array();

	# loop through rows
	while ($row = $sth->fetchObject()) {
		$item = array();

		$item['ID'] = $row->id;
		$item['Username'] = $row->username;

		# push this row onto array
		array_push($resultArray,$item);
	}

	return array($resultArray,$numResults);
}

# vim: ts=4
