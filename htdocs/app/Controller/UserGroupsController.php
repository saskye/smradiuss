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
 * @class UserGroupsController
 *
 * @brief This class manages groups for user.
 */
class UserGroupsController extends AppController
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
	 * This method is used to show user groups list with pagination.
	 * @param $userId
	 */
	public function index($userId)
	{
		// Get user group name.
		$groupName = $this->Access->getGroupName($this->Session->read('User.ID'));
		$this->set('groupName', $groupName);
		// Check permission.
		$permission = $this->Access->checkPermission('UserGroupsController', 'View', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		if (isset($userId)) {
			$this->UserGroup->recursive = 0;
			$this->paginate = array(
				'limit' => PAGINATION_LIMIT,
				'conditions' => array('UserID' => $userId)
			);
			// Contains data with pagination.
			$UserGroups  = $this->paginate();
			$UserGroupData =array();

			// Adding group name to array.
			foreach ($UserGroups as $userGroup) {
				$groupData= $this->UserGroup->getGroupById($userGroup['UserGroup']['GroupID']);

				if (isset($groupData[0]['Group']['Name'])) {
					$userGroup['UserGroup']['group'] = $groupData[0]['Group']['Name'];
				}
				$UserGroupData[] = $userGroup;
			}
			$UserGroup = $UserGroupData;
			$this->set('UserGroup', $UserGroup);
			$this->set('userId', $userId);
		}
	}



	/**
	 * @method add
	 * This method is used to add user groups.
	 * @param $userId
	 */
	public function add($userId)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('UserGroupsController', 'Add', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		if (isset($userId)) {
			$this->set('userId', $userId);
			//Fetching  all groups.
			$arr = array();
			$groupItems = $this->UserGroup->selectGroup();

			foreach ($groupItems as $val) {
				$arr[$val['Group']['ID']] = $val['Group']['Name'];
			}
			$this->set('arr', $arr);

			// Checking submission.
			if ($this->request->is('post')) {
				$requestData = $this->UserGroup->set($this->request->data);
					// Validating submitted data.
				if ($this->UserGroup->validates()) {
					// Saving user groups.
					$requestData['UserGroup']['UserID'] = $userId;
					$this->UserGroup->save($requestData);
					// Sending message to screen.
					$this->Session->setFlash(__('User Group is saved succefully')."!", 'flash_success');
				} else {
					$this->Session->setFlash(__('User Group is not saved succefully')."!", 'flash_failure');
				}
			}
		}
	}



	/**
	 * @method remove
	 * This method is used to delete user groups.
	 * @param $id
	 * @param $userId
	 */
	public function remove($id, $userId)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('UserGroupsController', 'Add', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		if (isset($id)) {
			// Deleting
			if ($this->UserGroup->delete($id)) {
				// Redirecting to index.
				$this->redirect('/user_groups/index/'.$userId);
				$this->Session->setFlash(__('User group is removed succefully')."!", 'flash_success');
			} else {
				$this->Session->setFlash(__('User group is not removed succefully')."!", 'flash_failure');
			}
		} else {
			$this->redirect('/user_groups/index'.$userId);
		}
	}
}

// vim: ts=4
