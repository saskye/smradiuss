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



// Load component class.
App::uses('Component', 'Controller');



// include model.
App::import('Model','WebuiUser');



/**
 * @class AccessComponent
 * @brief This class manages user permission.
 */
class AccessComponent extends Component
{
	// The other components that used in our component.
	var $components = array('Acl', 'Auth');
	// The class's $user var will be initiated as the component is loaded.
	var $user;



	/**
	 * @method startup
	 * @brief The startup method is called after the controller’s beforeFilter method
	 * but before the controller executes the current action handler.
	 */
	function startup(Controller $controller)
	{
		$this->user = $this->Auth->user();
	}



	/**
	 * @method check
	 * @brief This method is used for check user has permission or not.
	 * @param $aro
	 * @param $aco
	 * @return true/false
	 */
	function check($aro, $aco)
	{
		if ($this->Acl->check($aro, $aco)) {
			return true;
		} else {
			return false;
		}
	}



	/**
	 * @method checkHelper
	 * It loads a component with a custom method for use in the helper.
	 * @param $aro
	 * @param $aco
	 * @param $action
	 */
	function checkHelper($aro, $aco, $action = "*")
	{
		App::import('Component', 'Acl');
		$acl = new AclComponent(new ComponentCollection());
		return $acl->check($aro, $aco, $action);
	}



	/**
	 * @method getGroupName
	 * This method is used for get group name.
	 * @param $userId
	 * @return $groupName['Aro']['alias']
	 */
	function getGroupName($userId)
	{
		$groupName = $this->Acl->Aro->find(
			'first',
			array(
				'fields' => 'alias',
				'conditions' => array(
					'id' => $userId
				)
			)
		);
		return $groupName['Aro']['alias'];
	}



	/**
	 * @method checkPermission
	 * This method is used for check user permission.
	 * @param $model
	 * @param action
	 * @userId
	 * @return $permission
	 */
	function checkPermission($model, $action, $userId)
	{
		$objUser = new WebuiUser();
		$userData = $objUser->find(
			'first',
			array(
				'fields' => 'Type',
				'conditions' => array(
					'ID' => $userId
				)
			)
		);
		$controllerId = $this->Acl->Aco->find(
			'first',
			array(
				'fields' => 'id',
				'conditions' => array(
					'Model' => $model,
					'Actions' => $action,
					'parent_id IS NOT NULL'
				)
			)
		);
		$permission = $this->Acl->Aro->Permission->find(
			'first',
			array(
				'conditions' => array(
					'aro_id' => $userData['WebuiUser']['Type'],
					'aco_id' => $controllerId['Aco']['id']
				)
			)
		);
		return $permission;
	}
}



# vim: ts=4
?>
