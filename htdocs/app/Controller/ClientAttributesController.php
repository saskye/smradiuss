<?php
/**
 * Client Attributes
 * 
 */
 
class ClientAttributesController extends AppController {

	/* index function 
	 * @param $clientID
	 */
		public function index($clientID){
			if (isset($clientID)){			
				$this->paginate = array(
				'limit' => PAGINATION_LIMIT,
				'conditions' => array('ClientAttribute.ClientID' => $clientID)
				);
				$clientAttributes = $this->paginate();
				
				$this->set('clientAttributes', $clientAttributes);
				$this->set('clientID', $clientID);
			} else {
				$this->redirect('/client_attributes/index');
			}			
		}	
	
	/* add function 
	 * @param $clientID
	 */
	 
		public function add($clientID){
			$this->set('clientID', $clientID);
			if ($this->request->is('post')){
				$this->request->data['ClientAttribute']['Disabled'] = intval($this->request->data['ClientAttribute']['Disabled']);
				$this->request->data['ClientAttribute']['ClientID'] = intval($this->request->params['pass'][0]);
				$this->ClientAttribute->set($this->request->data);
				if ($this->ClientAttribute->validates()) {
					$this->ClientAttribute->save($this->request->data);
					$this->Session->setFlash(__('Client attribute is saved succefully!', true), 'flash_success');
				} else {
					$this->Session->setFlash(__('Client attribute is not saved succefully!', true), 'flash_failure');
				}
			}	
		}
	
	/* edit function 
	 * @param $id , $clientID
	 */
		public function edit($id, $clientID){
			$clientAttribute = $this->ClientAttribute->findById($id);
			$this->set('clientAttribute', $clientAttribute);
			if ($this->request->is('post')){
				$this->request->data['ClientAttribute']['Disabled'] = intval($this->request->data['ClientAttribute']['Disabled']);
				$this->ClientAttribute->set($this->request->data);
				if ($this->ClientAttribute->validates()) {
					$this->ClientAttribute->id = $id;
					$this->ClientAttribute->save($this->request->data);
					$this->Session->setFlash(__('Clien attribute is saved succefully!', true), 'flash_success');
				} else {
					$this->Session->setFlash(__('Clien attribute is not saved succefully!', true), 'flash_failure');
				}
			}
		}
	
	/* delete function 
	 * @param $id , $clientID
	 */
		public function remove($id, $clientID){
			if (isset($id)){
				if($this->ClientAttribute->delete($id)){
					$this->redirect('/client_attributes/index/'.$clientID);
					$this->Session->setFlash(__('Client attribute is removed succefully!', true), 'flash_success');
				} else {
					$this->Session->setFlash(__('Client attribute is not removed succefully!', true), 'flash_failure');
			}
			} else {
				$this->redirect('/client_attributes/index'.$clientID);
			}
		}
}