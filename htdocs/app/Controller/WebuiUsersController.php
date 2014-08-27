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
 * @class WebuiUsersController
 *
 * @brief This class manages webuiUsers.
 */
class WebuiUsersController extends AppController
{
	// This variable is used for include other conponents.
	var $components = array('Auth', 'Acl');


	/**
	 * @method beforeFilter
	 * This method executes method that we need to be executed before any other action.
	 */
	function beforeFilter()
	{
		parent::beforeFilter();
		$this->Auth->userModel = 'WebuiUser';
		$this->Auth->allow('login', 'logout');
	}



	/**
	 * @method login
	 * This method is used for check user authentication.
	 */
	public function login()
	{
		if ($this->request->is('post')) {
			$requestData = $this->WebuiUser->set($this->request->data);
			if ($this->Auth->login($requestData)) {
				$password = Security::hash($requestData['WebuiUser']['Password'], 'sha1', true);
				$selectData = $this->WebuiUser->find(
					'all',
					array(
						'conditions' => array(
							'Username' => $requestData['WebuiUser']['Username'],
							'Password' => $password
						)
					)
				);
				if (!empty($selectData)) {
					$this->Session->write('User.ID', $selectData[0]['WebuiUser']['ID']);
					$this->Session->write('User.Type', $selectData[0]['WebuiUser']['Type']);
					return $this->redirect($this->Auth->redirect('/users/index'));
				} else {
					// Handling REST response here
					if ($this->request->accepts('application/json')) {
						throw new UnauthorizedException("Login Failed");
					}

					$this->Session->setFlash(__('Invalid username or password, try again'), 'flash_failure');
				}
			}
		}
	}



	/**
	 * @method logout
	 * This method is used for clear user authentication.
	 */
	public function logout()
	{
		$this->Auth->logout();
		$this->redirect('/webui_users/login');
	}
}



// vim: ts=4
