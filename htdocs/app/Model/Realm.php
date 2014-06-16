<?php
class Realm extends AppModel
{
	public $useTable = 'realms';
	
	public $validate = array('Name' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please enter value')));
}