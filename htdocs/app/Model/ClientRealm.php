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
App::import('Model','Realm');



/**
 * @class ClientRealm
 *
 * @brief This class manages default table, validation and method.
 */
class ClientRealm extends AppModel
{

	// This variable is used for including table.
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
	 * @return $realmData
	 */
	public function selectRealms()
	{
		try {
			$objRealm = new Realm();
			$realmData = $objRealm->find(
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
		return $realmData;
	}



	/**
	 * @method getRealmsById
	 * @param $realmID
	 * This method is used for get realms name.
	 * @return $realmName
	 */
	public function getRealmsById($realmID)
	{
		try {
			$objRealm = new Realm();
			$realmName = $objRealm->find(
				'first',
				array(
					'conditions' => array(
						'ID' => $realmID
					),
					'fields' => array(
						'Name'
					)
				)
			);
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
		return $realmName;
	}
}



// vim: ts=4
