<?php

include_once("include/db.php");

# Remove group member
function removeAdminGroupMember($params) {

	$res = DBDo("DELETE FROM @TP@users_to_groups WHERE ID = ?",array($params[0]));

	# Return result
	if ($res !== TRUE) {
		return $res;
	}

	return NULL;
}

# Return list of members
function getAdminGroupMembers($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => '@TP@users_to_groups.ID',
		'Username' => '@TP@group_attributes.Username',
		'Disabled' => '@TP@group_attributes.Disabled'
	);

	# Fetch members
	$res = DBSelectSearch("
			SELECT 
				@TP@users_to_groups.ID, @TP@users.Username, @TP@users.Disabled 
			FROM 
				@TP@users_to_groups, @TP@users
			WHERE 
				@TP@users.ID = @TP@users_to_groups.UserID
			AND
				@TP@users_to_groups.GroupID = ".DBQuote($params[0])."
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
		$item['Username'] = $row->username;
		$item['Disabled'] = $row->disabled;

		# Push this row onto array
		array_push($resultArray,$item);
	}

	return array($resultArray,$numResults);
}

# vim: ts=4
