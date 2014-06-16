<?php
/**
 * Client Realms
 * 
 */
 
class ClientRealmsController extends AppController {
	public function index($clientID){
		if (isset($clientID)){			
			$this->paginate = array(
			'limit' => PAGINATION_LIMIT,
			'conditions' => array('ClientID' => $clientID)
			);
			$clientRealm = $this->paginate();
			$clientRealmsData =array();
			
			foreach($clientRealm as $clientRealms)
			{
				$groupData= $this->ClientRealm->getGroupById($clientRealms['ClientRealm']['RealmID']);
				if(isset($groupData[0]['realms']['Name']))
				{
				$clientRealms['ClientRealm']['realmName'] = $groupData[0]['realms']['Name'];
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
	
	public function add($clientID){
		if (isset($clientID))
		{
			$this->set('clientID', $clientID);
			$clientRealms = $this->ClientRealm->selectGroup();
			foreach($clientRealms as $val)
			{
				$arr[$val['realms']['ID']] = $val['realms']['Name'];
			}
			
			$this->set('arr', $arr);
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
	
	public function remove($id, $clientID){
		if (isset($id)){
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