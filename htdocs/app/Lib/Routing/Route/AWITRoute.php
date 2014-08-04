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

App::uses('CakeRoute', 'Cake/Routing/Route');

/**
 * @class AWITRoute
 * Custom routing class to add support for RFC3986 URI schemes
 *
 */
class AWITRoute extends CakeRoute {

/**
 * @method parse
 * Constructs the route array with the needed parameters
 *
 * @param string $url The URL to attempt to parse.
 * @return mixed Boolean false on failure, otherwise an array or parameters
 */

	public function parse($url) {

		// Parsing REQUEST_URI for controller and action
		$uriParts = explode('/', $_SERVER['REQUEST_URI']);

		// Controller and Action is located within these bounds
		if (count($uriParts) < 2) {
			return false;
		}

		$action = $uriParts[count($uriParts) - 1];
		$controller = $uriParts[count($uriParts) - 2];

		$action = substr($action, 0, strpos($action, '?'));

		$route['controller'] = $controller;
		$route['action'] = $action;

		// Placing $_REQUEST array as named parameter array
		$route['pass'] = array();
		$route['named'] = $_REQUEST;

		return $route;
	}

}
