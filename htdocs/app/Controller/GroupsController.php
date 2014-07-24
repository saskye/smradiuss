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
 * Groups
 *
 * @class GroupsController
 *
 * @brief This class manages the groups.
 */
class GroupsController extends AppController
{

	/**
	 * @method index
	 * This method is used for Showing group list with pagination.
	 */
	public function index()
	{
		$this->Group->recursive = -1;
		$this->paginate = array('limit' => PAGINATION_LIMIT );
		$groups = $this->paginate();
		$this->set('groups', $groups);
	}



	/**
	 * @method add
	 * This method is used to add groups.
	 */
	public function add()
	{
		if ($this->request->is('post')) {
			$this->Group->set($this->request->data);

			// Validating entered data.
			if ($this->Group->validates()) {
				// Saving data.
				$this->Group->save($this->request->data);
				$this->Session->setFlash(__('Group is saved succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('Group is not saved succefully!', true), 'flash_failure');
			}
		}
	}



	/**
	 * @method edit
	 * @param $id
	 * This method is used to edit groups.
	 */
	public function edit($id)
	{
		$group = $this->Group->findById($id);
		$this->set('group', $group);

		// Checking submit button is clicked or not
		if ($this->request->is('post')) {
			$this->Group->set($this->request->data);

			// Validating submitted data.
			if ($this->Group->validates()) {
				$this->Group->id = $id;
				$this->Group->save($this->request->data);
				$this->Session->setFlash(__('Group is edited succefully!', true), 'flash_success');
				// For reload page to reflect change in data
				$group = $this->Group->findById($id);
				$this->set('group', $group);
			} else {
				$this->Session->setFlash(__('Group is not edited succefully!', true), 'flash_failure');
			}
		}
	}



	/**
	 * @method remove
	 * @param $id
	 * This method is used to delete groups.
	 */
	public function remove($id)
	{
		// Deleting
		if ($this->Group->delete($id)) {
			// Redirected to index function.
			$this->redirect('/groups/index');
			$this->Session->setFlash(__('Group is removed succefully!', true), 'flash_success');
		} else {
			$this->Session->setFlash(__('Group is not removed succefully!', true), 'flash_failure');
		}
	}
}

// vim: ts=4
