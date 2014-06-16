<?php
/**
 * User Groups
 *
 */
class UserGroupsController extends AppController {

	public $use = array('Users');
	
	/* index function 
	 * @param $userId
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
			 
			$UserGroups  = $this->paginate();
			$UserGroupData =array();

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
	 */	
	public function add($userId)
	{
		if (isset($userId))
		{
			$this->set('userId', $userId);
			$groupItems = $this->UserGroup->selectGroup();
			foreach($groupItems as $val)
			{
				$arr[$val['groups']['ID']] = $val['groups']['Name'];
			}
			
			
			$this->set('arr', $arr);
			if ($this->request->is('post'))
			{
				$this->UserGroup->set($this->request->data);
				if ($this->UserGroup->validates()) 
				{
			    	$this->UserGroup->InsertRec($userId,$this->request->data);
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
	 */	
	public function remove($id, $userId){
		if (isset($id)){
			if($this->UserGroup->delete($id)){
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