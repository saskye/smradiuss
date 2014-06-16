<?php
class UsersController extends AppController {
	
	public $use = array('UserAttribute');

	/* index function 
	 * 
	 */			
	public function index(){
		//
		$this->User->recursive = -1;
		$this->paginate = array('limit' => PAGINATION_LIMIT );
		$users = $this->paginate();
		$this->set('users', $users);
		}	
	
	/* add function 
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
	 */		
	public function edit($id){
		$user = $this->User->findById($id);
		$this->set('user', $user);
		if ($this->request->is('post')){
			$this->request->data['User']['Disabled'] = intval($this->request->data['User']['Disabled']);
			$this->User->set($this->request->data);
			if ($this->User->validates()) {
				$this->User->id = $id;
			   $this->User->save($this->request->data);
				$this->Session->setFlash(__('User is edited succefully!', true), 'flash_success');
				// Code to update text field with new data.		
				$user = $this->User->findById($id);
				$this->set('user', $user);
			} else {
			    $this->Session->setFlash(__('User is not edited succefully!', true), 'flash_failure');
			}
		}
	}
	
	
	/* delete function 
	 * @param $id
	 */	
	public function remove($id){
		if($this->User->delete($id)){
			$this->User->deleteGroup($id);
			$this->redirect('/users/index');
			$this->Session->setFlash(__('User is removed succefully!', true), 'flash_success');
		} else {
			$this->Session->setFlash(__('User is not removed succefully!', true), 'flash_failure');
		}
	}
	
	/* attribute function 
	 * @param $id
	 */	
	public function attribute($id){
		if ($this->request->is('post')){			
			$this->request->data['UserAttribute'] = $this->request->data['User'];
			$this->UserAttribute->set($this->request->data);
			if ($this->UserAttribute->validates()) {
			    $this->UserAttribute->save($this->request->data);
				$this->Session->setFlash(__('User attribute is saved succefully!', true), 'flash_success');
			} else {
			    $this->Session->setFlash(__('User attribute is not saved succefully!', true), 'flash_failure');
			}
		}
	}
	
	
}