<?php
# WiSP Users functions
# Copyright (C) 2007-2011, AllWorldIT
# 
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License along
# with this program; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

include_once("include/db.php");


# Return list of wisp users
function getWiSPUsers($params) {

	# Filters and sorts are the same here
	$filtersorts = array(
		'Username' => '@TP@users.Username',
		'Disabled' => '@TP@users.Disabled',
		'ID' => '@TP@wisp_userdata.UserID',
		'Firstname' => '@TP@wisp_userdata.Firstname',
		'Lastname' => '@TP@wisp_userdata.Lastname',
		'Email' => '@TP@wisp_userdata.Email',
		'Phone' => '@TP@wisp_userdata.Phone'
	);

	# Perform query
	$res = DBSelectSearch("
		SELECT 
			@TP@users.Username, 
			@TP@users.Disabled, 
			@TP@wisp_userdata.UserID, 
			@TP@wisp_userdata.FirstName, 
			@TP@wisp_userdata.LastName, 
			@TP@wisp_userdata.Email, 
			@TP@wisp_userdata.Phone  
		FROM 
			@TP@users, @TP@wisp_userdata
		WHERE
			@TP@wisp_userdata.UserID = @TP@users.ID
		",$params[1],$filtersorts,$filtersorts
	);
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

		$item['ID'] = $row->userid;
		$item['Username'] = $row->username;
		$item['Disabled'] = $row->disabled;
		$item['Firstname'] = $row->firstname;
		$item['Lastname'] = $row->lastname;
		$item['Email'] = $row->email;
		$item['Phone'] = $row->phone;

		# Push this row onto array
		array_push($resultArray,$item);
	}

	# Return results
	return array($resultArray,$numResults);
}

# Return specific wisp user row
function getWiSPUser($params) {

	# Query for userdata and username
	$res = DBSelect("
		SELECT 
			@TP@wisp_userdata.UserID, 
			@TP@wisp_userdata.FirstName, 
			@TP@wisp_userdata.LastName, 
			@TP@wisp_userdata.Phone, 
			@TP@wisp_userdata.Email, 
			@TP@wisp_userdata.LocationID,
			@TP@users.Username,
			@TP@users.Disabled
		FROM 
			@TP@wisp_userdata, @TP@users
		WHERE 
			@TP@wisp_userdata.UserID = ?
		AND
			@TP@users.ID = @TP@wisp_userdata.UserID
			",array($params[0])
	);

	# Return if error
	if (!is_object($res)) {
		return $res;
	}

	# Build array of results
	$resultArray = array();
	$row = $res->fetchObject();

	# Set userdata fields
	$resultArray['ID'] = $row->userid;
	$resultArray['Username'] = $row->username;
	$resultArray['Disabled'] = $row->disabled;
	if (isset($row->firstname)) {
		$resultArray['Firstname'] = $row->firstname;
	} else {
		$resultArray['Firstname'] = null;
	}
	if (isset($row->lastname)) {
		$resultArray['Lastname'] = $row->lastname;
	} else {
		$resultArray['Lastname'] = null;
	}
	if (isset($row->phone)) {
		$resultArray['Phone'] = $row->phone;
	} else {
		$resultArray['Phone'] = null;
	}
	if (isset($row->email)) {
		$resultArray['Email'] = $row->email;
	} else {
		$resultArray['Email'] = null;
	}
	if (isset($row->locationid)) {
		$resultArray['LocationID'] = $row->locationid;
	} else {
		$resultArray['LocationID'] = null;
	}
	
	# Password query
	$res = DBSelect("
		SELECT
			Value
		FROM
			@TP@user_attributes
		WHERE
			Name = ?
			AND @TP@user_attributes.UserID = ?
			",array('User-Password',$params[0])
	);

	# Return if error
	if (!is_object($res)) {
		return $res;
	}

	# Set password
	$row = $res->fetchObject();
	if (isset($row->value)) {
		$resultArray['Password'] = $row->value;
	} else {
		$resultArray['Password'] = null;
	}

	# Set number of results
	$numResults = count($resultArray);

	# Return results
	return array($resultArray,$numResults);
}

