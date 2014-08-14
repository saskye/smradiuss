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
 * @method WebuiUser
 *
 * @brief This class manages default table and validation.
 */
class WebuiUser extends AppModel
{
	// Variable $useTable is used for load default table.
	public $useTable = 'webui_users';

	public $validate = array(
		'Username' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'message' => 'Please enter value'
			)
		),
		'Password' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'message' => 'Please enter value'
			)
		)
	);
}



// vim: ts=4
