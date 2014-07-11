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
 * @class UserTopup
 *
 * @brief This class manages default table, validation and methods.
 */
class UserTopup extends AppModel
{

	/**
	 * @var $useTable
	 * This variable is used for including table.
	 */
	public $useTable = 'topups';


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
	 * @method insertTopup
	 * This method is used for insert record in topups table.
	 * @param $userId
	 * @param $data
	 */
	public function insertTopup($userId, $data)
	{
		try {
			$timestamp = date("Y-m-d H:i:s");
			$res = $this->query("
				INSERT INTO topups
					(
						UserID,
						Timestamp,
						Type,
						Value,
						ValidFrom,
						ValidTo
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
			",
				array(
					$userId,
					$timestamp,
					$data['UserTopup']['Type'],
					$data['UserTopup']['Value'],
					$data['UserTopup']['valid_from'],
					$data['UserTopup']['valid_to']
				)
			);
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
	}



	/**
	 * @method editTopup
	 * This method is used for update topups table.
	 * @param $id
	 * @param $data
	 */
	public function editTopup($id, $data)
	{
		try {
			$res = $this->query("
				UPDATE
					topups
				SET
					`Type` = '".$data['UserTopup']['Type']."',
					`Value` = '".$data['UserTopup']['Value']."',
					`ValidFrom` = '".$data['UserTopup']['valid_from']."',
					`ValidTo` = '".$data['UserTopup']['valid_to']."'
				WHERE
					`ID` = ".$id
			);
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
	}
}



// vim: ts=4