# Get wisp user attributes
function getWiSPUserAttributes($params) {

	# Attributes query
	$res = DBSelect("
		SELECT
			ID, Name, Operator, Value
		FROM
			@TP@user_attributes
		WHERE
			@TP@user_attributes.UserID = ?",
			array($params[0])
	);

	# Return if error
	if (!is_object($res)) {
		return $res;
	}

	# Set attributes
	$i = 0;
	$attributes = array();
	while ($row = $res->fetchObject()) {
		$attributes[$i] = array();
		$attributes[$i]['ID'] = $row->id;
		$attributes[$i]['Name'] = $row->name;
		$attributes[$i]['Operator'] = $row->operator;
		$attributes[$i]['Value'] = $row->value;
		$i++;
	}

	# Set number of results
	$numResults = count($attributes);

	# Return results
	return array($attributes,$numResults);
}

# Get wisp user groups
function getWiSPUserGroups($params) {

	# Groups query
	$res = DBSelect("
		SELECT
			@TP@groups.Name, @TP@groups.ID
		FROM
			@TP@users_to_groups, @TP@groups
		WHERE
			@TP@users_to_groups.GroupID = @TP@groups.ID
			AND @TP@users_to_groups.UserID = ?",
			array($params[0])
	);

	# Return if error
	if (!is_object($res)) {
		return $res;
	}

	# Set groups
	$i = 0;
	$groups = array();
	while ($row = $res->fetchObject()) {
		$groups[$i]['ID'] = $row->id;
		$groups[$i]['Name'] = $row->name;
		$i++;
	}

	# Set number of results
	$numResults = count($groups);

	# Return results
	return array($groups,$numResults);
}

