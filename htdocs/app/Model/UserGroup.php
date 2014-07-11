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
 * @class UserGroup
 *
 * @brief This class manages default table, validation and methods.
 */
class UserGroup extends AppModel
{

	/**
	 * @var $useTable
	 * This variable is used for including table.
	 */
	public $useTable = 'users_to_groups';


	// Validating form controllers.
	public $validate = array(
		'Type' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'message' => 'Please enter value'
			)
		)
	);



	/**
	 * @method selectGroup
	 * This method is used for fetching all groups.
	 * @return $res
	 */
	public function selectGroup()
	{
		try {
			$res = $this->query("SELECT ID, Name FROM groups");
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
		return $res;
	}



	/**
	 * @method getGroupById
	 * This method is used for fetching group name.
	 * @param $groupId
	 * @return $res
	 */
	public function getGroupById($groupId)
	{
		try {
			$res = $this->query("SELECT ID,Name FROM groups WHERE ID = ".$groupId);
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
		return $res;
	}



	/**
	 * @method insertRec
	 * This method is used for saving user groups.
	 * @param $userId
	 * @param $data
	 */
	public function insertRec($userId, $data)
	{
		try {
			$res = $this->query("
				INSERT INTO users_to_groups
					(
						UserID,
						GroupID
					)
				VALUES
					(
						'".$userId."',
						'".$data['UserGroup']['Type']."'
					)
			");
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
	}
}



// vim: ts=4
