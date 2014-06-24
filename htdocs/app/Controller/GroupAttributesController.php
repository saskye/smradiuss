<?php
/**
 * Group Attribute
 */
class  GroupAttributesController extends AppController {

	/* index function
	 * @param  $groupId
	 * Functon loads list of group attributes with pagination
	 *
	 */
	public function index($groupId){
		if (isset($groupId)){
			// Fetching data with pagination.
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
	 * Function used to add group attributes.
	 *
	 */
	public function add($groupId){
		$this->set('groupId', $groupId);
		if ($this->request->is('post')){
			$this->request->data['GroupAttribute']['Disabled'] = intval($this->request->data['GroupAttribute']['Disabled']);
			$this->request->data['GroupAttribute']['GroupID'] = intval($this->request->params['pass'][0]);
			$this->GroupAttribute->set($this->request->data);
			// Validating entered data.
			if ($this->GroupAttribute->validates()) {
				// Saving data to table.
				$this->GroupAttribute->save($this->request->data);
				$this->Session->setFlash(__('Group attribute is saved succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('Group attribute is not saved succefully!', true), 'flash_failure');
			}
		}
	}

	/* edit function
	 * @param $id, $groupId
	 * Function used to edit group attributes.
	 *
	 */
	public function edit($id, $groupId){
		// Assigning group attribues values find by id to var.
		$groupAttribute = $this->GroupAttribute->findById($id);
		$this->set('groupAttribute', $groupAttribute);
		if ($this->request->is('post')){
			$this->request->data['GroupAttribute']['Disabled'] = intval($this->request->data['GroupAttribute']['Disabled']);
			$this->GroupAttribute->set($this->request->data);
			if ($this->GroupAttribute->validates()) {
				$this->GroupAttribute->id = $id;
				//Saving data to the table.
				$this->GroupAttribute->save($this->request->data);
				$this->Session->setFlash(__('Attribute is saved succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('Attribute is not saved succefully!', true), 'flash_failure');
			}
		}
	}

	/* remove function
	 * @param $id, $groupId
	 * Function used to delete group attributes.
	 *
	 */
	public function remove($id, $groupId){
		if (isset($id)){
			// Deleting then redirecting to index function.
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

}

// vim: ts=4
