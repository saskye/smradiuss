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

	# Query for userdata and username
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

	# Set userdata fields
	$resultArray['ID'] = $row->userid;
	$resultArray['Username'] = $row->username;
	$resultArray['Firstname'] = $row->firstname;
	$resultArray['Lastname'] = $row->lastname;
	$resultArray['Phone'] = $row->phone;
	$resultArray['Email'] = $row->email;
	$resultArray['Attributes'] = array();
	
	# Query to get user password
	$res = DBSelect("
				SELECT
					user_attributes.Value
				FROM
					user_attributes
				WHERE
					user_attributes.Name = 'User-Password'
				AND
					user_attributes.UserID = ?
					",array($params[0])
	);

	if (!is_object($res)) {
		return $res;
	}

	# Set user password field
	$row = $res->fetchObject();
	$resultArray['Password'] = $row->value;

	# Query to get all other attributes
	$res = DBSelect("
				SELECT
					user_attributes.ID,
					user_attributes.Name,
					user_attributes.Operator,
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

	$i = 0;
	# Array for multiple attributes
	while ($row = $res->fetchObject()) {
		$resultsArray['Attributes'][$i]['ID'] = $row->id;
		$resultsArray['Attributes'][$i]['Name'] = $row->name;
		$resultsArray['Attributes'][$i]['Operator'] = $row->operator;
		$resultsArray['Attributes'][$i]['Value'] = $row->value;
		$i++;
	}

	$numResults = $res->rowCount();
	return array($resultArray,$numResults);
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
	if ($res !== FALSE) {
		$res = "Failed to add 'Username'";
	}

	# Continue with others if successful
	if ($res !== FALSE) {
		$userID = DBLastInsertID();
		$res = DBDo("
			INSERT INTO 
					user_attributes (UserID,Name,Operator,Value) 
			VALUES 
					(?,?,?,?)",
			array($userID,
				'User-Password',
				'==',
				$params[0]['Password'])
		);
		if ($res !== FALSE) {
			$res = "Failed to add 'User-Password' attribute";
		}
	}

	# Link users ID to make user a wisp user
	if ($res !== FALSE) {
		$res = DBDo("INSERT INTO wisp_userdata (UserID) VALUES (?)",array($userID));
		if ($res !== FALSE) {
			$res = "Failed to link to wisp users";
		}
	}

	# Personal information is optional when adding
	if ($res !== FALSE && isset($params[0]['Firstname'])) {
		$res = DBDo("UPDATE wisp_userdata SET FirstName = ? WHERE UserID = ?",array($params[0]['Firstname'],$userID));
		if ($res !== FALSE) {
			$res = "Failed to add 'Firstname'";
		}
	}
	if ($res !== FALSE && isset($params[0]['Lastname'])) {
		$res = DBDo("UPDATE wisp_userdata SET LastName = ? WHERE UserID = ?",array($params[0]['Lastname'],$userID));
		if ($res !== FALSE) {
			$res = "Failed to add 'Lastname'";
		}
	}
	if ($res !== FALSE && isset($params[0]['Phone'])) {
		$res = DBDo("UPDATE wisp_userdata SET Phone = ? WHERE UserID = ?",array($params[0]['Phone'],$userID));
		if ($res !== FALSE) {
			$res = "Failed to add 'Phone'";
		}
	}
	if ($res !== FALSE && isset($params[0]['Email'])) {
		$res = DBDo("UPDATE wisp_userdata SET Email = ? WHERE UserID = ?",array($params[0]['Email'],$userID));
		if ($res !== FALSE) {
			$res = "Failed to add 'Email'";
		}
	}

	# Grab each attribute and add it's details to the database
	if ($res !== FALSE && isset($params[0]['Attributes'])) {
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
			if ($res !== FALSE) {
				$res = "Failed to add 'Attribute'";
			}
		}
	}

	# Link user to groups if any selected
	if (isset($params[0]['Groups'])) {
		$refinedGroups = array();

		# Filter out unique group ID's
		foreach ($params[0]['Groups'] as $groupID) {
			$refinedGroups[$groupID] = $groupID;
		}
		foreach ($refinedGroups as $groupID) {
			if ($res !== FALSE) {
				$res = DBDo("INSERT INTO users_to_groups (UserID,GroupID) VALUES (?,?)",array($userID,$groupID['Name']));
				if ($res !== FALSE) {
					$res = "Failed to add 'Group'";
				}
			}
		}
	}

	# Commit changes if all was successful, else break
	if ($res === TRUE) {
		DBCommit();
		return NULL;
	} else {
		DBRollback();
	}

	return $res;
}

# Edit admin group
function updateWiSPUser($params) {
	global $db;

	DBBegin();
	$res = DBDo("UPDATE users SET Username = ? WHERE ID = ?",array($params[0]['Username'],$params[0]['ID']));
	if ($res !== FALSE) {
		DBDo("UPDATE user_attributes SET User-Password = ? WHERE UserID = ?",array($params[0]['Username'],$params[0]['ID']));
	}
	if ($res !== FALSE) {
		DBDo("
			UPDATE
				wisp_userdata
			SET
				FirstName = ?,
				LastName = ?,
				Phone = ?,
				Email = ?
			WHERE
				UserID = ?",
			array($params[0]['Firstname'],
			$params[0]['Lastname'],
			$params[0]['Phone'],
			$params[0]['Email'],
			$params[0]['ID'])
		);
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

# vim: ts=4
