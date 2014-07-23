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
App::import('Model','Group');



/**
 * @class UserGroup
 *
 * @brief This class manages default table, validation and methods.
 */
class UserGroup extends AppModel
{

	// This variable is used for including table.
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
	 * @return $groups
	 */
	public function selectGroup()
	{
		try {
			$objGroup = new Group();
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
	 * @method getGroupById
	 * This method is used for fetching group name.
	 * @param $groupId
	 * @return $userGroups
	 */
	public function getGroupById($groupId)
	{
		try {
			$objGroup = new Group();
			$userGroups = $objGroup->find(
				'all',
				array(
					'fields' => array(
						'ID',
						'Name'
					),
					'conditions' => array(
						'ID' => $groupId
					)
				)
			);
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
		return $userGroups;
	}



}



// vim: ts=4