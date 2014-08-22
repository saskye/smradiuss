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
 * @class RealmsController
 *
 * @brief This class manages the realms.
 */
class RealmsController extends AppController
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
	 * This method is used to show realms list with pagination.
	 */
	public function index()
	{
		// Get user group name.
		$groupName = $this->Access->getGroupName($this->Session->read('User.ID'));
		$this->set('groupName', $groupName);
		// Check permission.
		$permission = $this->Access->checkPermission('RealmsController', 'View', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		$this->Realm->recursive = -1;
		$this->paginate = array('limit' => PAGINATION_LIMIT);
		$realm = $this->paginate();
		$this->set('realm', $realm);
	}



	/**
	 * @method add
	 * This method is used to add realms.
	 */
	public function add()
	{
		// Check permission.
		$permission = $this->Access->checkPermission('RealmsController', 'Add', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		if ($this->request->is('post')) {
			$this->Realm->set($this->request->data);
			// Validating enterd data.
			if ($this->Realm->validates()) {
				$this->Realm->save($this->request->data);
				$this->Session->setFlash(__('Realm is saved succefully'."!"), 'flash_success');
			} else {
				$this->Session->setFlash(__('Realm is not saved succefully'."!"), 'flash_failure');
			}
		}
	}



	/**
	 * @method edit
	 * This method is used to edit realms.
	 * @param $id
	 */
	public function edit($id)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('RealmsController', 'Edit', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		// Fetch record and set to variable.
		$realm = $this->Realm->findById($id);
		$this->set('realm', $realm);
		// Checking submission.
		if ($this->request->is('post')) {
			// Setting submitted data.
			$this->Realm->set($this->request->data);
			// Validating submitted data.
			if ($this->Realm->validates()) {
				$this->Realm->id = $id;
				// Saving
				$this->Realm->save($this->request->data);
				$this->Session->setFlash(__('Realm is edited succefully'."!"), 'flash_success');
			} else {
				$this->Session->setFlash(__('Realm is not edited succefully'."!"), 'flash_failure');
			}
		}
	}



	/**
	 * @method remove
	 * This method is used to delete realms.
	 * @param $id
	 */
	public function remove($id)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('RealmsController', 'Delete', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		// Deleting & check done or not.
		if ($this->Realm->delete($id)) {
			// Redirecting to index.
			$this->redirect('/realms/index');
			$this->Session->setFlash(__('Realm is removed succefully'."!"), 'flash_success');
		} else {
			$this->Session->setFlash(__('Realm is not removed succefully'."!"), 'flash_failure');
		}
	}
}

// vim: ts=4
