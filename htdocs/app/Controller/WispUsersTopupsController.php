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
 * Wisp Users Topups
 *
 * @class WispUsersTopupsController
 *
 * @brief This class manages wisp users topups.
 */
class WispUsersTopupsController extends AppController
{

	/**
	 * @method index
	 * @param $userId
	 * This method is used to show wisp user topups with pagination.
	 */
	public function index($userId)
	{
		if (isset($userId)) {
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



	/**
	 * @method add
	 * @param $userId
	 * This method is used to add wisp user topups.
	 */
	public function add($userId)
	{
		if (isset($userId)) {
			$this->set('userId', $userId);
			// Checking button submission.
			if ($this->request->is('post')) {
				$this->WispUsersTopup->set($this->request->data);
				// Validating input.
				if ($this->WispUsersTopup->validates()) {
					// Saving data.
					$requestData['WispUsersTopup']['UserID'] = $userId;
					$requestData['WispUsersTopup']['ValidFrom'] = $requestData['WispUsersTopup']['valid_from'];
					$requestData['WispUsersTopup']['ValidTo'] = $requestData['WispUsersTopup']['valid_to'];
					$this->WispUsersTopup->save($requestData);
					$this->Session->setFlash(__('Wisp user topup is saved succefully!', true), 'flash_success');
				} else {
					$this->Session->setFlash(__('Wisp user topup is not saved!', true), 'flash_failure');
				}
			}
		}
	}



	/**
	 * @method edit
	 * @param $id
	 * @param $userId
	 * This method is used to edit wisp user topups.
	 */
	public function edit($id, $userId)
	{
		// Loading topup data from user Id.
		$topups = $this->WispUsersTopup->findById($id);
		$this->set('topup', $topups);
		$this->set('userId', $userId);
		// Checking submission.
		if ($this->request->is('post')) {
			// Setting data to model.
			$this->WispUsersTopup->set($this->request->data);
			// Validating data.
			if ($this->WispUsersTopup->validates()) {
				// Saving edited data.
				$this->WispUsersTopup->updateAll(
					array(
						'Type' => "'".$requestData['WispUsersTopup']['Type']."'",
						'Value' => "'".$requestData['WispUsersTopup']['Value']."'",
						'ValidFrom' => "'".$requestData['WispUsersTopup']['valid_from']."'",
						'ValidTo' => "'".$requestData['WispUsersTopup']['valid_to']."'"
					),
					array(
						'ID' => $id
					)
				);
				$this->Session->setFlash(__('Wisp user topup is edit succefully!', true), 'flash_success');
				// For page reload to reflect data.
				$topups = $this->WispUsersTopup->findById($id);
				$this->set('topup', $topups);
			} else {
				$this->Session->setFlash(__('Wisp user topup is not edit!', true), 'flash_failure');
			}
		}
	}



	/**
	 * @method remove
	 * @param $id
	 * @param $userId
	 * This method is used to delete wisp user topups.
	 */
	public function remove($id, $userId)
	{
		if (isset($id)) {
			// Deleting
			if ($this->WispUsersTopup->delete($id)) {
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
