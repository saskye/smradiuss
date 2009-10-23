<?php

include_once("include/db.php");

# Remove location member
function removeWiSPLocationMember($params) {

	# Perform query
	$res = DBDo("UPDATE @TP@wisp_userdata SET LocationID = NULL WHERE UserID = ?",array($params[0]));

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# Return list of location members
function getWiSPLocationMembers($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => '@TP@users.ID',
		'Username' => '@TP@users.Username'
	);

	# Perform query
	$res = DBSelectSearch("
			SELECT 
				@TP@users.ID, @TP@users.Username 
			FROM 
				@TP@wisp_userdata, @TP@users 
			WHERE 
				@TP@wisp_userdata.LocationID = ".DBQuote($params[0])."
			AND
				@TP@users.ID = @TP@wisp_userdata.UserID 
		",$params[1],$filtersorts,$filtersorts);
	$sth = $res[0]; $numResults = $res[1];

	# If STH is blank, return the error back to whoever requested the data
	if (!isset($sth)) {
		return $res;
	}

	# Loop through rows
	$resultArray = array();
	while ($row = $sth->fetchObject()) {

		# Build array for this row
		$item = array();

		$item['ID'] = $row->id;
		$item['Username'] = $row->username;

		# Push this row onto array
		array_push($resultArray,$item);
	}

	# Return results
	return array($resultArray,$numResults);
}

# vim: ts=4
