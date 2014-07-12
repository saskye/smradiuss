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
 * @class WispLocation
 *
 * @brief This class manages default table and validation.
 */
class WispLocation extends AppModel
{

	/**
	 * @var $useTable
	 * This variable is used for including table.
	 */
	public $useTable = 'wisp_locations';


	// Validating form controller.
	public $validate = array(
		'Name' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'message' => 'Please enter name.'
			)
		)
	);
}



// vim: ts=4
