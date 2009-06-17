<?php

include_once("include/db.php");

# Remove group member
function removeAdminGroupMember($params) {
	global $db;

	$res = DBDo("DELETE FROM users_to_groups WHERE ID = ?",array($params[0]));
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# Return list of members
function getAdminGroupMembers($params) {
	global $db;

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => 'users_to_groups.ID',
		'Username' => 'group_attributes.Username',
		'Disabled' => 'group_attributes.Disabled'
	);

	$res = DBSelectSearch("
			SELECT 
				users_to_groups.ID, users.Username, users.Disabled 
			FROM 
				users_to_groups, users
			WHERE 
				users.ID = users_to_groups.UserID
			AND
				users_to_groups.GroupID = ".DBQuote($params[0])."
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
		$item['Disabled'] = $row->disabled;

		# push this row onto array
		array_push($resultArray,$item);
	}

	return array($resultArray,$numResults);
}

?>
