<?php
/**
 * Realms
 *
 */
class RealmsController extends AppController
{
	/* index function 
	 * 
	 */
	public function index()
	{
		$this->Realm->recursive = -1;
		$this->paginate = array('limit' => PAGINATION_LIMIT);
		$realm = $this->paginate();
		$this->set('realm', $realm);
	}
	
	/* add function 
	 * 
	 */
	public function add(){
		if ($this->request->is('post')){
			$this->Realm->set($this->request->data);
			if ($this->Realm->validates()) {
			    $this->Realm->save($this->request->data);
				$this->Session->setFlash(__('Realm is saved succefully!', true), 'flash_success');
			} else {
			    $this->Session->setFlash(__('Realm is not saved succefully!', true), 'flash_failure');
			}
		}	
	}
	
	/* edit function 
	 * @param $id
	 */
	public function edit($id){
		$realm = $this->Realm->findById($id);
		$this->set('realm', $realm);
		if ($this->request->is('post')){
			$this->Realm->set($this->request->data);
			if ($this->Realm->validates()) {
				$this->Realm->id = $id;
			    $this->Realm->save($this->request->data);
				$this->Session->setFlash(__('Realm is edited succefully!', true), 'flash_success');
			} else {
			    $this->Session->setFlash(__('Realm is not edited succefully!', true), 'flash_failure');
			}
		}
	}
	
	/* delete function 
	 * @param $id
	 */
	public function remove($id){
		if($this->Realm->delete($id)){
			$this->redirect('/realms/index');
			$this->Session->setFlash(__('Realm is removed succefully!', true), 'flash_success');
		} else {
			$this->Session->setFlash(__('Realm is not removed succefully!', true), 'flash_failure');
		}
	}
}