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
App::import('Model','UserTopup');
App::import('Model','User');



/**
 * @method UserLog
 *
 * @brief This class manages default table, validation and methods.
 */
class UserLog extends AppModel
{

	// This variable is used for including table.
	public $useTable = 'accounting';



	// Validating form controllers.
	public $validate = array(
		'Value' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'message' => 'Please enter value'
			),
			'numeric' => array(
				'rule' => 'naturalNumber',
				'required' => true,
				'message'=> 'numbers only'
			)
		)
	);



	/**
	 * @method selectTopup
	 * This method is used for fetching records.
	 * @param $userId
	 * @param $validFrom
	 * @return $userTopupData
	 */
	public function selectTopup($userId, $validFrom)
	{
		try {
			$objUserTopup = new UserTopup();
			$userTopupData = $objUserTopup->find(
				'all',
				array(
					'conditions' => array(
						'UserID' => $userId,
						'ValidFrom' => $validFrom
					)
				)
			);
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
		return $userTopupData;
	}



	/**
	 * @method selectUser
	 * This method is used for fetch username.
	 * @param $userId
	 * @return $userData
	 */
	public function selectUser($userId)
	{
		try {
			$objUser = new User();
			$userData = $objUser->find(
				'first',
				array(
					'conditions' => array(
						'ID' => $userId
					),
					'fields' => array(
						'Username'
					)
				)
			);
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
		return $userData;
	}
}



// vim: ts=4
