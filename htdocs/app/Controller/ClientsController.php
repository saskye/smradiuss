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
 * Client
 *
 * @class ClientsController
 *
 * @brief This class manages clients.
 */
class ClientsController extends AppController
{

	/**
	 * @method index
	 * This method is used for fetching list of clients with pagination.
	 */
	public function index()
	{
		$this->Client->recursive = -1;
		$this->paginate = array('limit' => PAGINATION_LIMIT);
		$client = $this->paginate();
		$this->set('client', $client);
	}



	/**
	 * @method add
	 * This method is used to add clients.
	 */
	public function add()
	{
		if ($this->request->is('post')) {
			$this->Client->set($this->request->data);
			if ($this->Client->validates()) {
				$this->Client->save($this->request->data);
				$this->Session->setFlash(__('Client is saved succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('Client is not saved succefully!', true), 'flash_failure');
			}
		}
	}



	/**
	 * @method edit
	 * @param $id
	 * This method is used to edit clients.
	 */
	public function edit($id)
	{
		// Assigning client data to var.
		$client = $this->Client->findById($id);
		$this->set('client', $client);
		if ($this->request->is('post')) {
			$this->Client->set($this->request->data);
			if ($this->Client->validates()) {
				$this->Client->id = $id;
				$this->Client->save($this->request->data);
				$this->Session->setFlash(__('Client is edited succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('Client is not edited succefully!', true), 'flash_failure');
			}
		}
	}



	/**
	 * @method remove
	 * @param $id
	 * This method is used to delete clients.
	 */
	public function remove($id)
	{
		if ($this->Client->delete($id)) {
			$this->redirect('/clients/index');
			$this->Session->setFlash(__('Client is removed succefully!', true), 'flash_success');
		} else {
			$this->Session->setFlash(__('Client is not removed succefully!', true), 'flash_failure');
		}
	}
}

// vim: ts=4
