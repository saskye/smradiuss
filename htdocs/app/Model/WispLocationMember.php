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
 * Wisp Location Member Model
 *
 */

class WispLocationMember extends AppModel
{
	public $useTable = 'wisp_userdata';



	//Fetching username.
	public function selectUsername($userName)
	{
		return $res = $this->query("select Username from users where ID = '".$userName."'");
	}



	//Deleting record.
	public function deleteMembers($id)
	{
		$res = $this->query("update wisp_userdata set LocationID = '0' where ID = '".$id."'");
	}



}



// vim: ts=4
