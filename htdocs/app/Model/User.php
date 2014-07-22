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



// Import another models.
App::import('Model','UserAttribute');
App::import('Model','UserTopup');
App::import('Model','UserGroup');



/**
 * @class User
 *
 * @brief This class manages validation and method.
 */
class User extends AppModel
{

	// This variable is used for including table.
	public $useTable = 'users';


	// Validating form controller.
	public $validate = array(
		'Username' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'message' => 'Please choose a username'
			),
			'unique' => array(
				'rule' => 'isUnique',
				'message' => 'The username you have chosen has already been registered'
			)
		)
	);



	/**
	 * @method deleteUser
	 * This method is used for delete user records from different tables.
	 * @param $userId
	 */
	public function deleteUser($userId)
	{
		try {
			$objUserAttribute = new UserAttribute();
			$objUserAttribute->deleteAll(array('UserID' => $userId),false);

			$objUserGroup = new UserGroup();
			$objUserGroup->deleteAll(array('UserID' => $userId),false);

			$objUserTopup = new UserTopup();
			$objUserTopup->deleteAll(array('UserID' => $userId),false);
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
	}
}



// vim: ts=4
