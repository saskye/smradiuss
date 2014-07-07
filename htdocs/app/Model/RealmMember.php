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
 * Realm Member Model
 *
 */

class RealmMember extends AppModel
{
	public $useTable = 'clients_to_realms';



	// Fetch client name via its id.
	public function getClientNameById($clientID)
	{
		return $res = $this->query("select Name from clients where ID = ".$clientID);
	}



}



// vim: ts=4
