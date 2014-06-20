<?php
/**
 * Realm Members
 *
 */
 
class RealmMembersController extends AppController {
	/* index function 
	 * @param $realmId
	 * Function to show reamls members list with pagination 
	 * 
	 */
	public function index($realmID){
		if (isset($realmID)){	
			// Getting list with pagination.		
			$this->paginate = array(
                'limit' => PAGINATION_LIMIT,
				'conditions' => array('RealmID' => $realmID)
			);
			$realmMembers = $this->paginate();
			$realmMembersData =array();
			
			// Generating final array.
			foreach($realmMembers as $realmMember)
			{
				$clientData = $this->RealmMember->getClientNameById($realmMember['RealmMember']['ClientID']);
				if(isset($clientData[0]['clients']['Name']))
				{
					$realmMember['RealmMember']['clientName'] = $clientData[0]['clients']['Name'];
				}
				$realmMembersData[] = $realmMember;
			}
			$realmMember = $realmMembersData;
			// Send to view page.
			$this->set('realmMember', $realmMember);
			$this->set('realmID', $realmID);
		} else {
			$this->redirect('/realm_members/index');
		}			
	}	

	/* remove function 
	 * @param $id, $realmId
	 * Function used to remove realms members.
	 * 
	 */
	public function remove($id, $realmID){
		if (isset($id)){
			if($this->RealmMember->delete($id)){
				$this->redirect('/realm_members/index/'.$realmID);
				$this->Session->setFlash(__('Realm member is removed succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('Realm member is not removed succefully!', true), 'flash_failure');
			}
		} else {
			$this->redirect('/realm_members/index'.$realmID);
		}
	}
}