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
 * Group Attribute
 *
 * @class GroupAttributesController
 *
 * @brief This class manage the group attributes.
 */
class  GroupAttributesController extends AppController {

	/**
	 * @method index
	 * @param  $groupId
	 * This method is used to load list of group attributes with pagination.
	 */
	public function index($groupId){
		if (isset($groupId)){
			// Fetching data with pagination.
			$this->paginate = array(
			'limit' => PAGINATION_LIMIT,
			'conditions' => array('GroupAttribute.GroupID' => $groupId)
			);
			$groupAttributes = $this->paginate();

			$this->set('groupAttributes', $groupAttributes);
			$this->set('groupId', $groupId);
		} else {
			$this->redirect('/users/index');
		}
	}

	/**
	 * @method add
	 * @param $groupId
	 * This method is used to add group attributes.
	 */
	public function add($groupId){
		$this->set('groupId', $groupId);
		if ($this->request->is('post')){
			$this->request->data['GroupAttribute']['Disabled'] = intval($this->request->data['GroupAttribute']['Disabled']);
			$this->request->data['GroupAttribute']['GroupID'] = intval($this->request->params['pass'][0]);
			$this->GroupAttribute->set($this->request->data);
			// Validating entered data.
			if ($this->GroupAttribute->validates()) {
				// Saving data to table.
				$this->GroupAttribute->save($this->request->data);
				$this->Session->setFlash(__('Group attribute is saved succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('Group attribute is not saved succefully!', true), 'flash_failure');
			}
		}
	}

	/**
	 * @method edit
	 * @param $id
	 * @param $groupId
	 * This method is used to edit group attributes.
	 */
	public function edit($id, $groupId){
		// Assigning group attribues values find by id to var.
		$groupAttribute = $this->GroupAttribute->findById($id);
		$this->set('groupAttribute', $groupAttribute);
		if ($this->request->is('post')){
			$this->request->data['GroupAttribute']['Disabled'] = intval($this->request->data['GroupAttribute']['Disabled']);
			$this->GroupAttribute->set($this->request->data);
			if ($this->GroupAttribute->validates()) {
				$this->GroupAttribute->id = $id;
				//Saving data to the table.
				$this->GroupAttribute->save($this->request->data);
				$this->Session->setFlash(__('Attribute is saved succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('Attribute is not saved succefully!', true), 'flash_failure');
			}
		}
	}

	/**
	 * @method remove
	 * @param $id
	 * @param $groupId
	 * This method is used to delete group attributes.
	 */
	public function remove($id, $groupId){
		if (isset($id)){
			// Deleting then redirecting to index function.
			if($this->GroupAttribute->delete($id)){
				$this->redirect('/group_attributes/index/'.$groupId);
				$this->Session->setFlash(__('Attribute is removed succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('Attribute is not removed succefully!', true), 'flash_failure');
			}
		} else {
		$this->redirect('/group_attributes/index'.$userId);
		}
	}

}

// vim: ts=4
