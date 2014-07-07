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
 * User Model
 *
 */

class User extends AppModel
{
	//Validating form controller.
	public $validate = array('Username' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please choose a username'), 'unique' => array('rule' => 'isUnique', 'message' => 'The username you have chosen has already been registered')));



	// Delete user records form different tables.
	public function deleteUserRef($userId)
	{
		$res = $this->query("delete from wisp_userdata where UserID = ".$userId);
		$res = $this->query("delete from user_attributes where UserID = '".$userId."'");
		$res = $this->query("delete from users_to_groups where UserID = '".$userId."'");
		$res = $this->query("delete from topups where UserID = '".$userId."'");
	}



}



// vim: ts=4
