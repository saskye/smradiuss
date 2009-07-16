<?php

include_once("include/db.php");


# Return list of users
function getAdminUsers($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => 'users.ID',
		'Username' => 'users.Username',
		'Disabled' => 'users.Disabled',
	);

	# Perform query
	$res = DBSelectSearch("SELECT ID, Username, Disabled FROM users",$params[1],$filtersorts,$filtersorts);
	$sth = $res[0]; $numResults = $res[1];

	# If STH is blank, return the error back to whoever requested the data
	if (!isset($sth)) {
		return $res;
	}

	# Loop through rows
	$resultArray = array();
	while ($row = $sth->fetchObject()) {

		# Array for this row
		$item = array();

		$item['ID'] = $row->id;
		$item['Username'] = $row->username;
		$item['Disabled'] = $row->disabled;

		# Push this row onto main array
		array_push($resultArray,$item);
	}

	# Return results
	return array($resultArray,$numResults);
}

# Return specific user
function getAdminUser($params) {

	# Perform query
	$res = DBSelect("SELECT ID, Username, Disabled FROM users WHERE ID = ?",array($params[0]));

	# Return error if failed
	if (!is_object($res)) {
		return $res;
	}

	# Build array of results
	$resultArray = array();
	$row = $res->fetchObject();

	$resultArray['ID'] = $row->id;
	$resultArray['Username'] = $row->username;
	$resultArray['Disabled'] = $row->disabled;

	# Return results
	return $resultArray;
}

# Remove admin user
function removeAdminUser($params) {

	# Begin transaction
	DBBegin();

	# Delete user information, if any
	$res = DBDo("DELETE FROM wisp_userdata WHERE UserID = ?",array($params[0]));

	# Delete user attribtues
	if ($res !== FALSE) {
		$res = DBDo("DELETE FROM user_attributes WHERE UserID = ?",array($params[0]));
	}

	# Remove user from groups
	if ($res !== FALSE) {
		$res = DBDo("DELETE FROM users_to_groups WHERE UserID = ?",array($params[0]));
	}

	# Get list of topups and delete summaries
	if ($res !== FALSE) {
		$topupList = array();
		$res = DBSelect("
			SELECT
				topups_summary.TopupID
			FROM
				topups_summary, topups
			WHERE
				topups_summary.TopupID = topups.ID
				AND topups.UserID = ?",
				array($params[0])
		);

		if (!is_object($res)) {
			$res = FALSE;
		} else {
			while ($row = $res->fetchObject()) {
				array_push($topupList,$row->topupid);
			}
		}

		if ($res !== FALSE && sizeof($topupList) > 0) {
			# Remove topup summaries
			foreach ($topupList as $id) {
				if ($res !== FALSE) {
					$res = DBDo("
						DELETE FROM
							topups_summary
						WHERE
							TopupID = ?",
							array($id)
					);
				}
			}
		}
	}

	# Remove topups
	if ($res !== FALSE) {
		$res = DBDo("DELETE FROM topups WHERE UserID = ?",array($params[0]));
	}

	# Delete user
	if ($res !== FALSE) {
		$res = DBDo("DELETE FROM users WHERE ID = ?",array($params[0]));
	}

	# Commit and return if successful
	if ($res !== FALSE) {
		DBCommit();
		return $res;
	# Else rollback database
	} else {
		DBRollback();
	}

	return NULL;
}

# Add admin user
function createAdminUser($params) {

	# Perform query
	$res = DBDo("INSERT INTO users (Username) VALUES (?)",array($params[0]['Username']));

	# Return result
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# Edit admin user
function updateAdminUser($params) {

	# Perform query
	$res = DBDo("UPDATE users SET Username = ? WHERE ID = ?",array($params[0]['Username'],$params[0]['ID']));

	# Return result
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

# vim: ts=4
