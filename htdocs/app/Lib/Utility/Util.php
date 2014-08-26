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



/**
 * Included libraries.
 */
App::uses('Model', 'Model');
App::uses('AppModel', 'Model');
App::uses('ConnectionManager', 'Model');
App::uses('ClassRegistry', 'Utility');



/**
 * @class Util
 * Utility class
 *
 * @package       Cake.Utility
 */
class Util extends Object {



	/**
	 * @def ATTRIBUTE_OPERATORS
	 * Contains a list of Attribute Operators used throughout the application
	 */
	public static $attributeOperators = array (
		'=',
		':=',
		'==',
		'+=',
		'!=',
		'<',
		'>',
		'<=',
		'>=',
		'=~',
		'!~',
		'=*',
		'!*',
		'||=='
	);



	/**
	 * @method getAttributeOperators
	 * Returns the array which represents the Attribute Operators
	 *
	 * @return ATTRIBUTE_OPERATORS
	 */
	public static function getAttributeOperators() {
		return self::$attributeOperators;
	}



	/**
	 * @method getOperatorByValue
	 *
	 * @param $search the value to search for
	 * @return int Index at the given operator value
	 */
	public static function getAttributeOperatorIndexByValue($search) {
		return end(array_keys(array_search($search, self::$attributeOperators)));
	}



}
