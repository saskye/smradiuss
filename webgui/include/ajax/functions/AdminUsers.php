<?php

include_once("include/db.php");


# Return list of users
function getAdminUsers($params) {
	global $db;

	# Filters and sorts are the same here
	$filtersorts = array(
		'ID' => 'users.ID',
		'Username' => 'users.Username',
		'Disabled' => 'users.Disabled',
	);

	$res = DBSelectSearch("SELECT ID, Username, Disabled FROM users",$params[1],$filtersorts,$filtersorts);
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

# Return specific group row
function getAdminUser($params) {
	global $db;


	$res = DBSelect("SELECT ID, Username, Disabled FROM users WHERE ID = ?",array($params[0]));
	if (!is_object($res)) {
		return $res;
	}

	$resultArray = array();

	$row = $res->fetchObject();

	$resultArray['ID'] = $row->id;
	$resultArray['Username'] = $row->username;
	$resultArray['Disabled'] = $row->disabled;

	$res = DBSelect("SELECT Value FROM user_attributes WHERE Name = ? AND UserID = ?",
			array('User-Password',$params[0])
	);
	if (!is_object($res)) {
		return $res;
	}

	$row = $res->fetchObject();

	$resultArray['Password'] = $row->value;

	return $resultArray;
}

# Remove admin group
function removeAdminUser($params) {
	global $db;

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

# Add admin group
function createAdminUser($params) {
	global $db;

	DBBegin();
	$res = DBDo("INSERT INTO users (Username) VALUES (?)",array($params[0]['Username']));

	if ($res !== FALSE) {
		$lastInsertID = DBLastInsertID();
		if (isset($lastInsertID)) {
			$res = DBDo("INSERT INTO user_attributes (UserID,Name,Operator,Value) VALUES (?,?,?,?)",
					array($lastInsertID,'User-Password','==',$params[0]['Password'])
			);
		} else {
			$res = 0;
		}
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

# Edit admin group
function updateAdminUser($params) {
	global $db;

	DBBegin();
	$res = DBDo("UPDATE users SET Username = ? WHERE ID = ?",array($params[0]['Username'],$params[0]['ID']));

	if ($res !== FALSE) {
		$res = DBDo("UPDATE user_attributes SET Value = ? WHERE Name = ? AND UserID = ?",
				array($params[0]['Password'],'User-Password',$params[0]['ID'])
		);
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

# vim: ts=4
