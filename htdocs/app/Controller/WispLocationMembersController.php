<?php
/* 
 * wisp location member
 */	
class WispLocationMembersController extends AppController
{
	/* index function 
	 * @param $LocationID
	 */	
	public function index($LocationID)
	{
		//echo "<pre>";print_r($LocationID);exit;
		$this->WispLocationMember->recursive = -1;
		$this->paginate = array('limit' => PAGINATION_LIMIT,
								'conditions' => array('LocationID' => $LocationID));
		$wispLocationMember = $this->paginate();
		$memberData = array();
		
		foreach($wispLocationMember as $wMember)
		{
			$userNameData= $this->WispLocationMember->selectUsername($wMember['WispLocationMember']['UserID']);
			foreach($userNameData as $uData)
			{
				if(isset($uData['users']['Username']))
				{
					$wMember['WispLocationMember']['userName'] = $userNameData[0]['users']['Username'];
				}
				$memberData[] = $wMember;
			}
		}
		$wispLocationMember = $memberData;
		$this->set('wispLocationMember', $wispLocationMember);
		$this->set('LocationID', $LocationID);
	}
	/* delete function 
	 * @param $id, $LocationID
	 */		
	public function remove($id, $LocationID)
	{
		$deleteMember = $this->WispLocationMember->deleteMembers($id);
		$this->redirect('/WispLocation_Members/index/'.$LocationID);
		$this->Session->setFlash(__('Wisp locations member is removed succefully!', true), 'flash_success');
	}
}