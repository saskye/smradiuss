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
	/**
	 * @var $components
	 * Variable $components is used for load other components.
	 */
	var $components = array('Auth', 'Acl','Access');


	/**
	 * @var $helpers
	 * This variable is used for include other helper file.
	 */
	var $helpers = array('Access');


	/**
	 * @methos beroreFilter
	 * @brief This method is used for executes functions that we need to be executed before any other action.
	 */
	function beforeFilter()
	{
		parent::beforeFilter();
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
	 * @method edit
	 * This method is used for edit permission.
	 * @param $id
	 */
	public function edit($id)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('UserPermissionsController', 'Edit', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		// Fetching permission data.
		$permissionData = $this->Acl->Aro->Permission->find(
			'first',
			array(
				'conditions' => array(
					'Permission.id' => $id
				)
			)
		);
		$this->set('permissionData', $permissionData);
		$aroId = $permissionData['Aro']['id'];
		$this->set('aroId', $aroId);
		$acoId = $permissionData['Aco']['parent_id'];
		$this->set('acoId', $acoId);
		if ($this->request->is('post')) {
			$requestData = $this->UserPermission->set($this->request->data);
			$arosId = $requestData['UserPermission']['aro_id'];
			$acosId = $requestData['UserPermission']['aco_id'];
			if ($arosId == $permissionData['Aro']['id'] && $acosId == $permissionData['Aco']['parent_id']) {
				$getActions = array_slice($requestData, 1);
				if ($this->UserPermission->validates()) {
					// Fetching controller name by id.
					$controllerName = $this->Acl->Aco->find(
						'first',
						array(
							'fields' => array(
								'alias'
							),
							'conditions' => array(
								'parent_id IS Null',
								'id' => $acosId
							)
						)
					);
					// Fetching action name by id.
					$actionName = $this->Acl->Aro->find(
						'first',
						array(
							'fields' => array(
								'alias'
							),
							'conditions' => array(
								'id' => $arosId
							)
						)
					);
					// Fetching all action's id by it's parent id.
					$allActions = $this->Acl->Aco->find(
						'list',
						array(
							'fields' => array(
								'id'
							),
							'conditions' => array(
								'parent_id' => $acosId
							)
						)
					);
					// Deleting previous records.
					foreach ($allActions as $actions) {
						$this->UserPermission->deleteAll(
							array(
								'aro_id' => $arosId,
								'aco_id' => $actions
							)
						);
					}
					foreach ($requestData['permission'] as $actionId) {
						if ($actionId != 0) {
							// Fetching controller alias name by controller's action id.
							$controllerAlias = $this->Acl->Aco->find(
								'first',
								array(
									'fields' => 'alias',
									'conditions' => array(
										'id' => $actionId
									)
								)
							);
							// Save updated records.
							$this->Acl->allow(
								$actionName['Aro']['alias'],
								$controllerAlias['Aco']['alias']
							);
						}
					}
					$this->Session->setFlash(__('User Permission was edited successfully')."!", 'flash_success');
					$this->redirect('/user_permissions');
				} else {
					$this->Session->setFlash(__('User Permission was not edited successfully')."!", 'flash_failure');
				}
			} else {
				$this->Session->setFlash(__('Error occured on submission')."!", 'flash_failure');
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



	/**
	 * @method editActions
	 * This method is used for edit actions permission.
	 * @param $controllerId
	 */
	public function editActions($typeId, $controllerId)
	{
		// Fetching controller actions.
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
		// Fetching controller action's permission.
		$aco = array();
		foreach ($controllerActions as $key => $value) {
			array_push($aco, $this->Acl->Aro->Permission->find(
					'first',
					array(
						'conditions' => array(
							'aco_id' => $key,
							'aro_id' => $typeId
						)
					)
				)
			);
		}
		$i = 0;
		foreach ($controllerActions as $key => $value) {
			if (isset($aco[$i]['Aco'])) {
				if (in_array($value,$aco[$i]['Aco'])) {
					$userAction[$i]['id'] = $key;
					$userAction[$i]['value'] = $value;
					$userAction[$i]['checked'] = '1';
				} else {
					$userAction[$i]['id'] = $key;
					$userAction[$i]['value'] = $value;
					$userAction[$i]['checked'] = '0';
				}
			} else {
				$userAction[$i]['id'] = $key;
				$userAction[$i]['value'] = $value;
				$userAction[$i]['checked'] = '0';
			}
			$i++;
		}
		$this->set('userActions', $userAction);
		$this->layout = false;
	}
}


// vim: ts=4
