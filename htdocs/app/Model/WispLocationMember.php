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



// Import User model.
App::import('Model','User');



/**
 * @class WispLocationMember
 *
 * @brief This class manages default table and methods.
 */
class WispLocationMember extends AppModel
{

	// This variable is used for including table.
	public $useTable = 'wisp_userdata';


	/**
	 * @method selectUsername
	 * This method is used for fetching username.
	 * @param $userid
	 * @return $userData
	 */
	public function selectUsername($userid)
	{
		try {
			$objUser = new User();
			$userData = $objUser->find(
				'first',
				array(
					'conditions' => array(
						'ID' => $userid
					)
				)
			);
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
		return $userData;
	}



}



// vim: ts=4
