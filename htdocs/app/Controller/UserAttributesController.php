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
 * User Attributes
 *
 * @class UserAttributesController
 *
 * @brief This class manages the attributes for users.
 */
class UserAttributesController extends AppController
{

	/**
	 * @method index
	 * @param $userId
	 * This method is used to show list of user attributes with pagination.
	 */
	public function index($userId)
	{
		if (isset($userId)) {
			$this->UserAttribute->recursive = 0;
			$this->paginate = array(
				'limit' => PAGINATION_LIMIT,
				'conditions' => array('UserAttribute.UserID' => $userId)
			);
			$userAttributes  = $this->paginate();
			$this->set('userAttributes', $userAttributes);
			$this->set('userId', $userId);
		} else {
			$this->redirect('/users/index');
		}
	}



	/**
	 * @method add
	 * @param $userId
	 * This method is used to add users attributes.
	 */
	public function add($userId)
	{
		$this->set('userId', $userId);

		if ($this->request->is('post')) {
			$this->request->data['UserAttribute']['Disabled'] = intval($this->request->data['UserAttribute']['Disabled']);
			$this->request->data['UserAttribute']['UserID'] = intval($this->request->params['pass'][0]);
			$this->UserAttribute->set($this->request->data);

			// Validating
			if ($this->UserAttribute->validates()) {
				// Saving
				$this->UserAttribute->save($this->request->data);
				$this->Session->setFlash(__('User attribute is saved succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('User attribute is not saved succefully!', true), 'flash_failure');
			}
		}
	}



	/**
	 * @method edit
	 * @param $id
	 * @param $userId
	 * This method is used to edit users attributes.
	 */
	public function edit($id, $userId)
	{
		$userAttribute = $this->UserAttribute->findById($id);
		$this->set('userAttribute', $userAttribute);

		if ($this->request->is('post')) {
			$this->request->data['UserAttribute']['Disabled'] = intval($this->request->data['UserAttribute']['Disabled']);
			$this->UserAttribute->set($this->request->data);

			if ($this->UserAttribute->validates()) {
				$this->UserAttribute->id = $id;
			    $this->UserAttribute->save($this->request->data);
				$this->Session->setFlash(__('Attribute is saved succefully!', true), 'flash_success');
			} else {
			    $this->Session->setFlash(__('Attribute is not saved succefully!', true), 'flash_failure');
			}
		}
	}



	/**
	 * @method remove
	 * @param $id
	 * @param $userId
	 * This method is used to delete users attributes.
	 */
	public function remove($id, $userId)
	{
		if (isset($id)) {
			// Deleting and checking.
			if ($this->UserAttribute->delete($id)) {
				// Redirecting to index.
				$this->redirect('/user_attributes/index/'.$userId);
				$this->Session->setFlash(__('User is removed succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('User is not removed succefully!', true), 'flash_failure');
			}
		} else {
			$this->redirect('/user_attributes/index'.$userId);
		}
	}
}

// vim: ts=4
