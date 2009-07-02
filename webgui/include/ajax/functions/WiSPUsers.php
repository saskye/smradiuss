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
					wisp_userdata.LocationID,
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
	$resultArray['LocationID'] = $row->locationid;
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

	# Begin transaction
	DBBegin();

	# Delete user information
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

# Add wisp user
function createWiSPUser($params) {
	global $db;

	DBBegin();
	$res = "Username & Password required for single user. For adding multiple users an integer is required.";
	# If we adding single user
	if (empty($params[0]['Number']) && !empty($params[0]['Password']) && !empty($params[0]['Username'])) {
		# Insert username
		$res = DBDo("INSERT INTO users (Username) VALUES (?)",array($params[0]['Username']));

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
		}

		# Link users ID to make user a wisp user
		if ($res !== FALSE) {
			$res = DBDo("INSERT INTO wisp_userdata (UserID) VALUES (?)",array($userID));
		}

		# Add personal information
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
		if ($res !== FALSE && isset($params[0]['LocationID'])) {
			$res = DBDo("UPDATE wisp_userdata SET LocationID = ? WHERE UserID = ?",array($params[0]['LocationID'],$userID));
		}

		# Grab each attribute and add it's details to the database
		if ($res !== FALSE && isset($params[0]['Attributes'])) {
			foreach ($params[0]['Attributes'] as $attr) {

				# Default value without modifier
				$attrValue = $attr['Value'];

				if ($attr['Name'] == 'SMRadius-Capping-Traffic-Limit' || $attr['Name'] == 'SMRadius-Capping-Uptime-Limit') {
					# If modifier is set we need to work out attribute value
					if (isset($attr['Modifier'])) {
						switch ($attr['Modifier']) {
							case "Seconds":
								$attrValue = $attr['Value'] / 60;
							case "Minutes":
								$attrValue = $attr['Value'];
							case "Hours":
								$attrValue = $attr['Value'] * 60;
							case "Days":
								$attrValue = $attr['Value'] * 1440;
							case "Weeks":
								$attrValue = $attr['Value'] * 10080;
							case "Months":
								$attrValue = $attr['Value'] * 44640; 
							case "MBytes":
								$attrValue = $attr['Value'];
							case "GBytes":
								$attrValue = $attr['Value'] * 1000;
							case "TBytes":
								$attrValue = $attr['Value'] * 1000000;
						}
					}
				}

				# Add attribute
				$res = DBDo("
						INSERT INTO 
								user_attributes (UserID,Name,Operator,Value) 
						VALUES
								(?,?,?,?)",
						array(
							$userID,
							$attr['Name'],
							$attr['Operator'],
							$attrValue
						)
				);
			}
		}

		# Link user to groups if any selected
		if ($res !== FALSE && isset($params[0]['Groups'])) {
			$refinedGroups = array();

			# Filter out unique group ID's
			foreach ($params[0]['Groups'] as $group) {
				foreach ($group as $ID=>$value) {
					$refinedGroups[$value] = $value;
				}
			}
			# Loop through groups
			foreach ($refinedGroups as $groupID) {
				$res = DBDo("INSERT INTO users_to_groups (UserID,GroupID) VALUES (?,?)",array($userID,$groupID));
			}
		}

	# We adding multiple users
	} elseif (!empty($params[0]['Number']) && $params[0]['Number'] > 1) {
		$wispUser = array();
		# Loop for number of chosen numbers
		for ($i = 0; $i < $params[0]['Number']; $i++) {

			# Check for duplicates and add
			$usernameReserved = 1;
			$characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
			while ($usernameReserved == 1) {

				# Generate random username
				$string = '';
				for ($c = 0; $c < 7; $c++) {
					$string .= $characters[rand(0, strlen($characters) - 1)];
				}

				$thisUsername = $string;
				# Add prefix to string
				if (!empty($params[0]['Prefix'])) {
					$thisUsername = $params[0]['Prefix'].$string;
				}

				# Check if username used
				$res = DBSelect("
							SELECT 
								users.Username
							FROM 
								users
							WHERE 
								users.Username = ?
								",array($thisUsername)
				);

				# If there are no rows we may continue
				if ($res->rowCount() == 0 && !isset($wispUser[$thisUsername])) {
					$usernameReserved = 0;

					# Generate random username
					$string = '';
					for ($c = 0; $c < 7; $c++) {
						$string .= $characters[rand(0, strlen($characters) - 1)];
					}

					# Add username and password onto array
					$wispUser[$thisUsername] = $string;
				}
			}
		}

		# Insert users from array into database
		foreach ($wispUser as $username => $password) {
			$res = DBDo("INSERT INTO users (Username) VALUES (?)",array($username));
			if ($res !== FALSE) {
				$id = DBLastInsertID();
				$res = DBDo("INSERT INTO user_attributes (UserID,Name,Operator,Value) VALUES (?,?,?,?)",
						array($id,'User-Password','==',$password)
				);

				# Grab each attribute and add it's details to the database
				if ($res !== FALSE && isset($params[0]['Attributes'])) {
					foreach ($params[0]['Attributes'] as $attr) {

						# Default value without modifier
						$attrValue = $attr['Value'];

						if ($attr['Name'] == 'SMRadius-Capping-Traffic-Limit' || $attr['Name'] == 'SMRadius-Capping-Uptime-Limit') {
							# If modifier is set we need to work out attribute value
							if (isset($attr['Modifier'])) {
								switch ($attr['Modifier']) {
									case "Seconds":
										$attrValue = $attr['Value'] / 60;
									case "Minutes":
										$attrValue = $attr['Value'];
									case "Hours":
										$attrValue = $attr['Value'] * 60;
									case "Days":
										$attrValue = $attr['Value'] * 1440;
									case "Weeks":
										$attrValue = $attr['Value'] * 10080;
									case "Months":
										$attrValue = $attr['Value'] * 44640; 
									case "MBytes":
										$attrValue = $attr['Value'];
									case "GBytes":
										$attrValue = $attr['Value'] * 1000;
									case "TBytes":
										$attrValue = $attr['Value'] * 1000000;
								}
							}
						}

						# Add attribute
						$res = DBDo("
								INSERT INTO 
										user_attributes (UserID,Name,Operator,Value) 
								VALUES
										(?,?,?,?)",
								array(
									$id,
									$attr['Name'],
									$attr['Operator'],
									$attrValue
								)
						);
					}
				}

				# Link user to groups if any selected
				if ($res !== FALSE && isset($params[0]['Groups'])) {
					$refinedGroups = array();

					# Filter out unique group ID's
					foreach ($params[0]['Groups'] as $group) {
						foreach ($group as $ID=>$value) {
							$refinedGroups[$value] = $value;
						}
					}
					# Loop through groups
					foreach ($refinedGroups as $groupID) {
						$res = DBDo("INSERT INTO users_to_groups (UserID,GroupID) VALUES (?,?)",array($id,$groupID));
					}
				}
				# Link to wisp users
				if ($res !== FALSE) {
					$res = DBDo("INSERT INTO wisp_userdata (UserID) VALUES (?)",
							array($id)
					);
				}
			}
		}
	}

	# Commit changes if all was successful, else rollback
	if ($res !== FALSE) {
		DBCommit();
		return $res;
	} else {
		DBRollback();
	}

	return NULL;
}

# Edit wisp user
function updateWiSPUser($params) {
	global $db;

	DBBegin();
	$res = DBDo("UPDATE users SET Username = ? WHERE ID = ?",array($params[0]['Username'],$params[0]['ID']));
	if ($res !== FALSE) {
		$res = DBDo("UPDATE user_attributes SET User-Password = ? WHERE UserID = ?",array($params[0]['Username'],$params[0]['ID']));
	}
	if ($res !== FALSE) {
		$res = DBDo("
				UPDATE
					wisp_userdata
				SET
					FirstName = ?,
					LastName = ?,
					Phone = ?,
					Email = ?,
					LocationID = ?
				WHERE
					UserID = ?",
				array($params[0]['Firstname'],
				$params[0]['Lastname'],
				$params[0]['Phone'],
				$params[0]['Email'],
				$params[0]['LocationID'],
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
