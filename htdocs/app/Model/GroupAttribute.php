<?php
/**
 * Group Attribute Model
 *
 */

class GroupAttribute extends AppModel
{
	// Validating form controller.
	public $validate = array('Name' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please enter name')), 'Value' => array('required' => array('rule' => array('notEmpty'),'message' => 'Please enter value')));
}



// vim: ts=4
