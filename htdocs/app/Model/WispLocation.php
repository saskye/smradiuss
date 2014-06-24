<?php
/**
 * Wisp Location Model
 *
 */
 
class WispLocation extends AppModel
{
	public $useTable = 'wisp_locations';
	
	//Validating form controller.
	public $validate = array('Name' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please enter name.')));
}