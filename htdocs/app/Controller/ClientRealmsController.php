<?php
/**
 * Client Realms
 *
 * @class ClientRealmsController
 *
 * @brief This class manage the client realms.
 */

class ClientRealmsController extends AppController {

	/**
	 * @method index
	 * @param $clientID
	 * This method is used to loads client realms list with pagination.
	 */
	public function index($clientID){
		if (isset($clientID)){
			// Fetching records with pagination.
			$this->paginate = array(
			'limit' => PAGINATION_LIMIT,
			'conditions' => array('ClientID' => $clientID)
			);
			$clientRealm = $this->paginate();
			$clientRealmsData =array();

			foreach($clientRealm as $clientRealms)
			{
				// Get realms name via realms id.
				$realmsData= $this->ClientRealm->getRealmsById($clientRealms['ClientRealm']['RealmID']);
				if(isset($realmsData[0]['realms']['Name']))
				{
				$clientRealms['ClientRealm']['realmName'] = $realmsData[0]['realms']['Name'];
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
	public function add($clientID){
		if (isset($clientID))
		{
			$this->set('clientID', $clientID);
			// Fetch realms for select box controler.
			$clientRealms = $this->ClientRealm->selectRealms();
			// Adding realms name to final array.
			foreach($clientRealms as $val)
			{
				$arr[$val['realms']['ID']] = $val['realms']['Name'];
			}
			$this->set('arr', $arr);
			// run only when submit button clicked.
			if ($this->request->is('post'))
			{
				$this->ClientRealm->set($this->request->data);
				if ($this->ClientRealm->validates())
				{
					$this->ClientRealm->InsertRec($clientID,$this->request->data);
					$this->Session->setFlash(__('Client member is saved succefully!', true), 'flash_success');
				}
				else
				{
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
	public function remove($id, $clientID){
		if (isset($id)){
			// Deleting then redirected to index function.
			if($this->ClientRealm->delete($id)){
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
