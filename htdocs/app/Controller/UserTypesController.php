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
 * @class UserTypesController
 *
 * @brief This class manages user type.
 */
class UserTypesController extends AppController
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
	 * This method is used for fetching list of user type with pagination.
	 */
	public function index()
	{
		// Get user group name.
		$groupName = $this->Access->getGroupName($this->Session->read('User.ID'));
		$this->set('groupName', $groupName);
		// Check permission.
		$permission = $this->Access->checkPermission('UserTypesController', 'View', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		$this->UserType->recursive = -1;
		$this->paginate = array('limit' => PAGINATION_LIMIT);
		$userTypes = $this->paginate();
		$this->set('userTypes', $userTypes);
	}



	/**
	 * @method add
	 * This method is used to add user type.
	 */
	public function add()
	{
		// Check permission.
		$permission = $this->Access->checkPermission('UserTypesController', 'Add', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		if ($this->request->is('post')) {
			$this->UserType->set($this->request->data);
			if ($this->UserType->validates()) {
				$this->UserType->save($this->request->data);
				$this->Session->setFlash(__('User type is saved successfully')."!", 'flash_success');
			} else {
				$this->Session->setFlash(__('User type is not saved successfully')."!", 'flash_failure');
			}
		}
	}



	/**
	 * @method edit
	 * This method is used to edit user type.
	 * @param $id
	 */
	public function edit($id)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('UserTypesController', 'Edit', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		// Assigning client data to var.
		$userTypes = $this->UserType->findById($id);
		$this->set('userTypes', $userTypes);
		if ($this->request->is('post')) {
			$this->UserType->id = $id;
			$this->UserType->set($this->request->data);
			if ($this->UserType->validates()) {
				$this->UserType->save($this->request->data);
				$this->Session->setFlash(__('User Type is edited successfully')."!", 'flash_success');
			} else {
				$this->Session->setFlash(__('User Type is not edited successfully')."!", 'flash_failure');
			}
		}
	}
}



// vim: ts=4
