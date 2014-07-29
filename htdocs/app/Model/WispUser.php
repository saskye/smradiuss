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



// Import another model.
App::import('Model','User');
App::import('Model','WispLocation');
App::import('Model','UserAttribute');
App::import('Model','WispUsersTopup');
App::import('Model','UserGroup');
App::import('Model','Group');
App::import('Model','GroupMember');



/**
 * @class WispUser
 *
 * @brief This class manages default table, validation and methods.
 */
class WispUser extends AppModel
{

	// This variable is used for including table.
	public $useTable = 'wisp_userdata';


	/**
	 * @method selectById
	 * This method is used for fetching record from users table.
	 * @param $userId
	 * @return $userData
	 */
	public function selectById($userId)
	{
		try {
			// This variable is used for create User class object.
			$objUser = new User();


			// This variable is used for get data.
			$userData = $objUser->find(
				'first',
				array(
					'conditions' => array(
						'ID' => $userId
					)
				)
			);
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
		return $userData;
	}



	/**
	 * @method selectLocation
	 * This method is used for fetching all locations data.
	 * @return $locationData
	 */
	public function selectLocation()
	{
		try {
			// This variable is used for create WispLocation class object.
			$objLocation = new WispLocation();


			// This variable is used for get data.
			$locationData = $objLocation->find('all');
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
		return $locationData;
	}



	/**
	 * @method insertUsername
	 * This method is used for insert username in table and get its id.
	 * @param $userName
	 * @return $lastInsertID
	 */
	public function insertUsername($userName)
	{
		try {
			// This variable is used for create User class object.
			$objUser = new User();


			$objUser->set('Username', $userName);
			$objUser->save();

			// This variable is used for get last inserted id.
			$lastInsertID = $objUser->getLastInsertID();
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
		return $lastInsertID;
	}



	/**
	 * @method addValue
	 * This method is used for insert attribute data in table.
	 * @param $userId
	 * @param $attName
	 * @param $attoperator
	 * @param $password
	 * @param $modifier
	 */
	public function addValue($userId, $attName, $attoperator, $password, $modifier = '')
	{
		try {
			// This variable is used for create UserAttribute class object.
			$objUserAttribute = new UserAttribute();


			$objUserAttribute->set(
				array(
					'UserID' => $userId,
					'Name' => $attName,
					'Operator' => $attoperator,
					'Value' => $password,
					'Disabled' => '0',
					'modifier' => $modifier
				)
			);
			$objUserAttribute->save();
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
	}



	/**
	 * @method getValue
	 * This method is used for fetching value form table.
	 * @param $userId
	 * @return $valueData
	 */
	public function getValue($userId)
	{
		try {
			// This variable is used for create UserAttribute calss object.
			$objUserAttribute = new UserAttribute();


			// This variable is used for get data.
			$valueData = $objUserAttribute->find(
				'first',
				array(
					'conditions' => array(
						'UserID' => $userId
					),
					'fields' => array(
						'Value'
					)
				)
			);
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
		return $valueData;
	}



	/**
	 * @method updateUsername
	 * This method is used for update username.
	 * @param $userId
	 * @param $userName
	 */
	public function updateUsername($userId, $userName)
	{
		try {
			// This variable is used for create User class object.
			$objUser = new User();


			$objUser->updateAll(
				array(
					'Username' => "'$userName'"
				),
				array(
					'ID' => $userId
				)
			);
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
	}



	/**
	 * @method updateValue
	 * This method is used for update value.
	 * @param $userId
	 * @param $userValue
	 */
	public function updateValue($userId, $userValue)
	{
		try {
			// This variable is used for create UserAttribute class object.
			$objUserAttribute = new UserAttribute();


			$objUserAttribute->updateAll(
				array(
					'Value' => "'$userValue'"
				),
				array(
					'UserID' => $userId
				)
			);
		} catch (exception $ex) {
			throw new exception('Error in query');
		}
	}



	/**
	 * @method deleteUserAttributes
	 * This method is used for deleteing atribute.
	 * @param $userId
	 */
	public function deleteUserAttributes($userId)
	{
		try {
			// This variable is used for create UserAttribute class object.
			$objUserAttribute = new UserAttribute($userId);


			$objUserAttribute->delete();
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
	}



	/**
	 * @method deleteUsers
	 * This method is used for delete user record form all related tables.
	 * @param $usreId
	 */
	public function deleteUsers($userId)
	{
		try {
			// This variable is used for create UserAttribute class object.
			$objUserAttribute = new UserAttribute();


			$objUserAttribute->deleteAll(array('UserID' => $userId),false);

			// This variable is used for create UserGroup class object.
			$objUserGroup = new UserGroup();


			$objUserGroup->deleteAll(array('UserID' => $userId),false);

			// This variable is used for create WispUsersTopup class object.
			$objWispUsersTopup = new WispUsersTopup();


			$objWispUsersTopup->deleteAll(array('UserID' => $userId),false);


			// This variable is used for create User class object.
			$objUser = new User();


			$objUser->delete(array('ID' => $userId));
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
	}



	/**
	 * @method getUserName
	 * This method is used for check if username used.
	 * @param $userName
	 * @return $usernameCountCheck
	 */
	public function getUsername($userName)
	{
		try {
			// This variable is used for create User class object.
			$objUser = new User();


			// This variable is used for get data.
			$usernameCountCheck = $objUser->find(
				'count',
				array(
					'conditions' => array(
						'Username' => $userName
					)
				)
			);
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
		return $usernameCountCheck;
	}



	/**
	 * @method selectGroup
	 * This method is used for fetching all groups.
	 * @return $groups
	 */
	public function selectGroup()
	{
		try {
			// This variable is used for create Group class object.
			$objGroup = new Group();


			// This variable is used for get data.
			$groups = $objGroup->find(
				'all',
				array(
					'fields' => array(
						'ID',
						'Name'
					)
				)
			);
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
		return $groups;
	}



	/**
	 * @method selectUserGroups
	 * This method is used for select user group.
	 * @param $userId
	 * @return $arr
	 */
	public function selectUserGroups($userId)
	{
		try {
			// This variable is used for create Group class object.
			$objGroup = new Group();


			// This variable is used for create GroupMember class object.
			$objGroupMember = new GroupMember();


			// This variable is used for get data.
			$groupMember = $objGroupMember->find(
				'first',
				array(
					'conditions' => array(
						'UserID' => $userId
					),
					'fields' => array(
						'ID',
						'UserID',
						'GroupID',
						'Disabled',
						'Comment'
					)
				)
			);


			if ($groupMember) {
				// This variable is used for get data.
				$group = $objGroup->find(
					'first',
					array(
						'conditions' => array(
							'ID' => $groupMember['GroupMember']['GroupID']
						),
						'fields' => array(
							'name'
						)
					)
				);

				// This variable is used for merges two arrays into one array.
				$userGroup = array_merge($groupMember,$group);


				// This variable is used for creates an array.
				$arr = array($userGroup);
			}
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
		return $arr;
	}



	/**
	 * @method selectUserAttributes
	 * This method is used for select user attributes.
	 * @param $userId
	 * @return $userAttribute
	 */
	public function selectUserAttributes($userId)
	{
		try {
			// This variable is used for create UserAttribute class object.
			$objUserAttribute = new UserAttribute();


			// This variable is used for get data.
			$userAttribute = $objUserAttribute->find(
				'all',
				array(
					'conditions' => array(
						'UserID' => $userId
					)
				)
			);
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
		return $userAttribute;
	}



	/**
	 * @method insertUserGroup
	 * This method is used for add group.
	 * @param $userId
	 * @param groupId
	 */
	public function insertUserGroup($userId, $groupId)
	{
		try {
			// This varialble is used for create GroupMember class object.
			$objGroupMember = new GroupMember();


			$objGroupMember->set(
				array(
					'UserID' => $userId,
					'GroupID' => $groupId,
					'Disabled' => '0',
					'Comment' => ''
				)
			);
			$objGroupMember->save();
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
	}



	/**
	 * @method deleteUserGroup
	 * This method is used for delete group.
	 * @param $userId
	 */
	public function deleteUserGroup($userId)
	{
		try {
			// This variable is used for create UserGroup class object.
			$objUserGroup = new UserGroup();


			$objUserGroup->deleteAll(
				array(
					'UserID' => $userId
				),
				false
			);
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
	}



	/**
	 * @method deleteUserAttibute
	 * This method is used for delete attributes.
	 * @param $userId
	 */
	public function deleteUserAttibute($userId)
	{
		try {
			// This variable is used for create UserAttribute class object.
			$objUserAttribute = new UserAttribute();


			$objUserAttribute->deleteAll(
				array(
					'UserID' => $userId
				),
				false
			);
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
	}
}



// vim: ts=4
