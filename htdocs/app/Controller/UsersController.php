<?php
class UsersController extends AppController {

	public $use = array('UserAttribute');

	/* index function 
	 * Showing users list with pagination.
	 *
	 */			
	public function index(){
		$this->User->recursive = -1;
		$this->paginate = array('limit' => PAGINATION_LIMIT );
		$users = $this->paginate();
		$this->set('users', $users);
	}	

	/* add function 
	 * Adding users data.
	 *
	 */	

	public function add(){
		if ($this->request->is('post')){
			$this->request->data['User']['Disabled'] = intval($this->request->data['User']['Disabled']);
			$this->User->set($this->request->data);
			if ($this->User->validates()) {
			    $this->User->save($this->request->data);
				$this->Session->setFlash(__('User is saved succefully!', true), 'flash_success');
			} else {
			    $this->Session->setFlash(__('User is not saved succefully!', true), 'flash_failure');
			}
		}	
	}

	/* edit function 
	 * @param $id
	 * Editing users data.
	 */		
	public function edit($id){
		// Finding users data and assigning to var $user.
		$user = $this->User->findById($id);
		$this->set('user', $user);
		// Checking button is clicked or not.
		if ($this->request->is('post')){
			$this->request->data['User']['Disabled'] = intval($this->request->data['User']['Disabled']);
			$this->User->set($this->request->data);
			// Validating submitted data.
			if ($this->User->validates()) {
				$this->User->id = $id;
				// Save to database.				
				$this->User->save($this->request->data);
				$this->Session->setFlash(__('User is edited succefully!', true), 'flash_success');
				// To load page with new data saved above.
				$user = $this->User->findById($id);
				$this->set('user', $user);
			} else {
			    $this->Session->setFlash(__('User is not edited succefully!', true), 'flash_failure');
			}
		}
	}

	/* remove function 
	 * @param $id
	 * Function used to delete users data.
	 *
	 */	
	public function remove($id){
		// Deleting & checking done or not.
		if($this->User->delete($id)){
			// Deleting user reference data from other db tables.
			$this->User->deleteUserRef($id);
			// Redirecting users to index function.
			$this->redirect('/users/index');
			$this->Session->setFlash(__('User is removed succefully!', true), 'flash_success');
		} else {
			$this->Session->setFlash(__('User is not removed succefully!', true), 'flash_failure');
		}
	}
}