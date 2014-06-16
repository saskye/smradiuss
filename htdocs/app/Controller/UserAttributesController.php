<?php
/**
 * User Attribute
 *
 */
class UserAttributesController extends AppController {
	
	/* index function 
	 * @param $userId
	 */		
	public function index($userId){
		if (isset($userId)){
			$this->UserAttribute->recursive = 0;
			$this->paginate = array(
                'limit' => PAGINATION_LIMIT,
				'conditions' => array('UserAttribute.UserID' => $userId)
				 );
			$userAttributes  = $this->paginate();
			$this->set('userAttributes', $userAttributes);
			$this->set('userId', $userId);
		} else {
			$this->redirect('/users/index');
		}			
	}	
	
	/* add function 
	 * @param $userId
	 */		
	public function add($userId){
		$this->set('userId', $userId);
		if ($this->request->is('post')){
				$this->request->data['UserAttribute']['Disabled'] = intval($this->request->data['UserAttribute']['Disabled']);
				$this->request->data['UserAttribute']['UserID'] = intval($this->request->params['pass'][0]);
				$this->UserAttribute->set($this->request->data);
				if ($this->UserAttribute->validates()) {
			  	  $this->UserAttribute->save($this->request->data);
					$this->Session->setFlash(__('User attribute is saved succefully!', true), 'flash_success');
				} else {
			  	  $this->Session->setFlash(__('User attribute is not saved succefully!', true), 'flash_failure');
				}
		}	
	}
	
	/* edit function 
	 * @param $id, $userId
	 */		
	public function edit($id, $userId){
		$userAttribute = $this->UserAttribute->findById($id);
		$this->set('userAttribute', $userAttribute);
		if ($this->request->is('post')){
			$this->request->data['UserAttribute']['Disabled'] = intval($this->request->data['UserAttribute']['Disabled']);
			$this->UserAttribute->set($this->request->data);
			if ($this->UserAttribute->validates()) {
				$this->UserAttribute->id = $id;
			    $this->UserAttribute->save($this->request->data);
				$this->Session->setFlash(__('Attribute is saved succefully!', true), 'flash_success');
			} else {
			    $this->Session->setFlash(__('Attribute is not saved succefully!', true), 'flash_failure');
			}
		}
	}
	/* delete function 
	 * @param $id, $userId
	 */		
	public function remove($id, $userId){
		if (isset($id)){
			if($this->UserAttribute->delete($id)){
				$this->redirect('/user_attributes/index/'.$userId);
				$this->Session->setFlash(__('User is removed succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('User is not removed succefully!', true), 'flash_failure');
			}
		} else {
			$this->redirect('/user_attributes/index'.$userId);
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