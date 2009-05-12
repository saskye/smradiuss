<?php

include_once("include/db.php");


# Return list of wisp users
function getWiSPUsers($params) {
	global $db;

	# Filters and sorts are the same here
	$filtersorts = array(
		'Username' => 'users.Username',
		'Disabled' => 'users.Disabled',
		'ID' => 'wisp_userdata.UserID',
		'Firstname' => 'wisp_userdata.Firstname',
		'Lastname' => 'wisp_userdata.Lastname',
		'Email' => 'wisp_userdata.Email',
		'Phone' => 'wisp_userdata.Phone'
	);

	$res = DBSelectSearch("
		SELECT 
			users.Username, 
			users.Disabled, 
			wisp_userdata.UserID, 
			wisp_userdata.FirstName, 
			wisp_userdata.LastName, 
			wisp_userdata.Email, 
			wisp_userdata.Phone  
		FROM 
			users, wisp_userdata
		WHERE
			wisp_userdata.UserID = users.ID
		",$params[1],$filtersorts,$filtersorts
	);

	$sth = $res[0]; $numResults = $res[1];
	# If STH is blank, return the error back to whoever requested the data
	if (!isset($sth)) {
		return $res;
	}

	$resultArray = array();

	# loop through rows
	while ($row = $sth->fetchObject()) {
		$item = array();

		$item['ID'] = $row->userid;
		$item['Username'] = $row->username;
		$item['Disabled'] = $row->disabled;
		$item['Firstname'] = $row->firstname;
		$item['Lastname'] = $row->lastname;
		$item['Email'] = $row->email;
		$item['Phone'] = $row->phone;

		# push this row onto array
		array_push($resultArray,$item);
	}

	return array($resultArray,$numResults);
}

# Return specific wisp user row
function getWiSPUser($params) {
	global $db;


	$res = DBSelect("SELECT ID, Username FROM users WHERE ID = ?",array($params[0]));
	if (!is_object($res)) {
		return $res;
	}

	$resultArray = array();

	$row = $res->fetchObject();

	$resultArray['ID'] = $row->id;
	$resultArray['Username'] = $row->username;

	return $resultArray;
}

# Remove wisp user
function removeWiSPUser($params) {
	global $db;

	DBBegin();
	$res = DBDo("DELETE FROM wisp_userdata WHERE UserID = ?",array($params[0]));

	if ($res !== FALSE) {
		$res = DBDo("DELETE FROM users WHERE ID = ?",array($params[0]));
	} else {
		DBRollback();
		return $res;
	}

	if ($res !== FALSE) {
		DBCommit();
		return $res;
	} else {
		DBRollback();
	}

	return NULL;
}

# Add wisp user
function createWiSPUser($params) {
	global $db;

	DBBegin();
	$res = DBDo("INSERT INTO users (Username) VALUES (?)",array($params[0]['Username']));

	if ($res !== FALSE) {
		$userID = DBLastInsertID();
		$res = DBDo("INSERT INTO wisp_userdata (UserID) VALUES (?)",array($userID));
	}

	if ($res !== FALSE) {
		DBCommit();
		return $res;
	} else {
		DBRollback();
	}
#	if (!is_numeric($res)) {
#		return $res;
#	}

	return NULL;
}

# Edit admin group
function updateWiSPUser($params) {
	global $db;

	$res = DBDo("UPDATE users SET Username = ? WHERE ID = ?",array($params[0]['Username'],$params[0]['ID']));
	if (!is_numeric($res)) {
		return $res;
	}

	return NULL;
}

?>
