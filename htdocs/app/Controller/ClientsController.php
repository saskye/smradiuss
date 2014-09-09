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
 * @class ClientsController
 *
 * @brief This class manages clients.
 */
class ClientsController extends AppController
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
	 * This method is used for fetching list of clients with pagination.
	 */
	public function index()
	{
		// Get user group name.
		$groupName = $this->Access->getGroupName($this->Session->read('User.ID'));
		$this->set('groupName', $groupName);
		// Check permission.
		$permission = $this->Access->checkPermission('ClientsController', 'View', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		$this->Client->recursive = -1;
		$this->paginate = array('limit' => PAGINATION_LIMIT);
		$client = $this->paginate();
		$this->set('client', $client);
	}



	/**
	 * @method add
	 * This method is used to add clients.
	 */
	public function add()
	{
		// Check permission.
		$permission = $this->Access->checkPermission('ClientsController', 'Add', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		if ($this->request->is('post')) {
			$this->Client->set($this->request->data);
			if ($this->Client->validates()) {
				$this->Client->save($this->request->data);
				$this->Session->setFlash(__('Client is saved succefully')."!", 'flash_success');
			} else {
				$this->Session->setFlash(__('Client is not saved succefully')."!", 'flash_failure');
			}
		}
	}



	/**
	 * @method edit
	 * This method is used to edit clients.
	 * @param $id
	 */
	public function edit($id)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('ClientsController', 'Edit', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		// Assigning client data to var.
		$client = $this->Client->findById($id);
		$this->set('client', $client);
		if ($this->request->is('post')) {
			$this->Client->set($this->request->data);
			if ($this->Client->validates()) {
				$this->Client->id = $id;
				$this->Client->save($this->request->data);
				$this->Session->setFlash(__('Client is edited succefully')."!", 'flash_success');
			} else {
				$this->Session->setFlash(__('Client is not edited succefully')."!", 'flash_failure');
			}
		}
	}



	/**
	 * @method remove
	 * This method is used to delete clients.
	 * @param $id
	 */
	public function remove($id)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('ClientsController', 'Delete', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		if ($this->Client->delete($id)) {
			$this->redirect('/clients/index');
			$this->Session->setFlash(__('Client is removed succefully')."!", 'flash_success');
		} else {
			$this->Session->setFlash(__('Client is not removed succefully')."!", 'flash_failure');
		}
	}
}

// vim: ts=4
