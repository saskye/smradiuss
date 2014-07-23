<?php
/**
 * Copyright (c) 2014, AllWorldIT
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */



/**
 * Wisp User Model
 *
 */

class WispUser extends AppModel
{
	public $useTable = 'wisp_userdata';

	//Validating form controllers.
	public $validate = array(
		'Username' => array(
			'required' => array(
				'rule' => array(
					'notEmpty'
				),
				'message' => 'Please choose a username'
			),
			'unique' => array(
				'rule' => array(
					'uniqueCheck'
				),
				'message' => 'The username you have chosen has already been registered'
			)
		)
	);



	//Check username is exist or not in table.
	public function uniqueCheck($Username)
	{
		$res = $this->query("SELECT COUNT(ID) FROM users WHERE Username = ?", array($Username['Username']));

		if ($res[0][0]['count(ID)'] >= 1) {
			return false;
		} else {
			return true;
		}
	}



	//Fetching record from users table.
	public function selectById($userId)
	{
		return $this->query("SELECT Username, Disabled FROM users WHERE ID = ?", array($userId));
	}



	//Fetching all locations for select box controller.
	public function selectLocation()
	{
		 return $this->query("SELECT * FROM wisp_locations");
	}



	//Inser username in table and get its id.
	public function insertUsername($userName)
	{
		$res = $this->query("
			INSERT INTO users
				(
					Username
				)
			VALUES
				(
					?
				)
			",
			array($userName)
		);

		return $this->getLastInsertID();
	}



	//Inser data in wisp_userdata table.
	public function insertRec($data)
	{
		$res = $this->query("
			INSERT INTO wisp_userdata
				(
					UserID,
					LocationID,
					FirstName,
					LastName,
					Email,
					Phone
				)
			VALUES
				(
					?,
					?,
					?,
					?,
					?,
					?
				)
			",array(
				$data['WispUser']['UserId'],
				$data['WispUser']['Location'],
				$data['WispUser']['FirstName'],
				$data['WispUser']['LastName'],
				$data['WispUser']['Email'],
				$data['WispUser']['Phone']
			)
		);
	}



	//Update wisp_userdata table.
	public function updateRec($data, $userId)
	{
		$res = $this->query("
			UPDATE wisp_userdata SET
				LocationID = ?
				FirstName = ?
				LastName = ?
				Email = ?
				Phone = ?
			WHERE
				UserID = ?
			",array(
				$data['WispUser']['Location'],
				$data['WispUser']['FirstName'],
				$data['WispUser']['LastName'],
				$data['WispUser']['Email'],
				$data['WispUser']['Phone'],
				$userId
			)
		);
	}



	//Insert attribute data in table.
	public function addValue($userId, $attName, $attoperator, $password, $modifier = '')
	{
		$res = $this->query("
				INSERT INTO user_attributes
					(
						UserID,
						Name,
						Operator,
						Value,
						Disabled,
						modifier
					)
				VALUES
					(
						?,
						?,
						?,
						?,
						?,
						?
					)
			",array(
				$userId,
				$attName,
				$attoperator,
				$password,
				0,
				$modifier
			)
		);
	}



	//Fetching value from table.
	public function getValue($userId)
	{
		 return $this->query("SELECT Value FROM user_attributes WHERE UserID = ?", array($userId));
	}



	//Update username.
	public function updateUsername($userId, $userName)
	{
		$res = $this->query("UPDATE users SET Username = ? WHERE ID = ?", array($userName, $userId));
	}



	//Update value.
	public function updateValue($userId, $userValue)
	{
		$res = $this->query("UPDATE user_attributes SET Value = ? WHERE UserID = ?", array($userValue, $userId));
	}



	//Fetching user id for delete record.
	public function fetchUserId($id)
	{
		return $this->query("SELECT UserID FROM wisp_userdata WHERE ID = ?", array($id));
	}



	//Deleting attribute.
	public function deleteUserAttributes($userId)
	{
		$res = $this->query("DELETE FROM user_attributes WHERE UserID = ?", array($userId));
	}



	//Delete user record from all related tables.
	public function deleteUsers($userId)
	{
		$res = $this->query("DELETE FROM users WHERE ID = ?", array($userId));
		$res = $this->query("DELETE FROM user_attributes WHERE UserID = ?", array($userId));
		$res = $this->query("DELETE FROM users_to_groups WHERE UserID = ?", array($userId));
		$res = $this->query("DELETE FROM topups WHERE UserID = ?", array($userId));
	}



	// Check if username used
	public function getUserName($userName)
	{
		$res = $this->query("SELECT Username FROM users WHERE Username = ?", array($userName));
		return count($res);
	}



	// Fetching all groups to fill select control.
	public function selectGroup()
	{
		return $this->query("SELECT ID, Name FROM groups");
	}



	// Select user group from user id
	public function selectUserGroups($userId)
	{
		return $this->query("SELECT *,g.name FROM users_to_groups as utg , groups as g WHERE UserID = ".$userId." AND g.ID = utg.GroupID",false);
	}



	//Select user attributes.
	public function selectUserAttributes($userId)
	{
		return $this->query("
			SELECT
				ID,
				UserID,
				GroupID,
				Disabled,
				Comment,
				g.name
			FROM
				user_attributes
			WHERE
				UserID = ?
			", array($userId)
		);
	}



	//Add group
	public function insertUserGroup($userId, $groupId)
	{
		$res = $this->query("
				INSERT INTO users_to_groups
					(
						UserID,
						GroupID,
						Disabled,
						Comment
					)
				VALUES
					(
						?,
						?,
						?,
						?
					)
			", array($userId, $groupId, '0', '')
		);
	}



	//Delete group.
	public function deleteUserGroup($userId)
	{
		 $res = $this->query("DELETE FROM users_to_groups WHERE UserID = ?", array($userId));
	}



	//Delete attributes.
	public function deleteUserAttibute($userId)
	{
		$res = $this->query("DELETE FROM user_attributes WHERE UserID = ? AND Name != ?", array($userId, 'User-Password'));
	}



}



// vim: ts=4
