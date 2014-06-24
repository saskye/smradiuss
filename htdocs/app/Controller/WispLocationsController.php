<?php
/*
 * wisp location
 */	
class WispLocationsController extends AppController
{
	/* index function 
	 * @param $LocationID
	 * Used to show all location with pagination.
	 *
	 */	
	public function index()
	{
		$this->WispLocation->recursive = -1;
		$this->paginate = array('limit' => PAGINATION_LIMIT );
		// Fetching and assigning to variable.
		$wispLocation = $this->paginate();
		$this->set('wispLocation', $wispLocation);
	}

	/* add function 
	 * Used to add locations.
	 * 
	 */	
	public function add()
	{
		// Checking submission.
		if ($this->request->is('post'))
		{	
			// Setting data to model.
			$this->WispLocation->set($this->request->data);
			// Validating submitted data.
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
	 * Used to edit locations
	 *
	 */	
	public function edit($id){
		// Finding location from id and assigning to variable.
		$location = $this->WispLocation->findById($id);
		$this->set('location', $location);
		// Checking submission.
		if ($this->request->is('post')){
			// Setting submitted data.
			$this->WispLocation->set($this->request->data);
			// Validating submitted data.
			if ($this->WispLocation->validates()) {
				$this->WispLocation->id = $id;
				// Saving data.
			    $this->WispLocation->save($this->request->data);
				$this->Session->setFlash(__('Wisp Location is edited succefully!', true), 'flash_success');
			} else {
			    $this->Session->setFlash(__('Wisp Location is not edited!', true), 'flash_failure');
			}
		}
	}

	/* remove function 
	 * @param $id
	 * used to delete locations.
	 *
	 */	
	public function remove($id){
		// Deleting
		if($this->WispLocation->delete($id)){
			// Redirecting to index.
			$this->redirect('/WispLocations/index');
			$this->Session->setFlash(__('Wisp Locations is removed succefully!', true), 'flash_success');
		} else {
			$this->Session->setFlash(__('Wisp Locations is not removed!', true), 'flash_failure');
		}
	}
}