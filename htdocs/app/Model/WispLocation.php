<?php
class WispLocation extends AppModel
{
	public $useTable = 'wisp_locations';
	
	public $validate = array('Name' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please enter name.')));
}