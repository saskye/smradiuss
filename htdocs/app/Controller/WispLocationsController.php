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
 * Wisp locations
 *
 * @class WispLocationsController
 *
 * @brief This class manages locations.
 */
class WispLocationsController extends AppController
{

	/**
	 * @method index
	 * This method is used to show all locations with pagination.
	 */
	public function index()
	{
		$this->WispLocation->recursive = -1;
		$this->paginate = array('limit' => PAGINATION_LIMIT );
		// Fetching and assigning to variable.
		$wispLocation = $this->paginate();
		$this->set('wispLocation', $wispLocation);
	}



	/**
	 * @method add
	 * This method is used to add locations.
	 */
	public function add()
	{
		// Checking submission.
		if ($this->request->is('post')) {
			// Setting data to model.
			$this->WispLocation->set($this->request->data);
			// Validating submitted data.
			if ($this->WispLocation->validates()) {
			    $this->WispLocation->save($this->request->data);
				$this->Session->setFlash(__('Wisp Location is saved succefully!', true), 'flash_success');
			} else {
			    $this->Session->setFlash(__('Wisp Location is not saved!', true), 'flash_failure');
			}
		}
	}



	/**
	 * @method edit
	 * @param $id
	 * This method is used to edit locations.
	 */
	public function edit($id)
	{
		// Finding location from id and assigning to variable.
		$location = $this->WispLocation->findById($id);
		$this->set('location', $location);
		// Checking submission.
		if ($this->request->is('post')) {
			// Setting submitted data.
			$this->WispLocation->set($this->request->data);
			// Validating submitted data.
			if ($this->WispLocation->validates()) {
				$this->WispLocation->id = $id;
				// Saving data.
			    $this->WispLocation->save($this->request->data);
				$this->Session->setFlash(__('Wisp Location is edited succefully!', true), 'flash_success');
			} else {
			    $this->Session->setFlash(__('Wisp Location is not edited!', true), 'flash_failure');
			}
		}
	}



	/**
	 * @method remove
	 * @param $id
	 * This method is used to delete locations.
	 */
	public function remove($id)
	{
		// Deleting
		if ($this->WispLocation->delete($id)) {
			// Redirecting to index.
			$this->redirect('/WispLocations/index');
			$this->Session->setFlash(__('Wisp Locations is removed succefully!', true), 'flash_success');
		} else {
			$this->Session->setFlash(__('Wisp Locations is not removed!', true), 'flash_failure');
		}
	}
}

// vim: ts=4