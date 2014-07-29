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
 * Client Realms
 *
 * @class ClientRealmsController
 *
 * @brief This class manages the client realms.
 */
class ClientRealmsController extends AppController
{

	/**
	 * @method index
	 * @param $clientID
	 * This method is used to loads client realms list with pagination.
	 */
	public function index($clientID)
	{
		if (isset($clientID)) {
			// Fetching records with pagination.
			$this->paginate = array(
				'limit' => PAGINATION_LIMIT,
				'conditions' => array('ClientID' => $clientID)
			);
			$clientRealm = $this->paginate();
			$clientRealmsData =array();

			foreach ($clientRealm as $clientRealms) {
				// Get realms name via realms id.
				$realmsData= $this->ClientRealm->getRealmsById($clientRealms['ClientRealm']['RealmID']);

				if (isset($realmsData['Realm']['Name'])) {
					$clientRealms['ClientRealm']['realmName'] = $realmsData['Realm']['Name'];
				}
				$clientRealmsData[] = $clientRealms;
			}
			$clientRealms = $clientRealmsData;
			$this->set('clientRealms', $clientRealms);
			$this->set('clientID', $clientID);
		} else {
			$this->redirect('/client_realms/index');
		}
	}



	/**
	 * @method add
	 * @param $clientID
	 * This method is used to add client realms.
	 */
	public function add($clientID)
	{
		if (isset($clientID)) {
			$this->set('clientID', $clientID);
			// Fetch realms for select box controler.
			$clientRealms = $this->ClientRealm->selectRealms();

			// Adding realms name to final array.
			foreach ($clientRealms as $val) {
				$arr[$val['Realm']['ID']] = $val['Realm']['Name'];
			}
			$this->set('arr', $arr);

			// run only when submit button clicked.
			if ($this->request->is('post')) {
				$requestData = $this->ClientRealm->set($this->request->data);

				if ($this->ClientRealm->validates()) {
					if ($requestData) {
						$addData['ClientRealm']['ClientID'] = $clientID;
						$addData['ClientRealm']['RealmID'] = $requestData['ClientRealm']['Type'];
					}
					$this->ClientRealm->save($addData);
					$this->Session->setFlash(__('Client member is saved succefully!', true), 'flash_success');
				} else {
					$this->Session->setFlash(__('Client memberis not saved succefully!', true), 'flash_failure');
				}
			}
		}
	}



	/**
	 * @method remove
	 * @param $id
	 * @param $clientID
	 * This method is used to delete client realms.
	 */
	public function remove($id, $clientID)
	{
		if (isset($id)) {
			// Deleting then redirected to index function.
			if ($this->ClientRealm->delete($id)) {
				$this->redirect('/client_realms/index/'.$clientID);
				$this->Session->setFlash(__('Client realm is removed succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('Client realm is not removed succefully!', true), 'flash_failure');
			}
		} else {
			$this->redirect('/client_realms/index'.$clientID);
		}
	}
}

// vim: ts=4
