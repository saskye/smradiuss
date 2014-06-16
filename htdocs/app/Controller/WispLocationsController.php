<?php
	/*
	 * wisp location
	 */	
class WispLocationsController extends AppController
{
	/* index function 
	 * 
	 */	
	public function index()
	{
		$this->WispLocation->recursive = -1;
		$this->paginate = array('limit' => PAGINATION_LIMIT );
		$wispLocation = $this->paginate();
		$this->set('wispLocation', $wispLocation);
	}
	
	/* add function 
	 * 
	 */	
	public function add()
	{
		if ($this->request->is('post'))
		{
			$this->WispLocation->set($this->request->data);
			if ($this->WispLocation->validates()) {
			    $this->WispLocation->save($this->request->data);
				$this->Session->setFlash(__('Wisp Location is saved succefully!', true), 'flash_success');
			} else {
			    $this->Session->setFlash(__('Wisp Location is not saved!', true), 'flash_failure');
			}
		}
	}
	
	/* edit function 
	 * @param $id
	 */	
	public function edit($id){
		$location = $this->WispLocation->findById($id);
		$this->set('location', $location);
		if ($this->request->is('post')){
			$this->WispLocation->set($this->request->data);
			if ($this->WispLocation->validates()) {
				$this->WispLocation->id = $id;
			    $this->WispLocation->save($this->request->data);
				$this->Session->setFlash(__('Wisp Location is edited succefully!', true), 'flash_success');
			} else {
			    $this->Session->setFlash(__('Wisp Location is not edited!', true), 'flash_failure');
			}
		}
	}
	
	/* delete function 
	 * @param $id
	 */	
	public function remove($id){
		if($this->WispLocation->delete($id)){
			$this->redirect('/WispLocations/index');
			$this->Session->setFlash(__('Wisp Locations is removed succefully!', true), 'flash_success');
		} else {
			$this->Session->setFlash(__('Wisp Locations is not removed!', true), 'flash_failure');
		}
	}
}