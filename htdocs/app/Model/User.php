<?php
/**
 * User Model
 *
 */
 
class User extends AppModel
{
	//Validating form controller.
	public $validate = array('Username' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please choose a username'), 'unique' => array('rule' => 'isUnique', 'message' => 'The username you have chosen has already been registered')));
	
	// Delete user records form different tables.
	public function deleteUserRef($userId)
	{
		 $res = $this->query("delete from wisp_userdata where UserID = ".$userId);
		$res = $this->query("delete from user_attributes where UserID = '".$userId."'");
		$res = $this->query("delete from users_to_groups where UserID = '".$userId."'");
		$res = $this->query("delete from topups where UserID = '".$userId."'");
	}
}