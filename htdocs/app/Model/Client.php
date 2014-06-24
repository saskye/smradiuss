<?php
/**
 * Client Model
 *
 */

class Client extends AppModel
{
	public $useTable = 'clients';

	// Validating form controllers
	public $validate = array(
		'Name' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'message' => 'Please enter value.'
			)
		),
		'AccessList' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'message' => 'Please enter value.'
			)
		)
	);
}

// vim: ts=4
