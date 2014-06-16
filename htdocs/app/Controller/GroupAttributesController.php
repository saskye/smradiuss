<?php
/**
 * Group Attribute
 */
class  GroupAttributesController extends AppController {

	/* index function 
	 * @param  $groupId
	 */
	public function index($groupId){
		if (isset($groupId)){			
			$this->paginate = array(
			'limit' => PAGINATION_LIMIT,
			'conditions' => array('GroupAttribute.GroupID' => $groupId)
			);
			$groupAttributes = $this->paginate();
			
			$this->set('groupAttributes', $groupAttributes);
			$this->set('groupId', $groupId);
		} else {
			$this->redirect('/users/index');
		}			
	}	
	
	/* add function 
	 * @param $groupId
	 */
	public function add($groupId){
		$this->set('groupId', $groupId);
		if ($this->request->is('post')){
			$this->request->data['GroupAttribute']['Disabled'] = intval($this->request->data['GroupAttribute']['Disabled']);
			$this->request->data['GroupAttribute']['GroupID'] = intval($this->request->params['pass'][0]);
			$this->GroupAttribute->set($this->request->data);
			if ($this->GroupAttribute->validates()) {
				$this->GroupAttribute->save($this->request->data);
				$this->Session->setFlash(__('Group attribute is saved succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('Group attribute is not saved succefully!', true), 'flash_failure');
			}
		}	
	}
	
	/* edit function 
	 * @param $id, $groupId
	 */
	public function edit($id, $groupId){
		$groupAttribute = $this->GroupAttribute->findById($id);
		$this->set('groupAttribute', $groupAttribute);
		if ($this->request->is('post')){
			$this->request->data['GroupAttribute']['Disabled'] = intval($this->request->data['GroupAttribute']['Disabled']);
			$this->GroupAttribute->set($this->request->data);
			if ($this->GroupAttribute->validates()) {
				$this->GroupAttribute->id = $id;
				$this->GroupAttribute->save($this->request->data);
				$this->Session->setFlash(__('Attribute is saved succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('Attribute is not saved succefully!', true), 'flash_failure');
			}
		}
	}
	
	/* delete function 
	 * @param $id, $groupId
	 */
	public function remove($id, $groupId){
		if (isset($id)){
			if($this->GroupAttribute->delete($id)){
				$this->redirect('/group_attributes/index/'.$groupId);
				$this->Session->setFlash(__('Attribute is removed succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('Attribute is not removed succefully!', true), 'flash_failure');
			}
		} else {
		$this->redirect('/group_attributes/index'.$userId);
		}
	}
	
	/* read attributes function 
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