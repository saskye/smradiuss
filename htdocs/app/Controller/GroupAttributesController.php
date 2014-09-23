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
 * @class GroupAttributesController
 *
 * @brief This class manages the group attributes.
 */
class GroupAttributesController extends AppController
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
	 * This method is used to load list of group attributes with pagination.
	 * @param  $groupId
	 */
	public function index($groupId)
	{
		// Get user group name.
		$groupName = $this->Access->getGroupName($this->Session->read('User.ID'));
		$this->set('groupName', $groupName);
		// Check permission.
		$permission = $this->Access->checkPermission('GroupAttributesController', 'View', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		if (isset($groupId)) {
			// Fetching data with pagination.
			$this->paginate = array(
				'limit' => PAGINATION_LIMIT,
				'conditions' => array('GroupAttribute.GroupID' => $groupId)
			);
			$groupAttributes = $this->paginate();
			$this->set('groupAttributes', $groupAttributes);
			$this->set('groupId', $groupId);
		} else {
			$this->redirect('/users/index');
		}
	}



	/**
	 * @method add
	 * This method is used to add group attributes.
	 * @param $groupId
	 */
	public function add($groupId)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('GroupAttributesController', 'Add', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		$this->set('groupId', $groupId);
		$operators = Util::getAttributeOperators();
		$this->set('operators', $operators);
		if ($this->request->is('post')) {
			$this->request->data['GroupAttribute']['Disabled'] = intval($this->request->data['GroupAttribute']['Disabled']);
			$this->request->data['GroupAttribute']['GroupID'] = intval($this->request->params['pass'][0]);
			$this->GroupAttribute->set($this->request->data);

			// Validating entered data.
			if ($this->GroupAttribute->validates()) {
				// Saving data to table.
				$this->GroupAttribute->save($this->request->data);
				$this->Session->setFlash(__('Group attribute is saved successfully')."!", 'flash_success');
			} else {
				$this->Session->setFlash(__('Group attribute is not saved successfully')."!", 'flash_failure');
			}
		}
	}



	/**
	 * @method edit
	 * This method is used to edit group attributes.
	 * @param $id
	 * @param $groupId
	 */
	public function edit($id, $groupId)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('GroupAttributesController', 'Edit', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		// Assigning group attribues values find by id to var.
		$groupAttribute = $this->GroupAttribute->findById($id);
		$this->set('groupAttribute', $groupAttribute);
		$operators = Util::getAttributeOperators();
		$this->set('operators', $operators);
		if ($this->request->is('post')) {
			$this->request->data['GroupAttribute']['Disabled'] = intval($this->request->data['GroupAttribute']['Disabled']);
			$this->GroupAttribute->set($this->request->data);
			if ($this->GroupAttribute->validates()) {
				$this->GroupAttribute->id = $id;
				//Saving data to the table.
				$this->GroupAttribute->save($this->request->data);
				$this->Session->setFlash(__('Attribute was edited successfully')."!", 'flash_success');
			} else {
				$this->Session->setFlash(__('Attribute was not saved successfully')."!", 'flash_failure');
			}
		}
	}



	/**
	 * @method remove
	 * This method is used to delete group attributes.
	 * @param $id
	 * @param $groupId
	 */
	public function remove($id, $groupId)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('GroupAttributesController', 'Delete', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		if (isset($id)) {
			// Deleting then redirecting to index function.
			if ($this->GroupAttribute->delete($id)) {
				$this->redirect('/group_attributes/index/'.$groupId);
				$this->Session->setFlash(__('Attribute was removed successfully')."!", 'flash_success');
			} else {
				$this->Session->setFlash(__('Attribute was not removed successfully')."!", 'flash_failure');
			}
		} else {
			$this->redirect('/group_attributes/index'.$userId);
		}
	}
}



// vim: ts=4
