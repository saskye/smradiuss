<?php

include_once("include/db.php");


# Return list of attributes
function getAdminUserGroups($params) {
	global $db;

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => 'users_to_groups.GroupID',
		'Name' => 'groups.Name'
	);

	$resultArray = array();
	$res = DBDo("SELECT GroupID FROM users_to_groups WHERE UserID = ?",$params[0]);
	if ($res !== FALSE) {
		while ($row = $res->fetchObject()) {
			$item = array();
			$item['ID'] = $row->groupid;

			$res2 = DBDo("SELECT Name FROM groups WHERE ID = ?",$item['ID']);
			if ($res !== FALSE) {
				$row = $res->fetchObject();
				$item['Name'] = $row->name;
				if (isset($item['Name'])) {
					array_push($resultArray,$item);
				}
			}
		}
	}

	# If STH is blank, return the error back to whoever requested the data
	if ($res == FALSE) {
		return $res;
	}

	# loop through rows
	while ($row = $sth->fetchObject()) {
		$item = array();

		$item['ID'] = $row->groupid;
		$item['Name'] = $row->name;

		# push this row onto array
		array_push($resultArray,$item);
	}

	return array($resultArray,$numResults);
}

?>
