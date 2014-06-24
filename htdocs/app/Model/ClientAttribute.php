<?php
/**
 * Client Attribute Model
 *
 */

class ClientAttribute extends AppModel
{
	public $useTable = 'client_attributes';



	// Validating form controllers.
	public $validate = array(
		'Name' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'message' => 'Please enter value')
			),
		'Value' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'message' => 'Please enter value'
			)
		)
	);



}



// vim: ts=4
