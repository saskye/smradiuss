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
		$permissionList = $this->Acl->Aro->Permission->find('all');
		$this->set('permissionList', $permissionList);
	}



	/**
	 * @method add
	 * This method is used for add user permission.
	 */
	public function add()
	{
		$allGroups = '';
		$groups = $this->UserPermission->selectGroup();
		foreach ($groups as $value) {
			$allGroups[$value['Group']['Name']] = $value['Group']['Name'];
		}
		$this->set('allGroups', $allGroups);
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
						$controllerAlias = $this->Acl->Aco->find(
							'first',
							array(
								'fields' => 'alias',
								'conditions' => array(
									'id' => $actionId
								)
							)
						);
						$this->Acl->allow(
							$requestData['UserPermission']['aro_id'],
							$controllerAlias['Aco']['alias']
						);
					}
				}
				$this->Session->setFlash(__('Permission added succefully!'), 'flash_success');
			} else {
				$this->Session->setFlash(__('Permission not added!'), 'flash_failure');
			}
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
