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

	$res = DBSelect("
				SELECT 
					wisp_userdata.UserID, 
					wisp_userdata.FirstName, 
					wisp_userdata.LastName, 
					wisp_userdata.Phone, 
					wisp_userdata.Email, 
					users.Username
				FROM 
					wisp_userdata, users
				WHERE 
					wisp_userdata.UserID = ?
				AND
					users.ID = wisp_userdata.UserID
					",array($params[0])
	);

	if (!is_object($res)) {
		return $res;
	}

	$resultArray = array();

	$row = $res->fetchObject();

	$resultArray['ID'] = $row->userid;
	$resultArray['Username'] = $row->username;
	$resultArray['Firstname'] = $row->firstname;
	$resultArray['Lastname'] = $row->lastname;
	$resultArray['Phone'] = $row->phone;
	$resultArray['Email'] = $row->email;
	
	$res = DBSelect("
				SELECT
					user_attributes.Name,
					user_attributes.Value
				FROM
					user_attributes
				WHERE
					user_attributes.UserID = ?
					",array($params[0])
	);

	if (!is_object($res)) {
		return $res;
	}

	while ($row = $res->fetchObject()) {
		switch ($row->name) {
			case "User-Password":
				$resultArray['Password'] = $row->value;
				break;
			case "MACAddress":
				$resultArray['MACAddress'] = $row->value;
				break;
			case "SMRadius-Capping-Traffic-Limit":
				$resultArray['Datalimit'] = $row->value;
				break;
			case "SMRadius-Capping-Uptime-Limit":
				$resultArray['Uptimelimit'] = $row->value;
				break;
			case "Framed-IP-Address":
				$resultArray['IPAddress'] = $row->value;
				break;
		}
	}

	foreach ( array('Password','MACAddress','Datalimit','Uptimelimit','IPAddress') as $key) {
		if (!isset($resultArray[$key])) {
			$resultArray[$key] = '';
		}
	}
 
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
	# Insert username
	$res = DBDo("INSERT INTO users (Username) VALUES (?)",array($params[0]['Username']));

	# Continue with others if successful
	if ($res !== FALSE) {
		$userID = DBLastInsertID();
		DBDo("
			INSERT INTO 
					user_attributes (UserID,Name,Operator,Value) 
			VALUES 
					(?,?,?,?)",
			array($userID,
				'User-Password',
				'==',
				$params[0]['Password'])
		);
	}

	# Link users ID to make user a wisp user
	if ($res !== FALSE) {
		DBDo("INSERT INTO wisp_userdata (UserID) VALUES (?)",array($userID));
	}

	# Personal information is optional when adding
	if ($res !== FALSE && isset($params[0]['Firstname'])) {
		$res = DBDo("UPDATE wisp_userdata SET FirstName = ? WHERE UserID = ?",array($params[0]['Firstname'],$userID));
	}
	if ($res !== FALSE && isset($params[0]['Lastname'])) {
		$res = DBDo("UPDATE wisp_userdata SET LastName = ? WHERE UserID = ?",array($params[0]['Lastname'],$userID));
	}
	if ($res !== FALSE && isset($params[0]['Phone'])) {
		$res = DBDo("UPDATE wisp_userdata SET Phone = ? WHERE UserID = ?",array($params[0]['Phone'],$userID));
	}
	if ($res !== FALSE && isset($params[0]['Email'])) {
		$res = DBDo("UPDATE wisp_userdata SET Email = ? WHERE UserID = ?",array($params[0]['Email'],$userID));
	}

	# Grab each attribute and add it's details to the database
	if ($res !== FALSE) {
		foreach ($params[0]['Attributes'] as $attr) {
			$res = DBDo("
						INSERT INTO 
								user_attributes (UserID,Name,Operator,Value) 
						VALUES
								(?,?,?,?)",
						array(
							$userID,
							$attr['Name'],
							$attr['Operator'],
							$attr['Value'])
			);
		}
	}

	# Commit changes if all was successful, else break
	if ($res !== FALSE) {
		DBCommit();
		return $res;
	} else {
		DBRollback();
	}

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
