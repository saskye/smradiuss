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
 * @class UserTopupsController
 *
 * @brief This class manages topups for user.
 */
class UserTopupsController extends AppController
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
	 * This method is used to show user topups list with pagination.
	 * @param $userId
	 */
	public function index($userId)
	{
		// Get user group name.
		$groupName = $this->Access->getGroupName($this->Session->read('User.ID'));
		$this->set('groupName', $groupName);
		// Check permission.
		$permission = $this->Access->checkPermission('UserTopupsController', 'View', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		if (isset($userId)) {
			$this->UserTopup->recursive = 0;
			$this->paginate = array(
				'limit' => PAGINATION_LIMIT,
				'conditions' => array('UserID' => $userId)
			);
			$topups  = $this->paginate();
			$this->set('topups', $topups);
			$this->set('userId', $userId);
		}
	}



	/**
	 * @method add
	 * This method is used to add user topups.
	 * @param $userId
	 */
	public function add($userId)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('UserTopupsController', 'Add', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		if (isset($userId)) {
			$this->set('userId', $userId);

			// Checking button submission.
			if ($this->request->is('post')) {
				$requestData = $this->UserTopup->set($this->request->data);

				// Validating input.
				if ($this->UserTopup->validates()) {
					// Saving data.
					$requestData['UserTopup']['UserID'] = $userId;
					$requestData['UserTopup']['ValidFrom'] = $requestData['UserTopup']['valid_from'];
					$requestData['UserTopup']['ValidTo'] = $requestData['UserTopup']['valid_to'];
					$this->UserTopup->save($requestData);
					$this->Session->setFlash(__('User topup is saved succefully')."!", 'flash_success');
				} else {
					$this->Session->setFlash(__('User topup is not saved succefully')."!", 'flash_failure');
				}
			}
		}
	}



	/**
	 * @method edit
	 * This method is used to edit user topups.
	 * @param $id
	 * @param $userId
	 */
	public function edit($id, $userId)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('UserTopupsController', 'Edit', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		// Loading topup data from user Id.
		$topups = $this->UserTopup->findById($id);
		$this->set('topup', $topups);
		$this->set('userId', $userId);

		// Checking submission.
		if ($this->request->is('post')) {
			// Setting data to model.
			$requestData = $this->UserTopup->set($this->request->data);

			// Validating data.
			if ($this->UserTopup->validates()) {
				// Saving edited data.
				$this->UserTopup->updateAll(
					array(
						'Type' => "'".$requestData['UserTopup']['Type']."'",
						'Value' => "'".$requestData['UserTopup']['Value']."'",
						'ValidFrom' => "'".$requestData['UserTopup']['valid_from']."'",
						'ValidTo' => "'".$requestData['UserTopup']['valid_to']."'"
					),
					array(
						'ID' => $id
					)
				);
				$this->Session->setFlash(__('User topup is saved succefully')."!", 'flash_success');
				// For page reload to reflect new data.
				$topups = $this->UserTopup->findById($id);
				$this->set('topup', $topups);
			} else {
				$this->Session->setFlash(__('User topup is not saved succefully')."!", 'flash_failure');
			}
		}
	}



	/**
	 * @method remove
	 * This method is used to delete user topups.
	 * @param $id
	 * @param $userId
	 */
	public function remove($id, $userId)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('UserTopupsController', 'Delete', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		if (isset($id)) {
			// Deleting
			if ($this->UserTopup->delete($id)) {
				$this->redirect('/user_topups/index/'.$userId);
				$this->Session->setFlash(__('User topup is removed succefully')."!", 'flash_success');
			} else {
				$this->Session->setFlash(__('User topup is not removed succefully')."!", 'flash_failure');
			}
		} else {
			$this->redirect('/user_topups/index'.$userId);
		}
	}
}

// vim: ts=4
