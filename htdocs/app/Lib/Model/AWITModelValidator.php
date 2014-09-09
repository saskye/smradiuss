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



App::uses('ModelValidator', 'Model');
App::uses('Controller', 'Controller');

/**
 * @class AWITModelValidator
 * Overrides ModelValidator to provide REST responses
 *
 * AWITModelValidator object encapsulates all methods related to data validations for a model
 * It also provides an API to dynamically change validation rules for each model field.
 *
 * Implements ArrayAccess to easily modify rules as usually done with `Model::$validate`
 * definition array
 *
 * @package       Cake.Model
 * @link          http://book.cakephp.org/2.0/en/data-validation.html
 */
class AWITModelValidator extends ModelValidator implements ArrayAccess, IteratorAggregate, Countable {



	/**
	 * @method validates
	 * Overrides the validates method to provide REST responses
	 *
	 * @param array $options An optional array of custom options to be made available in the beforeValidate callback
	 * @return boolean True if there are no errors
	 */
	public function validates($options = array()) {
		$errors = $this->errors($options);
		if (empty($errors) && $errors !== false) {
			$errors = $this->_validateWithModels($options);
		}

		// Handle REST responses for validation
		if (!empty($errors) && $errors !== true) {
			$data = array (
				'status' => 'fail',
				'message' => $errors
			);

			$controller = $this->getController();
			if (is_object($controller) && $controller instanceof Controller) {
				$controller->set(compact('data'));
				$controller->set('_serialize', 'data');
			}

		}

		if (is_array($errors)) {
			return count($errors) === 0;
		}
		return $errors;
	}



	/**
	 * @method getController
	 * Returns the currently loaded controller from the Model
	 */
	public function getController() {
		if (is_object($this->_model->getController()) && $this->_model->getController() instanceof Controller) {
			return $this->_model->getController();
		}

		return false;
	}

}
