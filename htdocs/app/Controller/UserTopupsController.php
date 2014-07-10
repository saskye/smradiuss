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
 * User topups
 *
 * @class UserTopupsController
 *
 * @brief This class manages topups for user.
 */
class UserTopupsController extends AppController
{

	/**
	 * @method index
	 * @param $userId
	 * This method is used to show user topups list with pagination.
	 */
	public function index($userId)
	{
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
	 * @param $userId
	 * This method is used to add user topups.
	 */
	public function add($userId)
	{
		if (isset($userId)) {
			$this->set('userId', $userId);

			// Checking button submission.
			if ($this->request->is('post')) {
				$this->UserTopup->set($this->request->data);

				// Validating input.
				if ($this->UserTopup->validates()) {
					// Saving data.
			    	$this->UserTopup->InsertRec($userId,$this->request->data);
					$this->Session->setFlash(__('User topup is saved succefully!', true), 'flash_success');
				} else {
					$this->Session->setFlash(__('User topup is not saved succefully!', true), 'flash_failure');
				}
			}
		}
	}



	/**
	 * @method edit
	 * @param $id
	 * @param $userId
	 * This method is used to edit user topups.
	 */
	public function edit($id, $userId)
	{
		// Loading topup data from user Id.
		$topups = $this->UserTopup->findById($id);
		$this->set('topup', $topups);
		$this->set('userId', $userId);

		// Checking submission.
		if ($this->request->is('post')) {
			// Setting data to model.
			$this->UserTopup->set($this->request->data);

			// Validating data.
			if ($this->UserTopup->validates()) {
				// Saving edited data.
				$this->UserTopup->editRec($id, $this->request->data);
				$this->Session->setFlash(__('User topup is saved succefully!', true), 'flash_success');
				// For page reload to reflect new data.
				$topups = $this->UserTopup->findById($id);
				$this->set('topup', $topups);
			} else {
				$this->Session->setFlash(__('User topup is not saved succefully!', true), 'flash_failure');
			}
		}
	}



	/**
	 * @method remove
	 * @param $id
	 * @param $userId
	 * This method is used to delete user topups.
	 */
	public function remove($id, $userId)
	{
		if (isset($id)) {
			// Deleting
			if ($this->UserTopup->delete($id)) {
				$this->redirect('/user_topups/index/'.$userId);
				$this->Session->setFlash(__('User topup is removed succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('User topup is not removed succefully!', true), 'flash_failure');
			}
		} else {
			$this->redirect('/user_topups/index'.$userId);
		}
	}
}

// vim: ts=4
