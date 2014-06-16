<?php
/**
 * Client
 */
class ClientsController extends AppController
{
	/* index function 
	 * 
	 */
	public function index()
	{
		$this->Client->recursive = -1;
		$this->paginate = array('limit' => PAGINATION_LIMIT);
		$client = $this->paginate();
		$this->set('client', $client);
	}
	
	/* add function 
	 * 
	 */
	public function add(){
		if ($this->request->is('post')){
			$this->Client->set($this->request->data);
			if ($this->Client->validates()) {
			    $this->Client->save($this->request->data);
				$this->Session->setFlash(__('Client is saved succefully!', true), 'flash_success');
			} else {
			    $this->Session->setFlash(__('Client is not saved succefully!', true), 'flash_failure');
			}
		}	
	}
	
	/* edit function 
	 * @param $id 
	 */
	public function edit($id){
		$client = $this->Client->findById($id);
		$this->set('client', $client);
		if ($this->request->is('post')){
			$this->Client->set($this->request->data);
			if ($this->Client->validates()) {
				$this->Client->id = $id;
			    $this->Client->save($this->request->data);
				$this->Session->setFlash(__('Client is edited succefully!', true), 'flash_success');
			} else {
			    $this->Session->setFlash(__('Client is not edited succefully!', true), 'flash_failure');
			}
		}
	}
	
	/* delete function 
	 * @param $id
	 */
	public function remove($id){
		if($this->Client->delete($id)){
			$this->redirect('/clients/index');
			$this->Session->setFlash(__('Client is removed succefully!', true), 'flash_success');
		} else {
			$this->Session->setFlash(__('Client is not removed succefully!', true), 'flash_failure');
		}
	}
}