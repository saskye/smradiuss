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
 * @class User
 *
 * @brief This class manages validation and method.
 */
class User extends AppModel
{

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
			$res = $this->query("DELETE FROM wisp_userdata WHERE UserID = ".$userId);
			$res = $this->query("DELETE FROM User_attributes WHERE UserID = ".$userId);
			$res = $this->query("DELETE FROM users_to_groups WHERE UserID = ".$userId);
			$res = $this->query("DELETE FROM topups WHERE UserID = ".$userId);
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
	}
}



// vim: ts=4
