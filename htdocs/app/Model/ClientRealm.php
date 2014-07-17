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
 * @class ClientRealm
 *
 * @brief This class manages default table, validation and method.
 */
class ClientRealm extends AppModel
{

	/**
	 * @var $useTable
	 * This variable is used for including table.
	 */
	public $useTable = 'clients_to_realms';


	// Validating form controller.
	public $validate = array(
		'Type' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'message' => 'Please select value'
			)
		)
	);



	/**
	 * @method selectRealms
	 * This method is used for fetch realms data.
	 * @return $res
	 */
	public function selectRealms()
	{
		try {
			$res = $this->query("SELECT ID, Name FROM realms");
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
		return $res;
	}



	/**
	 * @method insertRec
	 * @param $clientID
	 * @param $data
	 * This method is used for insert record in table.
	 */
	public function insertRec($clientID, $data)
	{
		try {
			$res = $this->query("
					INSERT INTO clients_to_realms
						(
							ClientID,
							RealmID
						)
					VALUES
						(
							?,
							?
						)
				",array(
					$clientID,
					$data['ClientRealm']['Type']
				)
			);
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
	}



	/**
	 * @method getRealmsById
	 * @param $realmID
	 * This method is used for get realms name.
	 * @return $res
	 */
	public function getRealmsById($realmID)
	{
		try {
			$res = $this->query("SELECT Name FROM realms WHERE ID = ".$realmID);
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
		return $res;
	}
}



// vim: ts=4
