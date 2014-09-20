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
 * @class UserTypeMembersController
 *
 * @brief This class manages the user type members.
 */
class UserTypeMembersController extends AppController
{
	// Variable $components that load other components.
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
	 * This method is used for Showing user type members list with pagination.
	 * @param $userTypeId
	 */
	public function index($userTypeId)
	{
		// Get user group name.
		$groupName = $this->Access->getGroupName($this->Session->read('User.ID'));
		$this->set('groupName', $groupName);
		// Check permission.
		$permission = $this->Access->checkPermission('UserTypeMembersController', 'View', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		if (isset($userTypeId)) {
			$this->UserTypeMember->recursive = -1;
			$this->paginate = array(
					'limit' => PAGINATION_LIMIT,
					'conditions' => array('Type' => $userTypeId)
			);
			$userTypes = $this->paginate();
			$this->set('userTypes', $userTypes);
			$this->set('userTypeId', $userTypeId);
		} else {
			$this->redirect('/userType_members/index');
		}
	}



	/**
	 * @method remove
	 * This method is used to delete user type member.
	 * @param $userId
	 * @param $typeId
	 */
	public function remove($userId, $typeId)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('UserTypeMembersController', 'Delete', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		// Deleting
		if (isset($userId)) {
			$this->UserTypeMember->id = $userId;
			if ($this->UserTypeMember->saveField('Type' , 0)) {
				// Redirected to index function.
				$this->redirect('/userType_members/index/'.$typeId);
				$this->Session->setFlash(__('User type member is removed successfully')."!", 'flash_success');
			} else {
				$this->Session->setFlash(__('User Type member is not removed successfully')."!", 'flash_failure');
			}
		} else {
			$this->redirect('/userType_members/index/'.$typeId);
		}
	}
}



// vim: ts=4
