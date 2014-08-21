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



App::uses('Model', 'Model');
App::uses('AWITModelValidator', 'Model');
App::uses('CakeEventListener', 'Event');
App::uses('ClassRegistry', 'Utility');

/**
 * @class AWITModel
 * Overriding Cake Model to provide REST
 *
 * Object-relational mapper.
 *
 * DBO-backed object data model.
 * Automatically selects a database table name based on a pluralized lowercase object class name
 * (i.e. class 'User' => table 'users'; class 'Man' => table 'men')
 * The table is required to have at least 'id auto_increment' primary key.
 *
 * @package       Cake.Model
 * @link          http://book.cakephp.org/2.0/en/models.html
 */
class AWITModel extends Model implements CakeEventListener {


	/**
	 * @var $controller
	 * The current Controller loading the Model
	 */
	private $controller;



	/**
	 * @method __construct
	 * Overriding the Model Constructor to provide a parameter to specify the
	 * Controller currently loading the Model
	 *
	 * @param boolean|integer|string|array $id Set this ID for this model on startup,
	 * can also be an array of options, see above.
	 * @param string $table Name of database table to use.
	 * @param string $ds DataSource connection name.
	 */
	public function __construct($id = false, $table = null, $ds = null, $controller = null) {
		parent::__construct($id, $table, $ds, $controller);

		// Initializing the controller
		if (is_object($controller)) {
			$this->controller = $controller;
		} else {
			$this->controller = null;
		}

	}



	/**
	 * @method getController
	 * Returns the currently loaded controller from within the given model
	 */
	public function getController() {
		return $this->controller;
	}


	/**
	 * @method validator
	 * Overriding the validator method to specify the AWITModelValidator class
	 * Returns an instance of an AWIT model validator for this class
	 *
	 * @param AWITModelValidator Model validator instance.
	 *  If null a new AWITModelValidator instance will be made using current model object
	 * @return AWITModelValidator
	 */
	public function validator(AWITModelValidator $instance = null) {
		if ($instance) {
			$this->_validator = $instance;
		} elseif (!$this->_validator) {
			$this->_validator = new AWITModelValidator($this);
		}

		return $this->_validator;
	}



	/**
	 * @method save
	 * Overrides the Model save method to provide REST responses
	 *
	 * Saves model data (based on white-list, if supplied) to the database. By
	 * default, validation occurs before save.
	 *
	 * @param array $data Data to save.
	 * @param boolean|array $validate Either a boolean, or an array.
	 *   If a boolean, indicates whether or not to validate before saving.
	 *   If an array, can have following keys:
	 *
	 *   - validate: Set to true/false to enable or disable validation.
	 *   - fieldList: An array of fields you want to allow for saving.
	 *   - callbacks: Set to false to disable callbacks. Using 'before' or 'after'
	 *      will enable only those callbacks.
	 *   - `counterCache`: Boolean to control updating of counter caches (if any)
	 *
	 * @param array $fieldList List of fields to allow to be saved
	 * @return mixed On success Model::$data if its not empty or true, false on failure
	 * @link http://book.cakephp.org/2.0/en/models/saving-your-data.html
	 */
	public function save($data = null, $validate = true, $fieldList = array()) {
		$object = parent::save($data, $validate, $fieldList);

		$this->getController()->set(compact('object'));
		$this->getController()->set('_serialize', 'object');

		return $object;
	}



	/**
	 * @method delete
	 * Overrides delete to provide REST responses
	 *
	 * Removes record for given ID. If no ID is given, the current ID is used. Returns true on success.
	 *
	 * @param integer|string $id ID of record to delete
	 * @param boolean $cascade Set to true to delete records that depend on this record
	 * @return boolean True on success
	 * @link http://book.cakephp.org/2.0/en/models/deleting-data.html
	 */
	public function delete($id = null, $cascade = true) {
		$object = parent::delete($id, $cascade);

		// Setting appropriate response for failed deletes
		if ($object === false) {
			$object = array(
				'status' => 'fail',
				'message' => 'Could not be deleted'
			);
		}

		$this->getController()->set(compact('object'));
		$this->getController()->set('_serialize', 'object');

		return $object;
	}



	/**
	 * @method updateAll
	 * Overrides updateAll to provide REST responses
	 *
	 * Updates multiple model records based on a set of conditions.
	 *
	 * @param array $fields Set of fields and values, indexed by fields.
	 *    Fields are treated as SQL snippets, to insert literal values manually escape your data.
	 * @param mixed $conditions Conditions to match, true for all records
	 * @return boolean True on success, false on failure
	 * @link http://book.cakephp.org/2.0/en/models/saving-your-data.html#model-updateall-array-fields-array-conditions
	 */
	public function updateAll($fields, $conditions = true) {
		$object = parent::updateAll($fields, $conditions);

		$this->getController()->set(compact('object'));
		$this->getController()->set('_serialize', 'object');

		return $object;
	}



	/**
	 * @method create
	 * Overrides create method to provide REST responses
	 *
	 * Initializes the model for writing a new record, loading the default values
	 * for those fields that are not defined in $data, and clearing previous validation errors.
	 * Especially helpful for saving data in loops.
	 *
	 * @param boolean|array $data Optional data array to assign to the model after it is created. If null or false,
	 *   schema data defaults are not merged.
	 * @param boolean $filterKey If true, overwrites any primary key input with an empty value
	 * @return array The current Model::data; after merging $data and/or defaults from database
	 * @link http://book.cakephp.org/2.0/en/models/saving-your-data.html#model-create-array-data-array
	 */
	public function create($data = array(), $filterKey = false) {
		$object = parent::create($data, $filterKey);

		$this->getController()->set(compact('object'));
		$this->getController()->set('_serialize', 'object');

		return $object;
	}



}
