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
 * User Group Model
 *
 */

class UserGroup extends AppModel
{
	public $useTable = 'users_to_groups';

	//Validating form controllers.
	public $validate = array('Type' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please enter value')));



	//Fetching  all groups for select box controller.
	public function selectGroup()
	{
		return $res = $this->query("select ID, Name from groups");
	}



	//Fetching group name via its id.
	public function getGroupById($groupId)
	{
		return $res = $this->query("select ID,Name from groups where ID = ".$groupId);
	}



	// Saving user groups.
	public function insertRec($userId, $data)
	{
		$res = $this->query("INSERT INTO users_to_groups (UserID,GroupID) VALUES ('".$userId."','".$data['UserGroup']['Type']."')");
	}



}



// vim: ts=4
