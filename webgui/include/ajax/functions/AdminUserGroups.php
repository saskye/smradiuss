<?php

include_once("include/db.php");


# Link user to group
function addAdminUserGroup($params) {
	global $db;

	$res = DBDo("INSERT INTO users_to_groups (UserID,GroupID) VALUES (?,?)",array($params[0]['UserID'],$params[0]['GroupID']));
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# Unlink user from group
function removeAdminUserGroup($params) {
	global $db;

	$res = DBDo("DELETE FROM users_to_groups WHERE ID = ?",array($params[0]));
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}
 
# Return list of groups 
function getAdminUserGroups($params) {
	global $db;

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => 'users_to_groups.ID',
		'Name' => 'groups.Name'
	);

	$res = DBSelectSearch("
			SELECT 
				users_to_groups.ID, groups.Name 
			FROM 
				users_to_groups, groups 
			WHERE 
				users_to_groups.GroupID = groups.ID
				AND users_to_groups.UserID = ".DBQuote($params[0])."
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
		$item['Name'] = $row->name;

		# push this row onto array
		array_push($resultArray,$item);
	}

	return array($resultArray,$numResults);
}

# vim: ts=4
