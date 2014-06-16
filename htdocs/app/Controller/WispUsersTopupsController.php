<?php
/**
 * Wisp Users Topup
 *
 */
class WispUsersTopupsController extends AppController
{
	/* index function 
	 * @param $userId
	 */	
	public function index($userId)
	{
		if (isset($userId))
		{
			$this->WispUsersTopup->recursive = 0;
			$this->paginate = array(
                'limit' => PAGINATION_LIMIT,
				'conditions' => array('UserID' => $userId)
			);
			$wtopups  = $this->paginate();
			//echo "<pre>";print_r($wtopups);exit;
			$this->set('wtopups', $wtopups);
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
			if ($this->request->is('post'))
			{
				$this->WispUsersTopup->set($this->request->data);
				if ($this->WispUsersTopup->validates()) 
				{
			    	$this->WispUsersTopup->InsertRec($userId,$this->request->data);
					$this->Session->setFlash(__('Wisp user topup is saved succefully!', true), 'flash_success');
					
				} 
				else 
				{
			    	$this->Session->setFlash(__('Wisp user topup is not saved!', true), 'flash_failure');
				}
			}
		}
		else
		{
			
		}
	}
	
	/* edit function 
	 * @param $id, $userId
	 */	
	public function edit($id, $userId){
		$topups = $this->WispUsersTopup->findById($id);
		$this->set('topup', $topups);
		$this->set('userId', $userId);
		if ($this->request->is('post')){
			
			$this->WispUsersTopup->set($this->request->data);
			if ($this->WispUsersTopup->validates()) {

				$this->WispUsersTopup->editRec($id, $this->request->data);
				$this->Session->setFlash(__('Wisp user topup is edit succefully!', true), 'flash_success');

				// For page reload to reflect data.
				$topups = $this->WispUsersTopup->findById($id);
				$this->set('topup', $topups);
			} else {
			    $this->Session->setFlash(__('Wisp user topup is not edit!', true), 'flash_failure');
			}
		}
	}
	
	/* delete function 
	 * @param $id, $userId
	 */	
	public function remove($id, $userId){
		if (isset($id)){
			if($this->WispUsersTopup->delete($id)){
				$this->redirect('/wispUsers_topups/index/'.$userId);
				$this->Session->setFlash(__('User topup is removed succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('User topup is not removed succefully!', true), 'flash_failure');
			}
		} else {
			$this->redirect('/wispUsers_topups/index/'.$userId);
		}
	}
}