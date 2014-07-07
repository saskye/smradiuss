<?php
/**
 * Realms
 *
 * @class RealmsController
 *
 * @brief This class manage the realms.
 */
class RealmsController extends AppController
{
	/**
	 * @method index
	 * This method is used to show realms list with pagination.
	 */
	public function index()
	{
		$this->Realm->recursive = -1;
		$this->paginate = array('limit' => PAGINATION_LIMIT);
		$realm = $this->paginate();
		$this->set('realm', $realm);
	}

	/**
	 * @method add
	 * This method is used to add realms.
	 */
	public function add(){
		if ($this->request->is('post')){
			$this->Realm->set($this->request->data);
			// Validating enterd data.
			if ($this->Realm->validates()) {
			    $this->Realm->save($this->request->data);
				$this->Session->setFlash(__('Realm is saved succefully!', true), 'flash_success');
			} else {
			    $this->Session->setFlash(__('Realm is not saved succefully!', true), 'flash_failure');
			}
		}
	}

	/**
	 * @method edit
	 * @param $id
	 * This method is used to edit realms.
	 */
	public function edit($id){
		// Fetch record and set to variable.
		$realm = $this->Realm->findById($id);
		$this->set('realm', $realm);
		// Checking submission.
		if ($this->request->is('post')){
			// Setting submitted data.
			$this->Realm->set($this->request->data);
			// Validating submitted data.
			if ($this->Realm->validates()) {
				$this->Realm->id = $id;
				// Saving
			    $this->Realm->save($this->request->data);
				$this->Session->setFlash(__('Realm is edited succefully!', true), 'flash_success');
			} else {
			    $this->Session->setFlash(__('Realm is not edited succefully!', true), 'flash_failure');
			}
		}
	}

	/**
	 * @method remove
	 * @param $id
	 * This method is used to delete realms.
	 */
	public function remove($id){
		// Deleting & check done or not.
		if($this->Realm->delete($id)){
			// Redirecting to index.
			$this->redirect('/realms/index');
			$this->Session->setFlash(__('Realm is removed succefully!', true), 'flash_success');
		} else {
			$this->Session->setFlash(__('Realm is not removed succefully!', true), 'flash_failure');
		}
	}
}

// vim: ts=4
