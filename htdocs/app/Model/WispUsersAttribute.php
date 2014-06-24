<?php
class WispUsersAttribute extends AppModel
{
	public $useTable = 'user_attributes';
	
	public $validate = array('Value' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please enter value.')), 'Name' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please select value')), 'Operator' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please select value')));
	
	public function selectUserId($id)
	{
		return $res = $this->query("select UserID from wisp_userdata where ID = '".$id."'");
	}
}