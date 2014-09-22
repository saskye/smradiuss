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
App::import('Model','UserGroup');



/**
 * @class Group
 * brief This class manages table name, validation and mehtod.
 */
class Group extends AppModel
{
	// Variable is used for including table.
	public $useTable = 'groups';


	//Validating form controller.
	public $validate = array(
		'Name' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'message' => 'Please enter name.'
			),
			'unique' => array(
				'rule' => 'isUnique',
				'on' => 'create',
				'message' => 'The group name you have chosen has already been registered'
			)
		),
		'Priority' => array(
			'rule' => 'numeric',
			'required' => true,
			'message' => 'Please enter number only.'
		),
		'Comment' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'message' => 'Please enter comment.'
			)
		)
	);



	/**
	 * @method deleteUserGroup
	 * This method is used for deleteing user group.
	 * @param $groupId
	 */
	public function deleteUserGroup($groupId)
	{
		try {
			// This variable is used for create Group class object.
			$objGroup = new UserGroup();
			$objGroup->deleteAll(array('GroupID' => $groupId),false);
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
	}
}



// vim: ts=4
