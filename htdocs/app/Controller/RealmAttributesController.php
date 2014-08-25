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
 * @class RealmAttributesController
 *
 * @brief This class manages the attributes for realm.
 */
class RealmAttributesController extends AppController
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
	 * This method is used for showing realms attributes with pagination.
	 * @param $realmId
	 */
	public function index($realmId)
	{
		// Get user group name.
		$groupName = $this->Access->getGroupName($this->Session->read('User.ID'));
		$this->set('groupName', $groupName);
		// Check permission.
		$permission = $this->Access->checkPermission('RealmAttributesController', 'View', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		if (isset($realmId)) {
			$this->paginate = array(
				'limit' => PAGINATION_LIMIT,
				'conditions' => array('RealmAttribute.RealmID' => $realmId)
			);
			$realmAttributes = $this->paginate();
			$this->set('realmAttributes', $realmAttributes);
			$this->set('realmId', $realmId);

			// Setting the attribute operators.
			$attributeOperators = Util::getAttributeOperators();
			$this->set('attributeOperators', $attributeOperators);
		} else {
			$this->redirect('/realm_attributes/index');
		}
	}



	/**
	 * @method add
	 * This method is used to add realms attributes.
	 * @param $realmId
	 */
	public function add($realmId)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('RealmAttributesController', 'Add', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		$this->set('realmId', $realmId);
		// Setting the attribute operators.
		$attributeOperators = Util::getAttributeOperators();
		$this->set('attributeOperators', $attributeOperators);
		if ($this->request->is('post')) {
			$this->request->data['RealmAttribute']['Disabled'] = intval($this->request->data['RealmAttribute']['Disabled']);
			$this->request->data['RealmAttribute']['RealmID'] = intval($this->request->params['pass'][0]);
			$this->RealmAttribute->set($this->request->data);
			if ($this->RealmAttribute->validates()) {
				$this->RealmAttribute->save($this->request->data);
				$this->Session->setFlash(__('Realm attribute is saved succefully')."!", 'flash_success');
			} else {
				$this->Session->setFlash(__('Realm attribute is not saved succefully')."!", 'flash_failure');
			}
		}
	}



	/**
	 * @method edit
	 * This method is used to edit realms attributes.
	 * @param $id
	 */
	public function edit($id)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('RealmAttributesController', 'Edit', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		$realmAttribute = $this->RealmAttribute->findById($id);
		$this->set('realmAttribute', $realmAttribute);
		// Setting the attribute operators.
		$attributeOperators = Util::getAttributeOperators();
		$this->set('attributeOperators', $attributeOperators);
		// Checking submitted or not.
		if ($this->request->is('post')) {
			$this->request->data['RealmAttribute']['Disabled'] = intval($this->request->data['RealmAttribute']['Disabled']);
			// Setting submitted data.
			$this->RealmAttribute->set($this->request->data);
			if ($this->RealmAttribute->validates()) {
				$this->RealmAttribute->id = $id;
				$this->RealmAttribute->save($this->request->data);
				$this->Session->setFlash(__('Realm attribute is saved succefully')."!", 'flash_success');
			} else {
				$this->Session->setFlash(__('Realm attribute is not saved succefully')."!", 'flash_failure');
			}
		}
	}



	/**
	 * @method remove
	 * This method is used to delete realms attributes.
	 * @param $id
	 * @param $realmId
	 */
	public function remove($id, $realmId)
	{
		$permission = $this->Access->checkPermission('RealmAttributesController', 'Delete', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		if (isset($id)) {
			// Deleting & checking successful or not.
			if ($this->RealmAttribute->delete($id)) {
				// Redirecting to realms attribute index function.
				$this->redirect('/realm_attributes/index/'.$realmId);
				$this->Session->setFlash(__('Realm attribute is removed succefully')."!", 'flash_success');
			} else {
				$this->Session->setFlash(__('Realm attribute is not removed succefully')."!", 'flash_failure');
			}
		} else {
			$this->redirect('/realm_attributes/index'.$realmId);
		}
	}
}

// vim: ts=4
