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
 * @class UserPermission
 *
 * @brief This class manages default table and method.
 */
class UserPermission extends AppModel
{
	// Variable is used for including table.
	public $useTable = 'aros_acos';


	//Validating form controller.
	public $validate = array(
		'aro_id' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'message' => 'Please select type.'
			)
		),
		'aco_id' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'message' => 'Please select controller.'
			)
		)
	);
}



// vim: ts=4
