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
 * @method UserLog
 *
 * @brief This class manages default table, validation and methods.
 */
class UserLog extends AppModel
{

	/**
	 * @var $useTable
	 * This variable is used for including table.
	 */
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
	 * @param $data
	 * @return $userLog
	 */
	public function selectTopup($userId, $data)
	{
		try {
			$userLog = $this->query("
				SELECT
					`ID`,
					`UserID`,
					`Timestamp`,
					`Type`,
					`Value`,
					`ValidFrom`,
					`ValidTo`,
					`Depleted`,
					`SMAdminDepletedOn`
				FROM
					topups
				WHERE
					ValidFrom = '".$data."'
					AND UserID = '".$userId."'
			");
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
		return $userLog;
	}



	/**
	 * @method selectUser
	 * This method is used for fetch username.
	 * @param $userId
	 * @return $userLog
	 */
	public function selectUser($userId)
	{
		try {
			$userLog = $this->query("SELECT Username FROM users WHERE ID = ".$userId);
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
		return $userLog;
	}
}



// vim: ts=4
