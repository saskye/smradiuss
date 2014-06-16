<?php
/**
 * Groups
 */
class GroupsController extends AppController {
	
	/* index function 
	 *
	 */
	public function index(){
		$this->Group->recursive = -1;
		//$groups = $this->Group->find('all');
		$this->paginate = array('limit' => PAGINATION_LIMIT );
		$groups = $this->paginate();
		$this->set('groups', $groups);
	}
	
	/* add function 
	 * 
	 */
	public function add(){
		if ($this->request->is('post')){
			$this->Group->set($this->request->data);
			if ($this->Group->validates()) {
			    $this->Group->save($this->request->data);
				$this->Session->setFlash(__('Group is saved succefully!', true), 'flash_success');
			} else {
			    $this->Session->setFlash(__('Group is not saved succefully!', true), 'flash_failure');
			}
		}	
	}
	
	/* edit function 
	 * @param $id
	 */
	public function edit($id){
		$group = $this->Group->findById($id);
		$this->set('group', $group);
		if ($this->request->is('post')){
			$this->Group->set($this->request->data);
			if ($this->Group->validates()) {
				$this->Group->id = $id;
				$this->Group->save($this->request->data);
				$this->Session->setFlash(__('Group is edited succefully!', true), 'flash_success');
				// For reload page to reflect change in data
				$group = $this->Group->findById($id);
				$this->set('group', $group);
			} else {
				$this->Session->setFlash(__('Group is not edited succefully!', true), 'flash_failure');
			}
		}
	}
	
	/* delete function 
	 * @param $id
	 */
	public function remove($id){
		if($this->Group->delete($id)){
			$this->redirect('/groups/index');
			$this->Session->setFlash(__('Group is removed succefully!', true), 'flash_success');
		} else {
			$this->Session->setFlash(__('Group is not removed succefully!', true), 'flash_failure');
		}
	}
}