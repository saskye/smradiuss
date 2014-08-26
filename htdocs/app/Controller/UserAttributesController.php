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



App::uses('Util', 'Utility');



/**
 * @class UserAttributesController
 *
 * @brief This class manages the attributes for users.
 */
class UserAttributesController extends AppController
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
	 * This method is used to show list of user attributes with pagination.
	 * @param $userId
	 */
	public function index($userId)
	{
		// Get user group name.
		$groupName = $this->Access->getGroupName($this->Session->read('User.ID'));
		$this->set('groupName', $groupName);
		// Check permission.
		$permission = $this->Access->checkPermission('UserAttributesController', 'View', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		if (isset($userId)) {
			$this->UserAttribute->recursive = 0;
			$this->paginate = array(
				'limit' => PAGINATION_LIMIT,
				'conditions' => array('UserAttribute.UserID' => $userId)
			);
			$userAttributes = $this->paginate();

			// Setting the attribute operators
			$attributeOperators = Util::getAttributeOperators();

			$this->set('attributeOperators', $attributeOperators);
			$this->set('userAttributes', $userAttributes);
			$this->set('userId', $userId);
		} else {
			$this->redirect('/users/index');
		}
	}



	/**
	 * @method add
	 * This method is used to add users attributes.
	 * @param $userId
	 */
	public function add($userId)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('UserAttributesController', 'Add', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}

		// Setting the attribute operators
		$attributeOperators = Util::getAttributeOperators();

		$this->set('userId', $userId);
		if ($this->request->is('post')) {
			$this->request->data['UserAttribute']['Disabled'] = intval($this->request->data['UserAttribute']['Disabled']);
			$this->request->data['UserAttribute']['UserID'] = intval($this->request->params['pass'][0]);
			$this->UserAttribute->set($this->request->data);
			// Validating
			if ($this->UserAttribute->validates()) {
				// Saving
				$this->UserAttribute->save($this->request->data);
				$this->Session->setFlash(__('User attribute is saved succefully')."!", 'flash_success');
			} else {
				$this->Session->setFlash(__('User attribute is not saved succefully')."!", 'flash_failure');
			}
		}
	}



	/**
	 * @method edit
	 * This method is used to edit users attributes.
	 * @param $id
	 * @param $userId
	 */
	public function edit($id, $userId)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('UserAttributesController', 'Edit', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		$userAttribute = $this->UserAttribute->findById($id);
		$this->set('userAttribute', $userAttribute);

		// Setting the attribute operators
		$attributeOperators = Util::getAttributeOperators();

		if ($this->request->is('post')) {
			$this->request->data['UserAttribute']['Disabled'] = intval($this->request->data['UserAttribute']['Disabled']);
			$this->UserAttribute->set($this->request->data);
			if ($this->UserAttribute->validates()) {
				$this->UserAttribute->id = $id;
				$this->UserAttribute->save($this->request->data);
				$this->Session->setFlash(__('Attribute is saved succefully')."!", 'flash_success');
			} else {
				$this->Session->setFlash(__('Attribute is not saved succefully')."!", 'flash_failure');
			}
		}
	}



	/**
	 * @method remove
	 * This method is used to delete users attributes.
	 * @param $id
	 * @param $userId
	 */
	public function remove($id, $userId)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('UserAttributesController', 'Delete', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		if (isset($id)) {
			// Deleting and checking.
			if ($this->UserAttribute->delete($id)) {
				// Redirecting to index.
				$this->redirect('/user_attributes/index/'.$userId);
				$this->Session->setFlash(__('User is removed succefully')."!", 'flash_success');
			} else {
				$this->Session->setFlash(__('User is not removed succefully')."!", 'flash_failure');
			}
		} else {
			$this->redirect('/user_attributes/index'.$userId);
		}
	}
}

// vim: ts=4
