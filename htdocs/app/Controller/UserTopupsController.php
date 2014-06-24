<?php
/**
 * User topups
 *
 */
class UserTopupsController extends AppController {

	public $use = array('Users');
	/* index function
	 * @param $userId
	 * Used to show user topups list with pagination.
	 *
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
	 * Used to add user topups
	 *
	 */
	public function add($userId)
	{
		if (isset($userId))
		{
			$this->set('userId', $userId);
			// Checking button submission.
			if ($this->request->is('post'))
			{
				$this->UserTopup->set($this->request->data);
				// Validating input.
				if ($this->UserTopup->validates())
				{
					// Saving data.
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
	 * Used to edit user topups
	 *
	 */
	public function edit($id, $userId){
		// Loading topup data from user Id.
		$topups = $this->UserTopup->findById($id);
		$this->set('topup', $topups);
		$this->set('userId', $userId);
		// Checking submission.
		if ($this->request->is('post')){
			// Setting data to model.
			$this->UserTopup->set($this->request->data);
			// Validating data.
			if ($this->UserTopup->validates()) {
				// Saving edited data.
				$this->UserTopup->editRec($id, $this->request->data);
				$this->Session->setFlash(__('User topup is saved succefully!', true), 'flash_success');

				// For page reload to reflect new data.
				$topups = $this->UserTopup->findById($id);
				$this->set('topup', $topups);
			} else {
			    $this->Session->setFlash(__('User topup is not saved succefully!', true), 'flash_failure');
			}
		}
	}

	/* remove function
	 * @param $id, $userId
	 * Used to delete user topups.
	 *
	 */
	public function remove($id, $userId){
		if (isset($id)){
			// Deleting
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