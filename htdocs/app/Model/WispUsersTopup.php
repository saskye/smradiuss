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
 * @class WispUsersTopup
 *
 * @brief This class manages default table, validation and methods.
 */
class WispUsersTopup extends AppModel
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
		),
		'Type' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'message' => 'Please select value'
			)
		)
	);



	/**
	 * @method inser Rec
	 * This method is used for insert record in topups table.
	 * @param $userId
	 * @param $data
	 */
	public function insertRec($userId, $data)
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
				",array(
					$userId,
					$timestamp,
					$data['WispUsersTopup']['Type'],
					$data['WispUsersTopup']['Value'],
					$data['WispUsersTopup']['valid_from'],
					$data['WispUsersTopup']['valid_to']
				)
			);
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
	}



	/**
	 * @method editRec
	 * This method is used for update topups table.
	 * @param $id
	 * @param $data
	 */
	public function editRec($id, $data)
	{
		try {
			$res = $this->query("
				UPDATE
					topups
				SET
					`Type` = '".$data['WispUsersTopup']['Type']."',
					`Value` = '".$data['WispUsersTopup']['Value']."',
					`ValidFrom` = '".$data['WispUsersTopup']['valid_from']."',
					`ValidTo` = '".$data['WispUsersTopup']['valid_to']."'
				WHERE
					`ID` = ".$id
			);
		} catcvh (exception $ex) {
			throw new exception('Error in query.');
		}
	}
}



// vim: ts=4
