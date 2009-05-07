<?php

include_once("include/db.php");


# Return list of users
function getAdminGroups($params) {
	global $db;

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => 'groups.ID',
		'Name' => 'groups.Name',
		'Priority' => 'groups.Priority',
		'Disabled' => 'groups.Disabled',
		'Comment' => 'groups.Comment'
	);

	$res = DBSelectSearch("SELECT ID, Name, Priority, Disabled, Comment FROM groups",$params[1],$filtersorts,$filtersorts);
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
			$item['Priority'] = $row->priority;
			$item['Disabled'] = $row->disabled;
			$item['Comment'] = $row->comment;

			# push this row onto array
			array_push($resultArray,$item);
		}

	return array($resultArray,$numResults);
}

# Return list of users
function removeAdminGroup($params) {
	global $db;

	$res = DBDo("DELETE FROM groups WHERE ID = ".$params[0][0]);
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

?>
