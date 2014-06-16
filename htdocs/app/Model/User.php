<?php
class User extends AppModel
{
	public $validate = array('Username' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please choose a username'), 'unique' => array('rule' => 'isUnique', 'message' => 'The username you have chosen has already been registered')));
	
	public function deleteGroup($userId)
	{
		return $res = $this->query("DELETE FROM users_to_groups where UserID = ".$userId);
	}
}