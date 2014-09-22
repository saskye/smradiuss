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
 * @class GroupsController
 *
 * @brief This class manages the groups.
 */
class GroupsController extends AppController
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
	 * This method is used for Showing group list with pagination.
	 */
	public function index()
	{
		// Get user group name.
		$groupName = $this->Access->getGroupName($this->Session->read('User.ID'));
		$this->set('groupName', $groupName);
		// Check permission.
		$permission = $this->Access->checkPermission('GroupsController', 'View', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		$this->Group->recursive = -1;
		$this->paginate = array('limit' => PAGINATION_LIMIT );
		$groups = $this->paginate();
		$this->set('groups', $groups);
	}



	/**
	 * @method add
	 * This method is used to add groups.
	 */
	public function add()
	{
		// Check permission.
		$permission = $this->Access->checkPermission('GroupsController', 'Add', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		if ($this->request->is('post')) {
			$this->Group->set($this->request->data);

			// Validating entered data.
			if ($this->Group->validates()) {
				// Saving data.
				$this->Group->save($this->request->data);
				$this->Session->setFlash(__('Group is saved successfully')."!", 'flash_success');
			} else {
				$this->Session->setFlash(__('Group is not saved succefully')."!", 'flash_failure');
			}
		}
	}



	/**
	 * @method edit
	 * This method is used to edit groups.
	 * @param $id
	 */
	public function edit($id)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('GroupsController', 'Edit', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		$group = $this->Group->findById($id);
		$this->set('group', $group);

		// Checking submit button is clicked or not
		if ($this->request->is('post')) {
			$this->Group->set($this->request->data);
			$this->Group->id = $id;
			// Validating submitted data.
			if ($this->Group->validates()) {
				$this->Group->save($this->request->data);
				$this->Session->setFlash(__('Group is edited successfully')."!", 'flash_success');
				// For reload page to reflect change in data
				$group = $this->Group->findById($id);
				$this->set('group', $group);
			} else {
				$this->Session->setFlash(__('Group is not edited successfully')."!", 'flash_failure');
			}
		}
	}



	/**
	 * @method remove
	 * This method is used to delete groups.
	 * @param $id
	 */
	public function remove($id)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('GroupsController', 'Delete', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		// Deleting
		if ($this->Group->delete($id)) {
			$this->Group->deleteUserGroup($id);
			// Redirected to index function.
			$this->redirect('/groups/index');
			$this->Session->setFlash(__('Group was removed successfully')."!", 'flash_success');
		} else {
			$this->Session->setFlash(__('Group is not removed successfully')."!", 'flash_failure');
		}
	}
}



// vim: ts=4
