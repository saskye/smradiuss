<?php
class UserGroup extends AppModel
{
	public $useTable = 'users_to_groups';
	
	public $validate = array('Type' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please enter value')));
	
	public function selectGroup()
	{
		return $res = $this->query("select ID, Name from groups");
	}
	
	public function getGroupById($groupId)
	{
		return $res = $this->query("select ID,Name from groups where ID = ".$groupId);
	}
	
	public function insertRec($userId, $data)
	{
		//echo "INSERT INTO users_to_groups (UserID,GroupID) VALUES ('".$userId."','".$data['UserGroup']['Type']."')";
		$res = $this->query("INSERT INTO users_to_groups (UserID,GroupID) VALUES ('".$userId."','".$data['UserGroup']['Type']."')");
	}
}