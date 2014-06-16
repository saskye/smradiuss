<?php
/**
 * Realm Attributes
 *
 */
 
class RealmAttributesController extends AppController {
	/* index function 
	 * @param $realmId
	 */
	public function index($realmId){
		if (isset($realmId)){			
			$this->paginate = array(
                'limit' => PAGINATION_LIMIT,
				'conditions' => array('RealmAttribute.RealmID' => $realmId)
			);
			$realmAttributes = $this->paginate();
			
			$this->set('realmAttributes', $realmAttributes);
			$this->set('realmId', $realmId);
		} else {
			$this->redirect('/realm_attributes/index');
		}			
	}	
	
	/* edit function 
	 * @param $realmId
	 */
	public function add($realmId){
		$this->set('realmId', $realmId);
		if ($this->request->is('post')){
			$this->request->data['RealmAttribute']['Disabled'] = intval($this->request->data['RealmAttribute']['Disabled']);
			$this->request->data['RealmAttribute']['RealmID'] = intval($this->request->params['pass'][0]);
			$this->RealmAttribute->set($this->request->data);
			if ($this->RealmAttribute->validates()) {
			    $this->RealmAttribute->save($this->request->data);
				$this->Session->setFlash(__('Realm attribute is saved succefully!', true), 'flash_success');
			} else {
			    $this->Session->setFlash(__('Realm attribute is not saved succefully!', true), 'flash_failure');
			}
		}	
	}
	
	/* edit function 
	 * @param $id , $realmId
	 */
	public function edit($id, $realmId){
		$realmAttribute = $this->RealmAttribute->findById($id);
		$this->set('realmAttribute', $realmAttribute);
		if ($this->request->is('post')){
			$this->request->data['RealmAttribute']['Disabled'] = intval($this->request->data['RealmAttribute']['Disabled']);
			$this->RealmAttribute->set($this->request->data);
			if ($this->RealmAttribute->validates()) {
				$this->RealmAttribute->id = $id;
			    $this->RealmAttribute->save($this->request->data);
				$this->Session->setFlash(__('Realm attribute is saved succefully!', true), 'flash_success');
			} else {
			    $this->Session->setFlash(__('Realm attribute is not saved succefully!', true), 'flash_failure');
			}
		}
	}
	
	/* delete function 
	 * @param $id, $realmId
	 */
	public function remove($id, $realmId){
		if (isset($id)){
			if($this->RealmAttribute->delete($id)){
				$this->redirect('/realm_attributes/index/'.$realmId);
				$this->Session->setFlash(__('Realm attribute is removed succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('Realm attribute is not removed succefully!', true), 'flash_failure');
			}
		} else {
			$this->redirect('/realm_attributes/index'.$realmId);
		}
	}
}