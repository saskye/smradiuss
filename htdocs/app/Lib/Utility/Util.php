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
class Util extends Object
{
	/**
	 * @method getDefinedAttributeOperators
	 * Returns the array which represents the Defined Attribute Operators
	 *
	 * @return $attributeOperators
	 */
	public static function getDefinedAttributeOperators() {
		// Defined attribute operators
		$attributeOperators = array (
			'=' => array(
				'title' => __('Add as reply if unique'),
				'description' => __('
Not allowed as a check item for RADIUS protocol attributes. It is allowed for server configuration attributes (Auth-Type, etc),
and sets the value of on attribute, only if there is no other item of the same name.
As a reply item, it means "add the item to the reply list, but only if there is no other item of the same attribute.
				'),
			),
			':=' => array(
				'title' => __('Set configuration value'),
				'description' => __('
Always matches as a check item, and replaces in the configuration items any attribute of
the same name. If no attribute of that name appears in the request, then this attribute is added.
As a reply item, it has an identical meaning, but for the reply items, instead of the request items.
				'),
			),
			'==' => array(
				'title' => __('Match value in request'),
				'description' => __('
As a check item, it matches if the named attribute is present in the request, AND has the given value.
Not allowed as a reply item.
				'),
			),
			'+=' => array(
				'title' => __('Add reply and set configuration'),
				'description' => __('
Always matches as a check item, and adds the current attribute with value to the list of configuration items.
As a reply item, it has an identical meaning, but the attribute is added to the reply items.
				'),
			),
			'!=' => array(
				'title' => __('Inverse match value in request'),
				'description' => __('
As a check item, matches if the given attribute is in the request, AND does not have the given value.
Not allowed as a reply item.
				'),
			),
			'<' => array(
				'title' => __('Match less-than value in request'),
				'description' => __('
As a check item, it matches if the request contains an attribute with a value less than the one given.
Not allowed as a reply item.
				'),
			),
			'>' => array(
				'title' => __('Match greater-than value in request'),
				'description' => __('
As a check item, it matches if the request contains an attribute with a value greater than the one given.
Not allowed as a reply item.
				'),
			),
			'<=' => array(
				'title' => __('Match less-than or equal value in request'),
				'description' => __('
As a check item, it matches if the request contains an attribute with a value less than, or equal to the one given.
Not allowed as a reply item.
				'),
			),
			'>=' => array(
				'title' => __('Match greater-than or equal value in request'),
				'description' => __('
As a check item, it matches if the request contains an attribute with a value greater than, or equal to the one given.
Not allowed as a reply item.
				'),
			),
			'=~' => array(
				'title' => __('Match string containing regex in request'),
				'description' => __('
As a check item, it matches if the request contains an attribute which matches the given regular expression.
This operator may only be applied to string attributes.
Not allowed as a reply item.
				'),
			),
			'!~' => array(
				'title' => __('Match string not containing regex in request'),
				'description' => __('
As a check item, it matches if the request contains an attribute which does not match the given regular expression.
This operator may only be applied to string attributes.
Not allowed as a reply item.
				'),
			),
			'=*' => array(
				'title' => __('Match if attribute is defined in request'),
				'description' => __('
As a check item, it matches if the request contains the named attribute, no matter what the value is.
Not allowed as a reply item.
				'),
			),
			'!*' => array(
				'title' => __('Match if attribute is not defined in request'),
				'description' => __('
As a check item, it matches if the request does not contain the named attribute, no matter what the value is.
Not allowed as a reply item.
				'),
			),
			'||==' => array(
				'title' => __('Match any of these values in request'),
				'description' => __('
Logical OR, this creates a multi-value attribute of which any of the items can match the operator.
				'),
			)
		);



		return $attributeOperators;
	}



	/**
	 * @method getAttributeOperators
	 * Returns the array which represents the Attribute Operators
	 *
	 * @return $operators
	 */
	public static function getAttributeOperators() {

		$operators = array();
		foreach (array_keys(self::getDefinedAttributeOperators()) as $key) {
			$operators[$key] = $key;
		}

		return $operators;
	}



	/**
	 * @method getWispAttributeOperators
	 * Returns the array which represents the Attribute Operators
	 * for use with Wisp attribute select lists
	 *
	 * @return $operators
	 */
	public static function getWispAttributeOperators() {

		$definedOperators = self::getDefinedAttributeOperators();
		$operators = array();
		foreach (array_keys($definedOperators) as $key) {
			$operators[$key] = $definedOperators[$key]['title'];
		}

		return $operators;
	}



	/**
	 * @method getWispUserAttributeNames
	 * This method is used for return wisp user attribute option name array.
	 * @return $modifier
	 */
	public static function getWispUserAttributeNames()
	{
		$options = array(
			'Traffic Limit' => __('Traffic Limit'),
			'Uptime Limit' => __('Uptime Limit'),
			'IP Address' => __('IP Address'),
			'MAC Address' => __('MAC Address')
		);
		return $options;
	}



}
