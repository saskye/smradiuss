<?php
/**
 * User Group Model
 *
 */
 
class UserGroup extends AppModel
{
	public $useTable = 'users_to_groups';
	
	//Validating form controllers.
	public $validate = array('Type' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please enter value')));
	
	//Fetching  all groups for select box controller.
	public function selectGroup()
	{
		return $res = $this->query("select ID, Name from groups");
	}
	
	//Fetching group name via its id.
	public function getGroupById($groupId)
	{
		return $res = $this->query("select ID,Name from groups where ID = ".$groupId);
	}
	
	// Saving user groups.
	public function insertRec($userId, $data)
	{
		$res = $this->query("INSERT INTO users_to_groups (UserID,GroupID) VALUES ('".$userId."','".$data['UserGroup']['Type']."')");
	}
}