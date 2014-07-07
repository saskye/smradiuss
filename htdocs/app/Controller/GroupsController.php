<?php
/**
 * Groups
 *
 * @class GroupsController
 *
 * @brief This class manage the groups.
 */
class GroupsController extends AppController {

	/**
	 * @method index
	 * This method is used for Showing group list with pagination.
	 */
	public function index(){
		$this->Group->recursive = -1;
		$this->paginate = array('limit' => PAGINATION_LIMIT );
		$groups = $this->paginate();
		$this->set('groups', $groups);
	}

	/**
	 * @method add
	 * This method is used to add groups.
	 */
	public function add(){
		if ($this->request->is('post')){
			$this->Group->set($this->request->data);
			// Validating entered data.
			if ($this->Group->validates()) {
				// Saving data.
			    $this->Group->save($this->request->data);
				$this->Session->setFlash(__('Group is saved succefully!', true), 'flash_success');
			} else {
			    $this->Session->setFlash(__('Group is not saved succefully!', true), 'flash_failure');
			}
		}
	}

	/**
	 * @method edit
	 * @param $id
	 * This method is used to edit groups.
	 */
	public function edit($id){
		$group = $this->Group->findById($id);
		$this->set('group', $group);
		// Checking submit button is clicked or not
		if ($this->request->is('post')){
			$this->Group->set($this->request->data);
			// Validating submitted data.
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

	/**
	 * @method remove
	 * @param $id
	 * This method is used to delete group.
	 */
	public function remove($id){
		// Deleting
		if($this->Group->delete($id)){
			// Redirected to index function.
			$this->redirect('/groups/index');
			$this->Session->setFlash(__('Group is removed succefully!', true), 'flash_success');
		} else {
			$this->Session->setFlash(__('Group is not removed succefully!', true), 'flash_failure');
		}
	}
}

// vim: ts=4
