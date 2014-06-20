<?php
/* 
 * wisp location member
 */	
class WispLocationMembersController extends AppController
{
	/* index function 
	 * @param $LocationID
	 * Used to show members location with pagination.
	 *
	 */	
	public function index($LocationID)
	{
		$this->WispLocationMember->recursive = -1;
		$this->paginate = array('limit' => PAGINATION_LIMIT,'conditions' => array('LocationID' => $LocationID));
		// Assigning fetched data to variable.
		$wispLocationMember = $this->paginate();
		$memberData = array();
		
		// Generating final array.
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
	
	/* remove function 
	 * @param $id, $LocationID
	 * used to delete members locations.
	 *
	 */		
	public function remove($id, $LocationID)
	{
		// Deleting 
		$deleteMember = $this->WispLocationMember->deleteMembers($id);
		// Redirecting to index.
		$this->redirect('/WispLocation_Members/index/'.$LocationID);
		$this->Session->setFlash(__('Wisp locations member is removed succefully!', true), 'flash_success');
	}
}