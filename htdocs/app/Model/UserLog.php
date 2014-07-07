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
 * User Log Model
 *
 */

class UserLog extends AppModel
{
	//Validating form controllers.
	public $validate = array('Value' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please enter value'),'numeric' => array('rule'     => 'naturalNumber','required' => true,'message'=> 'numbers only')));

	public $useTable = 'accounting';



	//Fetch records form table.
	public function SelectRec($userId, $data)
	{
		return $userLog = $this->query("select * from topups where ValidFrom = '".$data."' and UserID = '".$userId."'");
	}



	//Fetch username.
	public function SelectAcc($userId)
	{
		return $userLog = $this->query("select Username from users where ID = '".$userId."'");
	}



}



// vim: ts=4
