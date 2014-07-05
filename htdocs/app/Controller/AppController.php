<?php
/**
 * Application level Controller
 *
 * @brief This file is application-wide controller file.
 *
 * You can put all application-wide controller-related methods here.
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * @class AppController
 * @brief Add your application-wide methods in the class below, your controllers
 *		  will inherit them.
 * @package app.Controller
 */

class AppController extends Controller {
	public $components = array('DebugKit.Toolbar', 'Session','Cookie');
}

// vim: ts=4
