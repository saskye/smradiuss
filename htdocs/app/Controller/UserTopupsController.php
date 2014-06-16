<?php
/**
 * User topups
 *
 */
class UserTopupsController extends AppController {

	public $use = array('Users');
	/* index function 
	 * @param $userId
	 */	
	public function index($userId)
	{
		if (isset($userId)){
			$this->UserTopup->recursive = 0;
			$this->paginate = array(
                'limit' => PAGINATION_LIMIT,
				'conditions' => array('UserID' => $userId)
				 );
			$topups  = $this->paginate();
			$this->set('topups', $topups);
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
				$this->UserTopup->set($this->request->data);
				if ($this->UserTopup->validates()) 
				{
			    	$this->UserTopup->InsertRec($userId,$this->request->data);
					$this->Session->setFlash(__('User topup is saved succefully!', true), 'flash_success');
					
				} 
				else 
				{
			    	$this->Session->setFlash(__('User topup is not saved succefully!', true), 'flash_failure');
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
		$topups = $this->UserTopup->findById($id);
		$this->set('topup', $topups);
		$this->set('userId', $userId);
		if ($this->request->is('post')){
			
			$this->UserTopup->set($this->request->data);
			if ($this->UserTopup->validates()) {

				$this->UserTopup->editRec($id, $this->request->data);
				$this->Session->setFlash(__('User topup is saved succefully!', true), 'flash_success');

				// For page reload to reflect data.
				$topups = $this->UserTopup->findById($id);
				$this->set('topup', $topups);
			} else {
			    $this->Session->setFlash(__('User topup is not saved succefully!', true), 'flash_failure');
			}
		}
	}
	
	/* delete function 
	 * @param $id, $userId
	 */	
	public function remove($id, $userId){
		if (isset($id)){
			if($this->UserTopup->delete($id)){
				$this->redirect('/user_topups/index/'.$userId);
				$this->Session->setFlash(__('User topup is removed succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('User topup is not removed succefully!', true), 'flash_failure');
			}
		} else {
			$this->redirect('/user_topups/index'.$userId);
		}
	}
}