<?php
/**
 * Copyright (c) 2014, AllWorldIT
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */



App::uses('CakeErrorController', 'Controller');

/**
 * @class AWITErrorController
 * Error Handling Controller which uses the RequestHandler component
 * with a class map which makes use of the custom AWITJson view
 *
 *
 */
class AWITErrorController extends CakeErrorController {

/**
 * @var $components
 * This variable is used for including components.
 */
public $components = array(
	'RequestHandler' => array(
		'viewClassMap' => array(
			'json' => 'AWITJson',
		)
	)
);



/**
 * @method __construct
 * Constructor
 *
 * @param CakeRequest $request
 * @param CakeResponse $response
 */
	public function __construct($request = null, $response = null) {
		parent::__construct($request, $response);
		$this->constructClasses();
		if (count(Router::extensions()) &&
				!$this->Components->attached('RequestHandler')
		) {
			$this->RequestHandler = $this->Components->load('RequestHandler');
		}
		if ($this->Components->enabled('Auth')) {
			$this->Components->disable('Auth');
		}
		if ($this->Components->enabled('Security')) {
			$this->Components->disable('Security');
		}
		$this->_set(array('cacheAction' => false, 'viewPath' => 'Errors'));
	}

}
