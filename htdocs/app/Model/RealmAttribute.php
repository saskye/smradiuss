<?php
/**
 * Realm Attribute Model
 *
 */
 
class RealmAttribute extends AppModel
{
	public $useTable = 'realm_attributes';
	
	//Validating form controllers.
	public $validate = array('Name' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please enter value')), 'Value' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please enter value')));
}