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
 * @class RealmMember
 *
 * @brief This class manages default table and validation.
 */
class RealmMember extends AppModel
{

	/**
	 * @var $useTable
	 * This variable is used for including table.
	 */
	public $useTable = 'clients_to_realms';


	/**
	 * @method getClientNameById
	 * This method is used for fetching client name.
	 * @param $clientID
	 * @return $res
	 */
	public function getClientNameById($clientID)
	{
		try {
			$res = $this->query("SELECT Name FROM clients WHERE ID = ?", array($clientID));
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
		return $res;
	}
}



// vim: ts=4
