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
 * Wisp Users Topup
 *
 */
class WispUsersTopupsController extends AppController
{
	/* index function
	 * @param $userId
	 * Used to show user topups with pagination.
	 *
	 */
	public function index($userId)
	{
		if (isset($userId))
		{
			$this->WispUsersTopup->recursive = 0;
			$this->paginate = array(
                'limit' => PAGINATION_LIMIT,
				'conditions' => array('UserID' => $userId)
			);

			$wtopups  = $this->paginate();
			$this->set('wtopups', $wtopups);
			$this->set('userId', $userId);
		}
	}

	/* add function
	 * @param $userId
	 * Used to add user topups
	 *
	 */
	public function add($userId)
	{
		if (isset($userId))
		{
			$this->set('userId', $userId);
			// Checking button submission.
			if ($this->request->is('post'))
			{
				$this->WispUsersTopup->set($this->request->data);
				// Validating input.
				if ($this->WispUsersTopup->validates())
				{
					// Saving data.
			    	$this->WispUsersTopup->InsertRec($userId,$this->request->data);
					$this->Session->setFlash(__('Wisp user topup is saved succefully!', true), 'flash_success');
				}
				else
				{
			    	$this->Session->setFlash(__('Wisp user topup is not saved!', true), 'flash_failure');
				}
			}
		}
		else
		{

		}
	}
	/* edit function
	 * @param $id, $userId
	 * Used to edit user topups
	 *
	 */
	public function edit($id, $userId){
		// Loading topup data from user Id.
		$topups = $this->WispUsersTopup->findById($id);
		$this->set('topup', $topups);
		$this->set('userId', $userId);
		// Checking submission.
		if ($this->request->is('post')){
			// Setting data to model.
			$this->WispUsersTopup->set($this->request->data);
			// Validating data.
			if ($this->WispUsersTopup->validates()) {
				// Saving edited data.
				$this->WispUsersTopup->editRec($id, $this->request->data);
				$this->Session->setFlash(__('Wisp user topup is edit succefully!', true), 'flash_success');

				// For page reload to reflect data.
				$topups = $this->WispUsersTopup->findById($id);
				$this->set('topup', $topups);
			} else {
			    $this->Session->setFlash(__('Wisp user topup is not edit!', true), 'flash_failure');
			}
		}
	}
	/* remove function
	 * @param $id, $userId
	 * Used to delete user topups.
	 *
	 */
	public function remove($id, $userId){
		if (isset($id)){
			// Deleting
			if($this->WispUsersTopup->delete($id)){
				// Redecting to index function.
				$this->redirect('/wispUsers_topups/index/'.$userId);
				$this->Session->setFlash(__('User topup is removed succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('User topup is not removed succefully!', true), 'flash_failure');
			}
		} else {
			$this->redirect('/wispUsers_topups/index/'.$userId);
		}
	}
}

// vim: ts=4