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
 * @class UserPermissionsController
 *
 * @brief This class manages user permissions.
 */
class UserPermissionsController extends AppController
{
	// Variable $components is used for load other components.
	var $components = array('Auth', 'Acl','Access');


	/**
	 * @methos beroreFilter
	 * @brief This method is used for executes functions that we need to be executed before any other action.
	 */
	function beforeFilter()
	{
		$this->Auth->userModel = 'WebuiUser';
		$this->Auth->allow('*');
	}



	/**
	 * @method index
	 * This method shows user permission list.
	 */
	public function index()
	{
		// Get user group name.
		$groupName = $this->Access->getGroupName($this->Session->read('User.ID'));
		$this->set('groupName', $groupName);
		// Check permission.
		$permission = $this->Access->checkPermission('UserPermissionsController', 'View', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		// Fetching all data.
		$this->paginate = array('limit' => PAGINATION_LIMIT);
		$permissionList = $this->paginate($this->Acl->Aro->Permission);
		$this->set('permissionList', $permissionList);
	}



	/**
	 * @method add
	 * This method is used for add user permission.
	 */
	public function add()
	{
		// Check permission.
		$permission = $this->Access->checkPermission('UserPermissionsController', 'Add', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		$allTypes = '';
		// Fetching all types.
		$aroTypes = $this->Acl->Aro->find(
			'all',
			array(
				'fields' => array(
					'alias'
				)
			)
		);
		foreach ($aroTypes as $types) {
			$allTypes[$types['Aro']['alias']] = $types['Aro']['alias'];
		}
		$this->set('allTypes', $allTypes);
		// Fetching all controllers.
		$controllers = $this->Acl->Aco->find(
			'list',
			array(
				'fields' => array(
					'id',
					'model'
				),
				'conditions' => array(
					'parent_id IS Null'
				)
			)
		);
		$this->set('controllers', $controllers);
		if ($this->request->is('post')) {
			$requestData = $this->UserPermission->set($this->request->data);
			$getActions = array_slice($requestData, 1);
			if ($this->UserPermission->validates()) {
				// Fetching controlelr name by id.
				$controllerName = $this->Acl->Aco->find(
					'first',
					array(
						'fields' => array(
						'alias'
						),
						'conditions' => array(
							'parent_id IS Null',
							'id' => $requestData['UserPermission']['aco_id']
						)
					)
				);
				foreach ($requestData['permission'] as $actionId) {
					if ($actionId != 0) {
						// Fetching controller's alias name by controller's action id.
						$controllerAlias = $this->Acl->Aco->find(
							'first',
							array(
								'fields' => 'alias',
								'conditions' => array(
									'id' => $actionId
								)
							)
						);
						// Save permission.
						$this->Acl->allow(
							$requestData['UserPermission']['aro_id'],
							$controllerAlias['Aco']['alias']
						);
					}
				}
				$this->Session->setFlash(__('Permission add successfully')."!", 'flash_success');
			} else {
				$this->Session->setFlash(__('Permission not added')."!", 'flash_failure');
			}
		}
	}



	/**
	 * @method remove
	 * This method is used to delete user permission.
	 * @param $id
	 */
	public function remove($id)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('UserPermissionsController', 'Delete', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		if ($this->UserPermission->delete($id)) {
			$this->redirect('/user_permissions');
			$this->Session->setFlash(__('User Permissions is removed successfully')."!", 'flash_success');
		} else {
			$this->Session->setFlash(__('User Permissions is not removed successfully')."!", 'flash_failure');
		}
	}



	/**
	 * @method getactions
	 * This method is used for load controller's actions.
	 * @param $controllerId
	 */
	public function getactions($controllerId)
	{
		$controllerActions = $this->Acl->Aco->find(
			'list',
			array(
				'fields' => array(
					'id',
					'Actions'
				),
				'conditions' => array(
					'parent_id' => $controllerId
				)
			)
		);
		$this->set('controllerActions', $controllerActions);
		$this->layout = false;
	}
}


// vim: ts=4
