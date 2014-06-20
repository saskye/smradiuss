<?php
/**
 * Realm Model
 *
 */
 
class Realm extends AppModel
{
	public $useTable = 'realms';
	
	//Validating form controller.
	public $validate = array('Name' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please enter value')));
}