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
 * @class WebuiUsersController
 *
 * @brief This class manages webuiUsers.
 */
class WebuiUsersController extends AppController
{
	/**
	 * @var $components
	 * This variable is used for include other conponents.
	 */
	var $components = array('Auth', 'Acl', 'Access');


	/**
	 * @method beforeFilter
	 * This method executes method that we need to be executed before any other action.
	 */
	function beforeFilter()
	{
		parent::beforeFilter();
		$this->Auth->userModel = 'WebuiUser';
		$this->Auth->allow('login');
	}



	/**
	 * @method index
	 * This method is used for showing webui user list.
	 */
	function index()
	{
		// Get user group name.
		$groupName = $this->Access->getGroupName($this->Session->read('User.ID'));
		$this->set('groupName', $groupName);
		// Check permission.
		$permission = $this->Access->checkPermission('WebuiUsersController', 'View', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		$this->WebuiUser->recursive = -1;
		$this->paginate = array('limit' => PAGINATION_LIMIT);
		$webuiUsers = $this->paginate();
		$this->set('webuiUsers', $webuiUsers);
		$types = $this->Acl->Aro->find('list',array('fields' => array('id','alias')));
		$this->set('types', $types);
	}



	/**
	 * @method add
	 * This method is used for add webui user.
	 */
	function add()
	{
		// Check permission.
		$permission = $this->Access->checkPermission('WebuiUsersController', 'Add', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		// Get all types.
		$types = $this->Acl->Aro->find('list',array('fields' => array('id','alias')));
		$this->set('types', $types);
		// run only when submit button clicked.
		if ($this->request->is('post')) {
			$requestData = $this->WebuiUser->set($this->request->data);
			if ($this->WebuiUser->validates()) {
				$password = Security::hash($requestData['WebuiUser']['Password'], 'sha1', true);
				$requestData['WebuiUser']['Password'] = $password;
				$this->WebuiUser->save($requestData);
				$this->Session->setFlash(__('Webui user is saved successfully')."!", 'flash_success');
			} else {
				$this->Session->setFlash(__('Webui user is not saved successfully')."!", 'flash_failure');
			}
		}
	}



	/**
	 * @method edit
	 * This method is used for edit webui user.
	 * @param $id
	 */
	function edit($id)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('WebuiUsersController', 'Edit', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		// Fetch record via id.
		$webuiUser = $this->WebuiUser->findById($id);
		$this->set('webuiUser', $webuiUser);
		// Fetch all types.
		$types = $this->Acl->Aro->find('list',array('fields' => array('id','alias')));
		$this->set('types', $types);
		if ($this->request->is('post')) {
			$requestData = $this->WebuiUser->set($this->request->data);
			$oldPassword = Security::hash($requestData['WebuiUser']['OldPassword'], 'sha1', true);
			$password = $webuiUser['WebuiUser']['Password'];
			if (!empty($requestData['WebuiUser']['OldPassword'])) {
				if (empty($requestData['WebuiUser']['NewPassword'])) {
					$this->Session->setFlash(__('New password required')."!", 'flash_failure');
					return false;
				}
			}
			if (!empty($requestData['WebuiUser']['NewPassword'])) {
				if (empty($requestData['WebuiUser']['OldPassword'])) {
					$this->Session->setFlash(__('Old password required')."!", 'flash_failure');
					return false;
				}
			}
			if (!empty($requestData['WebuiUser']['OldPassword']) && !empty($requestData['WebuiUser']['NewPassword'])) {
				if ($oldPassword == $password) {
					$newPassword = Security::hash($requestData['WebuiUser']['NewPassword'], 'sha1', true);
					$requestData['WebuiUser']['Password'] = $newPassword;
				} else {
					$this->Session->setFlash(__('Old password does not match')."!", 'flash_failure');
					return false;
				}
			} else {
				$requestData['WebuiUser']['Password'] = $password;
			}
			$this->WebuiUser->id = $id;
			if ($this->WebuiUser->validates()) {
				$this->WebuiUser->save($requestData);
				$this->Session->setFlash(__('Webui user was edited successfully')."!", 'flash_success');
			} else {
				$this->Session->setFlash(__('Webui user was not edited successfully')."!", 'flash_failure');
			}
		}
	}



	/**
	 * @method remove
	 * This method is used to delete client realms.
	 * @param $id
	 * @param $clientID
	 */
	public function remove($id)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('WebuiUsersController', 'Delete', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		if (isset($id)) {
			// Deleting then redirected to index function.
			if ($this->WebuiUser->delete($id)) {
				$this->redirect('/webui_users/index/');
				$this->Session->setFlash(__('Webui User was removed successfully')."!", 'flash_success');
			} else {
				$this->Session->setFlash(__('Webui User was not removed successfully')."!", 'flash_failure');
			}
		} else {
			$this->redirect('/webui_users/index');
		}
	}



	/**
	 * @method login
	 * This method is used for check user authentication.
	 */
	public function login()
	{
		if ($this->request->is('post')) {
			$requestData = $this->WebuiUser->set($this->request->data);
			if ($this->Auth->login($requestData)) {
				$password = Security::hash($requestData['WebuiUser']['Password'], 'sha1', true);
				$selectData = $this->WebuiUser->find(
					'all',
					array(
						'conditions' => array(
							'Username' => $requestData['WebuiUser']['Username'],
							'Password' => $password
						)
					)
				);
				if (!empty($selectData)) {
					$this->Session->write('User.ID', $selectData[0]['WebuiUser']['ID']);
					return $this->redirect($this->Auth->redirect('/users/index'));
				} else {
					// Handling REST response here
					if ($this->request->accepts('application/json')) {
						throw new UnauthorizedException("Login Failed");
					}

					$this->Session->setFlash(__('Invalid username or password, try again'), 'flash_failure');
				}
			}
		}
	}



	/**
	 * @method logout
	 * This method is used for clear user authentication.
	 */
	public function logout()
	{
		$this->Auth->logout();
		$this->Session->delete('User.ID');
		$this->redirect('/webui_users/login');
	}
}



// vim: ts=4
