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



// Import other model.
App::import('Model','Group');



/**
 * @class UserPermission
 *
 * @brief This class manages default table and method.
 */
class UserPermission extends AppModel
{
	// Variable is used for include default table.
	public $useTable = 'users_to_groups';


	/**
	 * @method selectGroup
	 * This method is used for select all groups.
	 * @return $groups
	 */
	public function selectGroup()
	{
		try {
			$objGroup = new Group();
			$groups = $objGroup->find(
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
		return $groups;
	}
}


// vim: ts=4
