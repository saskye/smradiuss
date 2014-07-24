<?php
/**
 * Copyright (c) 2014, AllWorldIT
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */



/**
 * Wisp Users Attribute
 *
 */
class WispUsersAttributesController extends AppController
{
	/* index function
	 * @param $Id
	 */
	public function index($Id)
	{
		$uId = $this->WispUsersAttribute->selectUserId($Id);
		$userId = $uId[0]['wisp_userdata']['UserID'];
		if (isset($userId)) {
			$this->WispUsersAttribute->recursive = 0;
			$this->paginate = array(
				'limit' => PAGINATION_LIMIT,
				'conditions' => array('UserID' => $userId)
			);
			$wispUsersAttribute  = $this->paginate();
			$this->set('wispUsersAttribute', $wispUsersAttribute);
			$this->set('userId', $userId);
		} else {
			$this->redirect('/users/index');
		}
	}

	//public function add($userId)
	/*public function add()
	{
		//$this->set('userId', $userId);
		if ($this->request->is('post')) {

			$requestData = $this->request->data;
			$value = $requestData['WispUsersAttribute']['Value'];
			$modifier = $requestData['WispUsersAttribute']['Modifier'];
			$attrValue = $value;
			if(isset($modifier)) {
				switch($modifier)
				{
					case "Seconds":
						$attrValue = $value / 60;
						break;
					case "Minutes":
						$attrValue = $value;
						break;
					case "Hours":
						$attrValue = $value * 60;
						break;
					case "Days":
						$attrValue = $value * 1440;
						break;
					case "Weeks":
						$attrValue = $value * 10080;
						break;
					case "Months":
						$attrValue = $value * 44640;
						break;
					case "MBytes":
						$attrValue = $value;
						break;
					case "GBytes":
						$attrValue = $value * 1000;
						break;
					case "TBytes":
						$attrValue = $value * 1000000;
						break;
				}
			}
			$requestData['WispUsersAttribute']['Value'] = $attrValue;
			//$requestData['WispUsersAttribute']['UserID'] = $userId;
			$this->WispUsersAttribute->set($requestData);

			if ($this->WispUsersAttribute->validates()) {
				$this->requestAction('/wispUsers/add', array('pass' => $requestData));
				//$this->Session->write('booking_id', $this->request->data['Booking']['booking_id']);
				//$a = $this->redirect(array('controller'=>'wispUsers','action'=>'add',$requestData));
				//echo $a; exit;
				//$this->WispUsersAttribute->save($requestData);
				$this->Session->setFlash(__('Wisp user attribute is saved succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('Wisp user attribute is not saved!', true), 'flash_failure');
			}
		}
	}*/

	/* edit function
	 * @param $id, $userId
	 */
	public function edit($id, $userId){
		$wispUsersAttribute = $this->WispUsersAttribute->findById($id);
		$this->set('wispUsersAttribute', $wispUsersAttribute);
		if ($this->request->is('post')){
			$this->request->data['WispUsersAttribute']['Disabled'] = intval($this->request->data['WispUsersAttribute']['Disabled']);
			$requestData = $this->request->data;
			//echo "<pre>";print_r($requestData);exit;
			$value = $requestData['WispUsersAttribute']['Value'];
			$modifier = $requestData['WispUsersAttribute']['Modifier'];
			$attrValue = $value;
			if(isset($modifier)) {
				switch($modifier) {
					case "Seconds":
						$attrValue = $value / 60;
						break;
					case "Minutes":
						$attrValue = $value;
						break;
					case "Hours":
						$attrValue = $value * 60;
						break;
					case "Days":
						$attrValue = $value * 1440;
						break;
					case "Weeks":
						$attrValue = $value * 10080;
						break;
					case "Months":
						$attrValue = $value * 44640;
						break;
					case "MBytes":
						$attrValue = $value;
						break;
					case "GBytes":
						$attrValue = $value * 1000;
						break;
					case "TBytes":
						$attrValue = $value * 1000000;
						break;
				}
			}
			$requestData['WispUsersAttribute']['Value'] = $attrValue;
			$this->WispUsersAttribute->set($requestData);
			if ($this->WispUsersAttribute->validates()) {
				$this->WispUsersAttribute->id = $id;
			    $this->WispUsersAttribute->save($requestData);
				$this->Session->setFlash(__('Wisp user attribute is updated succefully!', true), 'flash_success');
			} else {
			    $this->Session->setFlash(__('Wisp user attribute is not updated!', true), 'flash_failure');
			}
		}
	}
	/* delete function
	 * @param $id, $userId
	 */
	public function remove($id, $userId){
		if (isset($id)){
			if($this->WispUsersAttribute->delete($id)){
				$this->redirect('/wispUsers_attributes/index/'.$userId);
				$this->Session->setFlash(__('Wisp user attribute is removed succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('Wisp user attribute is not removed!', true), 'flash_failure');
			}
		} else {
			$this->redirect('/wispUsers_attributes/index/'.$userId);
		}
	}

	/* attribute function
	 * @param $Id
	 */
	public function attribute($id){
		if ($this->request->is('post')){
			$this->request->data['UserAttribute'] = $this->request->data['User'];
			$this->UserAttribute->set($this->request->data);
			if ($this->UserAttribute->validates()) {
				$this->UserAttribute->save($this->request->data);
				$this->Session->setFlash(__('User attribute is saved succefully!', true), 'flash_success');
			} else {
				$this->Session->setFlash(__('User attribute is not saved succefully!', true), 'flash_failure');
			}
		}
	}
}

// vim: ts=4
