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
 * Client Attributes
 *
 */

class ClientAttributesController extends AppController {

	/* index function
	 * @param $clientID
	 * Functon loads list of client attributes with pagination
	 *
	 */
		public function index($clientID){
			if (isset($clientID)){
				// Fetching records with pagination
				$this->paginate = array(
				'limit' => PAGINATION_LIMIT,
				'conditions' => array('ClientAttribute.ClientID' => $clientID));
				$clientAttributes = $this->paginate();

				$this->set('clientAttributes', $clientAttributes);
				$this->set('clientID', $clientID);
			} else {
				$this->redirect('/client_attributes/index');
			}
		}

	/* add function
	 * @param $clientID
	 * Function used to add client attributes.
	 *
	 */
		public function add($clientID){
			$this->set('clientID', $clientID);
			if ($this->request->is('post')){
				$this->request->data['ClientAttribute']['Disabled'] = intval($this->request->data['ClientAttribute']['Disabled']);
				$this->request->data['ClientAttribute']['ClientID'] = intval($this->request->params['pass'][0]);
				$this->ClientAttribute->set($this->request->data);
				// Validating user inputs.
				if ($this->ClientAttribute->validates()) {
					//Saving data to table.
					$this->ClientAttribute->save($this->request->data);
					$this->Session->setFlash(__('Client attribute is saved succefully!', true), 'flash_success');
				} else {
					$this->Session->setFlash(__('Client attribute is not saved succefully!', true), 'flash_failure');
				}
			}
		}

	/* edit function
	 * @param $id , $clientID
	 * Function used to edit client attributes.
	 *
	 */
		public function edit($id, $clientID){
			$clientAttribute = $this->ClientAttribute->findById($id);
			$this->set('clientAttribute', $clientAttribute);
			if ($this->request->is('post')){
				$this->request->data['ClientAttribute']['Disabled'] = intval($this->request->data['ClientAttribute']['Disabled']);
				$this->ClientAttribute->set($this->request->data);
				if ($this->ClientAttribute->validates()) {
					$this->ClientAttribute->id = $id;
					$this->ClientAttribute->save($this->request->data);
					$this->Session->setFlash(__('Clien attribute is saved succefully!', true), 'flash_success');
				} else {
					$this->Session->setFlash(__('Clien attribute is not saved succefully!', true), 'flash_failure');
				}
			}
		}

	/* remove function
	 * @param $id , $clientID
	 * Function used to delete client attributes when clientID and id is matched.
	 *
	 */
		public function remove($id, $clientID){
			if (isset($id)){
				// Deleting then redirecting to index
				if($this->ClientAttribute->delete($id)){
					$this->redirect('/client_attributes/index/'.$clientID);
					$this->Session->setFlash(__('Client attribute is removed succefully!', true), 'flash_success');
				} else {
					$this->Session->setFlash(__('Client attribute is not removed succefully!', true), 'flash_failure');
			}
			} else {
				$this->redirect('/client_attributes/index'.$clientID);
			}
		}
}

// vim: ts=4
