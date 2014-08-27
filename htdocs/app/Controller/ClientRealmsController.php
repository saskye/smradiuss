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
 * @class ClientRealmsController
 *
 * @brief This class manages the client realms.
 */
class ClientRealmsController extends AppController
{
	/**
	 * @var $components
	 * This variable is used for include other conponents.
	 */
	var $components = array('Auth', 'Acl','Access');


	/**
	 * @var $helpers
	 * This variable is used for include other helper file.
	 */
	var $helpers = array('Access');


	/**
	 * @method beforeFilter
	 * This method executes method that we need to be executed before any other action.
	 */
	function beforeFilter()
	{
		parent::beforeFilter();
	}



	/**
	 * @method index
	 * This method is used to loads client realms list with pagination.
	 * @param $clientID
	 */
	public function index($clientID)
	{
		// Get user group name.
		$groupName = $this->Access->getGroupName($this->Session->read('User.ID'));
		$this->set('groupName', $groupName);
		// Check permission.
		$permission = $this->Access->checkPermission('ClientRealmsController', 'View', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
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
	 * This method is used to add client realms.
	 * @param $clientID
	 */
	public function add($clientID)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('ClientRealmsController', 'Add', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
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
					$this->Session->setFlash(__('Client member is saved successfully')."!", 'flash_success');
				} else {
					$this->Session->setFlash(__('Client member is not saved successfully')."!", 'flash_failure');
				}
			}
		}
	}



	/**
	 * @method remove
	 * This method is used to delete client realms.
	 * @param $id
	 * @param $clientID
	 */
	public function remove($id, $clientID)
	{
		// Check permission.
		$permission = $this->Access->checkPermission('ClientRealmsController', 'Delete', $this->Session->read('User.ID'));
		if (empty($permission)) {
			throw new UnauthorizedException();
		}
		if (isset($id)) {
			// Deleting then redirected to index function.
			if ($this->ClientRealm->delete($id)) {
				$this->redirect('/client_realms/index/'.$clientID);
				$this->Session->setFlash(__('Client realm is removed successfully')."!", 'flash_success');
			} else {
				$this->Session->setFlash(__('Client realm is not removed successfully')."!", 'flash_failure');
			}
		} else {
			$this->redirect('/client_realms/index'.$clientID);
		}
	}
}

// vim: ts=4
