<?php
class ClientAttribute extends AppModel
{
	public $useTable = 'client_attributes';
	
	public $validate = array('Name' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please enter value')), 'Value' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please enter value')));
}