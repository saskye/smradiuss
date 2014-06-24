<?php
/**
 * User Groups
 *
 */
class UserGroupsController extends AppController {

	public $use = array('Users');

	/* index function
	 * @param $userId
	 * Showing used group list with pagination.
	 *
	 */
	public function index($userId)
	{
		if (isset($userId))
		{
			$this->UserGroup->recursive = 0;
			$this->paginate = array(
                'limit' => PAGINATION_LIMIT,
				'conditions' => array('UserID' => $userId)
			);
			// Contains data with pagination.
			$UserGroups  = $this->paginate();
			$UserGroupData =array();
			// Adding group name to array.
			foreach($UserGroups as $userGroup)
			{
				$groupData= $this->UserGroup->getGroupById($userGroup['UserGroup']['GroupID']);

				if(isset($groupData[0]['groups']['Name']))
				{
					$userGroup['UserGroup']['group'] = $groupData[0]['groups']['Name'];
				}
				$UserGroupData[] = $userGroup;
			}
			$UserGroup = $UserGroupData;
			$this->set('UserGroup', $UserGroup);
			$this->set('userId', $userId);
		}
	}

	/* add function
	 * @param $userId
	 * Function to add user groups.
	 */
	public function add($userId)
	{
		if (isset($userId))
		{
			$this->set('userId', $userId);
			//Fetching  all groups.
			$arr = array();
			$groupItems = $this->UserGroup->selectGroup();
			foreach($groupItems as $val)
			{
				$arr[$val['groups']['ID']] = $val['groups']['Name'];
			}

			$this->set('arr', $arr);
			// Checking submission.
			if ($this->request->is('post'))
			{
				$this->UserGroup->set($this->request->data);
				// Validating submitted data.
				if ($this->UserGroup->validates())
				{
					// Saving user groups.
			    	$this->UserGroup->InsertRec($userId,$this->request->data);
					// Sending message to screen.
					$this->Session->setFlash(__('User Group is saved succefully!', true), 'flash_success');

				}
				else
				{
			    	$this->Session->setFlash(__('User Group is not saved succefully!', true), 'flash_failure');
				}
			}
		}
		else
		{

		}
	}

	/* remove function
	 * @param $id, $userId
	 * Used to delete user groups
	 */
	public function remove($id, $userId){
		if (isset($id)){
			// Deleting
			if($this->UserGroup->delete($id)){
				// Redirecting to index.
				$this->redirect('/user_groups/index/'.$userId);
				$this->Session->setFlash(__('User group is removed succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('User group is not removed succefully!', true), 'flash_failure');
			}
		} else {
			$this->redirect('/user_groups/index'.$userId);
		}
	}
}