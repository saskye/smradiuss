<?php
/**
 * Groups Member
 *
 */
class GroupMemberController extends AppController
{
	/* included users table
	 *
	 */
	public $use = array('Users');

	/* index function
	 * @param $groupID
	 * Functon loads list of group members with pagination
	 *
	 */
	public function index($groupID)
	{
		if (isset($groupID))
		{
			$this->GroupMember->recursive = 0;
			$this->paginate = array(
				'limit' => PAGINATION_LIMIT,
				'conditions' => array('GroupID' => $groupID)
			);

			$GroupMember = $this->paginate();
			$UserNameData =array();

			// Preparing final array.
			foreach($GroupMember as $groupMember)
			{
				$userName = $this->GroupMember->getUserNameById($groupMember['GroupMember']['UserID']);
				if(isset($userName[0]['users']['Username']))
				{
					$groupMember['GroupMember']['UserName'] = $userName[0]['users']['Username'];
				}
				$UserNameData[] = $groupMember;
			}

			$GroupMember = $UserNameData;
			$this->set('GroupMember', $GroupMember);
			$this->set('groupID', $groupID);
		}
	}

	/* remove function
	 * @param $id, $groupID
	 * Function used to delete group member.

	 */
	public function remove($id, $groupID){
		if (isset($id)){
			if($this->GroupMember->delete($id)){
				$this->redirect('/group_member/index/'.$groupID);
				$this->Session->setFlash(__('Removed from this group succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('Not removed from this group!', true), 'flash_failure');
			}
		} else {
			$this->redirect('/group_member/index'.$groupID);
		}
	}
}