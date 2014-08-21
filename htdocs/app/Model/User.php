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



// Import another models.
App::import('Model','UserAttribute');
App::import('Model','UserTopup');
App::import('Model','UserGroup');
App::import('Util','Utilities');



/**
 * @class User
 *
 * @brief This class manages validation and method.
 */
class User extends AppModel
{

	/**
	 * @var $useTable
	 * This variable is used for including table.
	 */
	public $useTable = 'users';


	/**
	 * @var $validate
	 * Validating form controller.
	 */
	public $validate = array(
		'Username' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'message' => 'Please choose a username'
			),
			'unique' => array(
				'rule' => 'isUnique',
				'message' => 'The username you have chosen has already been registered'
			)
		)
	);



	/**
	 * @method save
	 * Overrides the AWITModel save method to provide updating of User-Password attributes
	 *
	 * Saves model data (based on white-list, if supplied) to the database. By
	 * default, validation occurs before save.
	 *
	 * @param array $data Data to save.
	 * @param boolean|array $validate Either a boolean, or an array.
	 * @param array $fieldList List of fields to allow to be saved
	 * @return mixed On success Model::$data if its not empty or true, false on failure
	 * @link http://book.cakephp.org/2.0/en/models/saving-your-data.html
	 *
	 */
	public function save($data = null, $validate = true, $fieldList = array()) {
		$object = parent::save($data, $validate, $fieldList);
		if (!empty($this->id)) {
			$id = $this->id;
		} else {
			$id = $object['User']['id'];
		}

		// Edit All or Create User-Password Attribute(s)
		if (isset($data['User']['Password']) && !empty($id)) {
			$objUserAttribute = new UserAttribute();

			$attrExists = $objUserAttribute->find('first',
				array(
					'conditions' => array(
						'Name' => 'User-Password',
						'UserID' => $id
					)
				)
			);

			if (!empty($attrExists)) {
				$attrData = array();
				$attrData['UserID'] = $id;
				$attrData['Name'] = "'User-Password'";
				$attrData['Operator']= "'" . Util::getAttributeOperatorIndexByValue('==') . "'";
				$attrData['Value'] = "'" . $data['User']['Password'] . "'";
				$attrData['Disabled'] = 0;

				$objUserAttribute->updateAll($attrData, array('Name' => 'User-Password', 'UserID' => $id));
			} else {
				$attrData = array();
				$attrData['UserID'] = $id;
				$attrData['Name'] = 'User-Password';
				$attrData['Operator']= '==';
				$attrData['Value'] = $data['User']['Password'];
				$attrData['Disabled'] = 0;

				$objUserAttribute->save(array('UserAttribute' => $attrData), false, array());
			}

		}

		return $object;
	}



	/**
	 * @method deleteUser
	 * This method is used for delete user records from different tables.
	 * @param $userId
	 */
	public function deleteUser($userId)
	{
		try {
			$objUserAttribute = new UserAttribute();
			$objUserAttribute->deleteAll(array('UserID' => $userId),false);

			$objUserGroup = new UserGroup();
			$objUserGroup->deleteAll(array('UserID' => $userId),false);

			$objUserTopup = new UserTopup();
			$objUserTopup->deleteAll(array('UserID' => $userId),false);
		} catch (exception $ex) {
			throw new exception('Error in query.');
		}
	}
}



// vim: ts=4
