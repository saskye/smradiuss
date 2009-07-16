<?php

include_once("include/db.php");


# Return list of groups
function getAdminGroups($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => 'groups.ID',
		'Name' => 'groups.Name',
		'Priority' => 'groups.Priority',
		'Disabled' => 'groups.Disabled',
		'Comment' => 'groups.Comment'
	);

	# Perform query
	$res = DBSelectSearch("SELECT ID, Name, Priority, Disabled, Comment FROM groups",$params[1],$filtersorts,$filtersorts);
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
		$item['Priority'] = $row->priority;
		$item['Disabled'] = $row->disabled;
		$item['Comment'] = $row->comment;

		# Push this row onto array
		array_push($resultArray,$item);
	}

	# Return results
	return array($resultArray,$numResults);
}

# Return specific group row
function getAdminGroup($params) {

	# Perform query
	$res = DBSelect("SELECT ID, Name, Priority, Disabled, Comment FROM groups WHERE ID = ?",array($params[0]));

	# Return error if failed
	if (!is_object($res)) {
		return $res;
	}

	# Build array of results
	$resultArray = array();
	$row = $res->fetchObject();

	$resultArray['ID'] = $row->id;
	$resultArray['Name'] = $row->name;
	$resultArray['Priority'] = $row->priority;
	$resultArray['Disabled'] = $row->disabled;
	$resultArray['Comment'] = $row->comment;

	# Return results
	return $resultArray;
}

# Remove admin group
function removeAdminGroup($params) {

	# Begin transaction
	DBBegin();

	# Unlink users from group
	$res = DBDo("DELETE FROM users_to_groups WHERE GroupID = ?",array($params[0]));

	# Delete group attribtues
	if ($res !== FALSE) {
		$res = DBDo("DELETE FROM group_attributes WHERE GroupID = ?",array($params[0]));
	}

	# Delete group
	if ($res !== FALSE) {
		$res = DBDo("DELETE FROM groups WHERE ID = ?",array($params[0]));
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

# Add admin group
function createAdminGroup($params) {

	# Perform query
	$res = DBDo("INSERT INTO groups (Name) VALUES (?)",array($params[0]['Name']));

	# Return result
	if (is_bool($res)) {
		return $res;
	}

	return NULL;
}

# Edit admin group
function updateAdminGroup($params) {

	# Perform query
	$res = DBDo("UPDATE groups SET Name = ? WHERE ID = ?",array($params[0]['Name'],$params[0]['ID']));

	# Return result
	if (is_bool($res)) {
		return $res;
	}

	return NULL;
}

# vim: ts=4
