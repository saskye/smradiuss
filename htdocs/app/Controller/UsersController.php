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



App::uses('AWITJsonView', 'Lib/View');

/**
 * Users
 *
 * @class UsersController
 *
 * @brief This class manages users.
 */
class UsersController extends AppController
{



	/**
	 * @var $use
	 * This variable is used for including UserAttribute table.
	 */
	public $use = array('UserAttribute');



	/**
	 * @var $components
	 * This variable is used for including components.
	 */
	public $components = array(
		'RequestHandler' => array(
			'viewClassMap' => array(
				'json' => 'AWITJson',
			)
		)
	);



	/**
	 * @method index
	 * This method is used to show users list with pagination.
	 */
	public function index()
	{
		$this->User->recursive = -1;
		$this->paginate = array('limit' => PAGINATION_LIMIT);
		$users = $this->paginate();
		$this->set('users', $users);

		$this->set('_serialize', array('users'));
	}


	/**
	 * @method indexall
	 * This method is used to show users list with pagination.
	 */
	public function indexall()
	{
		$this->User->hasMany = array(
			'UserAttribute' => array(
				'className' => 'UserAttribute',
				'foreignKey' => 'UserID',
				'associationForeignKey' => 'ID',
				'conditions' => array('UserAttribute.Name' => 'User-Password'),
			)
		);

		$this->User->recursive = 1;
		$this->paginate = array('limit' => PAGINATION_LIMIT);
		$users = $this->paginate();

		$retUsers = array();
		foreach ($users as $user) {
			if (isset($user['UserAttribute'][0]['Value'])) {
				$user['User']['Password'] = $user['UserAttribute'][0]['Value'];
			}
			array_push($retUsers, array('User' => $user['User']));
		}

		$users = $retUsers;

		// Processing REST requests
		$this->set(compact('users'));
		$this->set('_serialize', array('users'));
	}




	/**
	 * @method add
	 * This method is used to add users.
	 */
	public function add()
	{
		if ($this->request->is('post')) {
			$this->request->data['User']['Disabled'] = intval($this->request->data['User']['Disabled']);
			$this->User->set($this->request->data);

			if ($this->User->validates()) {
				$this->User->save($this->request->data);
				$this->Session->setFlash(__('User is saved succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('User is not saved succefully!', true), 'flash_failure');
			}
		}
	}



	/**
	 * @method edit
	 * @param $id
	 * This method is used to edit users.
	 */
	public function edit($id)
	{
		// Finding users data and assigning to var $user.
		$user = $this->User->findById($id);
		$this->set('user', $user);

		// Checking button is clicked or not.
		if ($this->request->is('post')) {
			$this->request->data['User']['Disabled'] = intval($this->request->data['User']['Disabled']);
			$this->User->set($this->request->data);

			// Validating submitted data.
			$this->User->id = $id;
			if ($this->User->validates()) {
				// Save to database.
				$this->User->save($this->request->data);
				$this->Session->setFlash(__('User is edited succefully!', true), 'flash_success');
				// To load page with new data saved above.
				$user = $this->User->findById($id);
				$this->set('user', $user);
			} else {
				$this->Session->setFlash(__('User is not edited succefully!', true), 'flash_failure');
			}
		}
	}



	/**
	 * @method remove
	 * @param $id
	 * This method is used to delete users.
	 */
	public function remove($id)
	{
		// Deleting & checking done or not.
		if ($this->User->delete($id)) {
			// Deleting user reference data from other db tables.
			$this->User->deleteUser($id);
			// Redirecting users to index function.
			$this->redirect('/users/index');
			$this->Session->setFlash(__('User is removed succefully!', true), 'flash_success');
		} else {
			$this->Session->setFlash(__('User is not removed succefully!', true), 'flash_failure');
		}
	}
}

// vim: ts=4
