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
 * @class GroupMember
 *
 * @brief This class manages default table and method.
 */
class GroupMember extends AppModel
{

	/**
	 * @var $useTable
	 * This variable is used for including table.
	 */
	public $useTable = 'users_to_groups';


	/**
	 * @method getUserNameById
	 * This method is used for fetching username.
	 * @param $userId
	 * @return $res
	 */
	public function getUserNameById($userId)
	{
		try {
			$res = $this->query("SELECT Username FROM users WHERE ID = ".$userId);
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
		return $res;
	}
}



// vim: ts=4
