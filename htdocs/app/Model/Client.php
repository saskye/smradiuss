<?php
class Client extends AppModel
{
	public $useTable = 'clients';
	
	public $validate = array('Name' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please enter value.')),'AccessList' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please enter value.')));
}