# Remove wisp user
function removeWiSPUser($params) {

	# Begin transaction
	DBBegin();

	# Delete user information
	$res = DBDo("DELETE FROM @TP@wisp_userdata WHERE UserID = ?",array($params[0]));

	# Delete user attribtues
	if ($res !== FALSE) {
		$res = DBDo("DELETE FROM @TP@user_attributes WHERE UserID = ?",array($params[0]));
	}

	# Remove user from groups
	if ($res !== FALSE) {
		$res = DBDo("DELETE FROM @TP@users_to_groups WHERE UserID = ?",array($params[0]));
	}
	
	# Get list of topups and delete summaries
	if ($res !== FALSE) {
		$topupList = array();
		$res = DBSelect("
			SELECT
				@TP@topups_summary.TopupID
			FROM
				@TP@topups_summary, topups
			WHERE
				@TP@topups_summary.TopupID = @TP@topups.ID
				AND @TP@topups.UserID = ?",
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
							@TP@topups_summary
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
		$res = DBDo("DELETE FROM @TP@topups WHERE UserID = ?",array($params[0]));
	}

	# Delete user
	if ($res !== FALSE) {
		$res = DBDo("DELETE FROM @TP@users WHERE ID = ?",array($params[0]));
	}

	# Return result
	if ($res !== TRUE) {
		DBRollback();
		return $res;
	} else {
		DBCommit();
	}

	return NULL;
}

# Add wisp user
function createWiSPUser($params) {
	# We need this to send notification
	global $adminEmails;

	# Begin transaction
	DBBegin();
	# Perform first query
	$res = "Username required for single user. For adding multiple users an integer is required.";
	# If we adding single user
	if (empty($params[0]['Number']) && !empty($params[0]['Username'])) {
		# Insert username
		$res = DBDo("INSERT INTO @TP@users (Username,Disabled) VALUES (?,?)",array($params[0]['Username'],$params[0]['Disabled']));

		# Continue with others if successful
		if ($res !== FALSE) {
			$userID = DBLastInsertID();
			$res = DBDo("
				INSERT INTO 
						@TP@user_attributes (UserID,Name,Operator,Value) 
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
			$res = DBDo("INSERT INTO @TP@wisp_userdata (UserID) VALUES (?)",array($userID));
		}

		# Add personal information
		if ($res !== FALSE && isset($params[0]['Firstname'])) {
			$res = DBDo("UPDATE @TP@wisp_userdata SET FirstName = ? WHERE UserID = ?",array($params[0]['Firstname'],$userID));
		}
		if ($res !== FALSE && isset($params[0]['Lastname'])) {
			$res = DBDo("UPDATE @TP@wisp_userdata SET LastName = ? WHERE UserID = ?",array($params[0]['Lastname'],$userID));
		}
		if ($res !== FALSE && isset($params[0]['Phone'])) {
			$res = DBDo("UPDATE @TP@wisp_userdata SET Phone = ? WHERE UserID = ?",array($params[0]['Phone'],$userID));
		}
		if ($res !== FALSE && isset($params[0]['Email'])) {
			$res = DBDo("UPDATE @TP@wisp_userdata SET Email = ? WHERE UserID = ?",array($params[0]['Email'],$userID));
		}
		if ($res !== FALSE && !empty($params[0]['LocationID'])) {
			$res = DBDo("UPDATE @TP@wisp_userdata SET LocationID = ? WHERE UserID = ?",array($params[0]['LocationID'],$userID));
		}

		# Grab each attribute and add it's details to the database
		if ($res !== FALSE && count($params[0]['Attributes']) > 0) {
			foreach ($params[0]['Attributes'] as $attr) {

				# We only want to add attributes with all values
				if (
						isset($attr['Name']) && $attr['Name'] != "" &&
						isset($attr['Operator']) && $attr['Operator'] != "" &&
						isset($attr['Value']) && $attr['Value'] != ""
				) {
					# Default value without modifier
					$attrValue = $attr['Value'];

					if ($attr['Name'] == 'SMRadius-Capping-Traffic-Limit' || $attr['Name'] == 'SMRadius-Capping-Uptime-Limit') {
						# If modifier is set we need to work out attribute value
						if (isset($attr['Modifier'])) {
							switch ($attr['Modifier']) {
								case "Seconds":
									$attrValue = $attr['Value'] / 60;
									break;
								case "Minutes":
									$attrValue = $attr['Value'];
									break;
								case "Hours":
									$attrValue = $attr['Value'] * 60;
									break;
								case "Days":
									$attrValue = $attr['Value'] * 1440;
									break;
								case "Weeks":
									$attrValue = $attr['Value'] * 10080;
									break;
								case "Months":
									$attrValue = $attr['Value'] * 44640; 
									break;
								case "MBytes":
									$attrValue = $attr['Value'];
									break;
								case "GBytes":
									$attrValue = $attr['Value'] * 1000;
									break;
								case "TBytes":
									$attrValue = $attr['Value'] * 1000000;
									break;
							}
						}
					}

					# Add attribute
					$res = DBDo("
							INSERT INTO 
									@TP@user_attributes (UserID,Name,Operator,Value) 
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
		}

		# Link user to groups if any selected
		if ($res !== FALSE && count($params[0]['Groups']) > 0) {
			$refinedGroups = array();

			# Filter out unique group ID's
			foreach ($params[0]['Groups'] as $group) {
				foreach ($group as $ID=>$value) {
					$refinedGroups[$value] = $value;
				}
			}
			# Loop through groups
			foreach ($refinedGroups as $groupID) {
				$res = DBDo("INSERT INTO @TP@users_to_groups (UserID,GroupID) VALUES (?,?)",array($userID,$groupID));
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
								@TP@users.Username
							FROM 
								@TP@users
							WHERE 
								@TP@users.Username = ?
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
			$res = DBDo("INSERT INTO @TP@users (Username,Disabled) VALUES (?,?)",array($username,$params[0]['Disabled']));
			if ($res !== FALSE) {
				$id = DBLastInsertID();
				$res = DBDo("INSERT INTO @TP@user_attributes (UserID,Name,Operator,Value) VALUES (?,?,?,?)",
						array($id,'User-Password','==',$password)
				);

				# Grab each attribute and add it's details to the database
				if ($res !== FALSE && count($params[0]['Attributes']) > 0) {
					foreach ($params[0]['Attributes'] as $attr) {

						# We only want to add attributes with all values
						if (
								isset($attr['Name']) && $attr['Name'] != "" &&
								isset($attr['Operator']) && $attr['Operator'] != "" &&
								isset($attr['Value']) && $attr['Value'] != ""
						) {
							# Default value without modifier
							$attrValue = $attr['Value'];

							if ($attr['Name'] == 'SMRadius-Capping-Traffic-Limit' || $attr['Name'] == 'SMRadius-Capping-Uptime-Limit') {
								# If modifier is set we need to work out attribute value
								if (isset($attr['Modifier'])) {
									switch ($attr['Modifier']) {
										case "Seconds":
											$attrValue = $attr['Value'] / 60;
											break;
										case "Minutes":
											$attrValue = $attr['Value'];
											break;
										case "Hours":
											$attrValue = $attr['Value'] * 60;
											break;
										case "Days":
											$attrValue = $attr['Value'] * 1440;
											break;
										case "Weeks":
											$attrValue = $attr['Value'] * 10080;
											break;
										case "Months":
											$attrValue = $attr['Value'] * 44640; 
											break;
										case "MBytes":
											$attrValue = $attr['Value'];
											break;
										case "GBytes":
											$attrValue = $attr['Value'] * 1000;
											break;
										case "TBytes":
											$attrValue = $attr['Value'] * 1000000;
											break;
									}
								}
							}

							# Add attribute
							$res = DBDo("
									INSERT INTO 
											@TP@user_attributes (UserID,Name,Operator,Value) 
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
				}

				# Link user to groups if any selected
				if ($res !== FALSE && count($params[0]['Groups']) > 0) {
					$refinedGroups = array();

					# Filter out unique group ID's
					foreach ($params[0]['Groups'] as $group) {
						foreach ($group as $ID=>$value) {
							$refinedGroups[$value] = $value;
						}
					}
					# Loop through groups
					foreach ($refinedGroups as $groupID) {
						$res = DBDo("INSERT INTO @TP@users_to_groups (UserID,GroupID) VALUES (?,?)",array($id,$groupID));
					}
				}

				# Link to wisp users
				if ($res !== FALSE) {
					$res = DBDo("INSERT INTO @TP@wisp_userdata (UserID) VALUES (?)",
							array($id)
					);
				}
			}
		}

		# Email userlist to admin
		if ($res !== FALSE && isset($adminEmails)) {

			// multiple recipients
			$to = $adminEmails;

			// subject
			$subject = count($wispUser).' WiSP users added';

			// html
			$html = '';

			foreach ($wispUser as $key => $val) {
				$html .= '<tr><td>'.$key.'</td><td>'.$val.'</td></tr>';
			}

			// message
			$message = '
			<html>
				<head>
					<title>User List</title>
				</head>
				<body>
					<table cellspacing="10">
						<tr>
							<th>Username</th><th>Password</th>
						</tr>'.$html.'
					</table>
				</body>
			</html>
			';

			// To send HTML mail, the Content-type header must be set
			$headers = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

			// Additional headers
			$headers .= 'From: SMRadius';

			// Mail it
			$res = mail($to, $subject, $message, $headers);
		}
	}

	# Commit changes if all was successful, else rollback
	if ($res !== TRUE) {
		DBRollback();
		return $res;
	} else {
		DBCommit();
	}

	return NULL;
}

# Edit wisp user
function updateWiSPUser($params) {

	DBBegin();

	$res = TRUE;

	# Perform query
	$res = DBDo("UPDATE @TP@users SET Username = ?, Disabled = ? WHERE ID = ?",array($params[0]['Username'],$params[0]['Disabled'],$params[0]['ID']));

	# Change password
	if ($res !== FALSE) {
		$res = DBDo("UPDATE @TP@user_attributes SET Value = ? WHERE UserID = ? AND Name = ?",
				array($params[0]['Password'],$params[0]['ID'],'User-Password'));
	}
	# If successful, continue
	if ($res !== FALSE) {
		$res = DBDo("
				UPDATE
					@TP@wisp_userdata
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
	# If successful, add location if any
	if ($res !== FALSE && !empty($params[0]['LocationID'])) {
		$res = DBDo("UPDATE @TP@wisp_userdata SET LocationID = ? WHERE UserID = ?",
				array($params[0]['LocationID'],$params[0]['ID'])
		);
	}

	# Process attributes being removed
	if ($res !== FALSE && count($params[0]['RAttributes']) > 0) {
		foreach ($params[0]['RAttributes'] as $attr) {
			if ($res !== FALSE) {
				# Perform query
				$res = DBDo("DELETE FROM @TP@user_attributes WHERE ID = ?",array($attr));
			}
		}
	}

	# Process groups being removed
	if ($res !== FALSE && count($params[0]['RGroups']) > 0) {
		foreach ($params[0]['RGroups'] as $attr) {
			if ($res !== FALSE) {
				# Perform query
				$res = DBDo("
					DELETE FROM
						@TP@users_to_groups
					WHERE
						UserID = ?
						AND GroupID = ?",
						array($params[0]['ID'],$attr)
				);
			}
		}
	}

	# Grab each attribute and add it's details to the database
	if ($res !== FALSE && count($params[0]['Attributes']) > 0) {
		foreach ($params[0]['Attributes'] as $attr) {

			# We only want to add attributes with all values
			if (
					isset($attr['Name']) && $attr['Name'] != "" &&
					isset($attr['Operator']) && $attr['Operator'] != "" &&
					isset($attr['Value']) && $attr['Value'] != ""
			) {
				# Default value without modifier
				$attrValue = $attr['Value'];

				if ($attr['Name'] == 'SMRadius-Capping-Traffic-Limit' || $attr['Name'] == 'SMRadius-Capping-Uptime-Limit') {
					# If modifier is set we need to work out attribute value
					if (isset($attr['Modifier'])) {
						switch ($attr['Modifier']) {
							case "Seconds":
								$attrValue = $attr['Value'] / 60;
								break;
							case "Minutes":
								$attrValue = $attr['Value'];
								break;
							case "Hours":
								$attrValue = $attr['Value'] * 60;
								break;
							case "Days":
								$attrValue = $attr['Value'] * 1440;
								break;
							case "Weeks":
								$attrValue = $attr['Value'] * 10080;
								break;
							case "Months":
								$attrValue = $attr['Value'] * 44640; 
								break;
							case "MBytes":
								$attrValue = $attr['Value'];
								break;
							case "GBytes":
								$attrValue = $attr['Value'] * 1000;
								break;
							case "TBytes":
								$attrValue = $attr['Value'] * 1000000;
								break;
						}
					}
				}

				# Check if we adding or updating
				if ($res !== FALSE) {
					# We adding an attribute..
					if ($attr['ID'] < 0) {
						$res = DBDo("
							INSERT INTO 
								@TP@user_attributes (UserID,Name,Operator,Value) 
							VALUES
								(?,?,?,?)",
							array(
								$params[0]['ID'],
								$attr['Name'],
								$attr['Operator'],
								$attrValue
							)
						);
					# We updating an attribute..
					} else {
						$res = DBDo("
							UPDATE
								@TP@user_attributes
							SET
								Name = ?,
								Operator = ?,
								Value = ?
							WHERE
								ID = ?",
							array($attr['Name'],$attr['Operator'],$attrValue,$attr['ID'])
						);
					}
				}
			}
		}
	}

	# Link user to groups if any selected
	if ($res !== FALSE && count($params[0]['Groups']) > 0) {
		$refinedGroups = array();

		# Filter out unique group ID's
		foreach ($params[0]['Groups'] as $group) {
			foreach ($group as $ID=>$value) {
				$refinedGroups[$value] = $value;
			}
		}
		# Loop through groups
		foreach ($refinedGroups as $groupID) {
			if ($res !== FALSE) {
				$res = DBSelect("
					SELECT
						ID
					FROM
						@TP@users_to_groups
					WHERE
						UserID = ?
						AND GroupID = ?",
						array($params[0]['ID'],$groupID)
				);
			}

			if (is_object($res)) {
				if ($res->rowCount() == 0) {
					$res = DBDo("INSERT INTO @TP@users_to_groups (UserID,GroupID) VALUES (?,?)",array($params[0]['ID'],$groupID));
				} else {
					$res = TRUE;
				}
			}
		}
	}

	# Commit changes if all was successful, else break
	if ($res !== TRUE) {
		DBRollback();
		return $res;
	} else {
		DBCommit();
	}

	return NULL;
}

# vim: ts=4
