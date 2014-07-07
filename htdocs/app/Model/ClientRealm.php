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
 * Client Realm Model
 *
 */

class ClientRealm extends AppModel
{
	public $useTable = 'clients_to_realms';



	//Validating form controller.
	public $validate = array(
		'Type' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'message' => 'Please select value'
			)
		)
	);



	// Fetch realms for select box controler.
	public function selectRealms()
	{
		return $res = $this->query("SELECT ID, Name FROM realms");
	}



	// Insert record in table.
	public function insertRec($clientID, $data)
	{
		$res = $this->query("
			INSERT INTO clients_to_realms
				(ClientID,RealmID)
			VALUES
				('".$clientID."','".$data['ClientRealm']['Type']."')
		");
	}



	// Get realms name via realms id.
	public function getRealmsById($realmID)
	{
		return $res = $this->query("select Name from realms where ID = ".$realmID);
	}
}



// vim: ts=4
