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



// Loads Util class.
App::uses('Util', 'Utility');



/**
 * @class ClientAttributesController
 *
 * @brief This class manages the client attributes.
 */
class ClientAttributesController extends AppController
{
	/**
	 * @var $components
	 * This variable is used for include other conponents.
	 */
	var $components = array('Auth', 'Acl','Access');


	/**
	 * @var $helpers
	 * This variable is used for include other helper file.
	 */
	var $helpers = array('Access');


	/**
	 * @method beforeFilter
	 * This method executes method that we need to be executed before any other action.
	 */
	function beforeFilter()
	{
		parent::beforeFilter();
	}



	/**
	 * @method index
	 * This method is used for fetching list of client attributes with pagination.
	 * @param $clientID
	 */
	public function index($clientID)
	{
		// Get user group name.
		$groupName = $this->Access->getGroupName($this->Session->read('User.ID'));
		$this->set('groupName', $groupName);
		// Check permission.
		$permission = $this->Access->checkPermission('ClientAttributesController', 'View', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		if (isset($clientID)) {
			// Fetching records with pagination
			$this->paginate = array(
				'limit' => PAGINATION_LIMIT,
				'conditions' => array('ClientAttribute.ClientID' => $clientID)
			);
			$clientAttributes = $this->paginate();
			$this->set('clientAttributes', $clientAttributes);
			$this->set('clientID', $clientID);
			// Setting the attribute operators.
			$attributeOperators = Util::getAttributeOperators();
			$this->set('attributeOperators', $attributeOperators);
		} else {
			$this->redirect('/client_attributes/index');
		}
	}



	/**
	 * @method add
	 * This method is used to add client attributes.
	 * @param $clientID
	 */
	public function add($clientID)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('ClientAttributesController', 'Add', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		$this->set('clientID', $clientID);
		// Setting the attribute operators.
		$attributeOperators = Util::getAttributeOperators();
		$this->set('attributeOperators', $attributeOperators);
		if ($this->request->is('post')) {
			$this->request->data['ClientAttribute']['Disabled'] = intval($this->request->data['ClientAttribute']['Disabled']);
			$this->request->data['ClientAttribute']['ClientID'] = intval($this->request->params['pass'][0]);
			$this->ClientAttribute->set($this->request->data);
			// Validating user inputs.
			if ($this->ClientAttribute->validates()) {
				//Saving data to table.
				$this->ClientAttribute->save($this->request->data);
				$this->Session->setFlash(__('Client attribute is saved succefully')."!", 'flash_success');
			} else {
				$this->Session->setFlash(__('Client attribute is not saved succefully')."!", 'flash_failure');
			}
		}
	}



	/**
	 * @method edit
	 * This method is used to edit client attributes.
	 * @param $id
	 * @param $clientID
	 */
	public function edit($id, $clientID)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('ClientAttributesController', 'Edit', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		$clientAttribute = $this->ClientAttribute->findById($id);
		$this->set('clientAttribute', $clientAttribute);
		// Setting the attribute operators.
		$attributeOperators = Util::getAttributeOperators();
		$this->set('attributeOperators', $attributeOperators);
		if ($this->request->is('post')) {
			$this->request->data['ClientAttribute']['Disabled'] = intval($this->request->data['ClientAttribute']['Disabled']);
			$this->ClientAttribute->set($this->request->data);
			if ($this->ClientAttribute->validates()) {
				$this->ClientAttribute->id = $id;
				$this->ClientAttribute->save($this->request->data);
				$this->Session->setFlash(__('Client attribute is saved succefully')."!", 'flash_success');
			} else {
				$this->Session->setFlash(__('Client attribute is not saved succefully')."!", 'flash_failure');
			}
		}
	}



	/**
	 * @method remove
	 * This method is used to delete client attributes.
	 * @param $id
	 * @param $clientID
	 */
	public function remove($id, $clientID)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('ClientAttributesController', 'Delete', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		if (isset($id)) {
			// Deleting then redirecting to index
			if ($this->ClientAttribute->delete($id)) {
				$this->redirect('/client_attributes/index/'.$clientID);
				$this->Session->setFlash(__('Client attribute is removed succefully')."!", 'flash_success');
			} else {
				$this->Session->setFlash(__('Client attribute is not removed succefully')."!", 'flash_failure');
			}
		} else {
			$this->redirect('/client_attributes/index'.$clientID);
		}
	}
}

// vim: ts=4
