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
 * Realm Attributes
 *
 * @class RealmAttributesController
 *
 * @brief This class manages the attributes for realm.
 */
class RealmAttributesController extends AppController
{

	/**
	 * @method index
	 * @param $realmId
	 * This method is used for showing realms attribures with pagination.
	 */
	public function index($realmId)
	{
		if (isset($realmId)) {
			$this->paginate = array(
				'limit' => PAGINATION_LIMIT,
				'conditions' => array('RealmAttribute.RealmID' => $realmId)
			);
			$realmAttributes = $this->paginate();

			$this->set('realmAttributes', $realmAttributes);
			$this->set('realmId', $realmId);
		} else {
			$this->redirect('/realm_attributes/index');
		}
	}



	/**
	 * @method add
	 * @param $realmId
	 * This method is used to add realms attributes.
	 */
	public function add($realmId)
	{
		$this->set('realmId', $realmId);
		if ($this->request->is('post')) {
			$this->request->data['RealmAttribute']['Disabled'] = intval($this->request->data['RealmAttribute']['Disabled']);
			$this->request->data['RealmAttribute']['RealmID'] = intval($this->request->params['pass'][0]);
			$this->RealmAttribute->set($this->request->data);
			if ($this->RealmAttribute->validates()) {
				$this->RealmAttribute->save($this->request->data);
				$this->Session->setFlash(__('Realm attribute is saved succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('Realm attribute is not saved succefully!', true), 'flash_failure');
			}
		}
	}



	/**
	 * @method edit
	 * @param $id
	 * This method is used to edit realms attributes.
	 */
	public function edit($id)
	{
		$realmAttribute = $this->RealmAttribute->findById($id);
		$this->set('realmAttribute', $realmAttribute);
		// Checking submitted or not.
		if ($this->request->is('post')) {
			$this->request->data['RealmAttribute']['Disabled'] = intval($this->request->data['RealmAttribute']['Disabled']);
			// Setting submitted data.
			$this->RealmAttribute->set($this->request->data);
			if ($this->RealmAttribute->validates()) {
				$this->RealmAttribute->id = $id;
				$this->RealmAttribute->save($this->request->data);
				$this->Session->setFlash(__('Realm attribute is saved succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('Realm attribute is not saved succefully!', true), 'flash_failure');
			}
		}
	}



	/**
	 * @method remove
	 * @param $id
	 * @param $realmId
	 * This method is used to delete realms attributes.
	 */
	public function remove($id, $realmId)
	{
		if (isset($id)) {
			// Deleting & checking successful or not.
			if ($this->RealmAttribute->delete($id)) {
				// Redirecting to realms attribute index function.
				$this->redirect('/realm_attributes/index/'.$realmId);
				$this->Session->setFlash(__('Realm attribute is removed succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('Realm attribute is not removed succefully!', true), 'flash_failure');
			}
		} else {
			$this->redirect('/realm_attributes/index'.$realmId);
		}
	}
}

// vim: ts=4
