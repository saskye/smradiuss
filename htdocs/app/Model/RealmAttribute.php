<?php
class RealmAttribute extends AppModel
{
	public $useTable = 'realm_attributes';
	
	public $validate = array('Name' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please enter value')), 'Value' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please enter value')));
}