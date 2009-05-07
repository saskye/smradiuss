<?php
# JSON interface
# Copyright (C) 2007-2009, AllWorldIT
# 
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License along
# with this program; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.



class json_response {

	private $_fields = array();
	private $_id;
	private $_results;
	private $_datasetSize;
	private $_status = RES_OK;

	## @method setID($id)
	# Set ID column
	#
	# @param id ID column name
	public function setID($id) {
		$this->_id = $id;
	}

	## @method setStatus($status)
	# Set response status
	#
	# @param status Either RES_OK (default) or RES_ERROR
	public function setStatus($status) {
		$this->_status = $status;
	}

	## @method addField($name,$type)
	# Add a field to our return results
	#
	# @param name Field name
	# @param type Field type, 'int', 'string', 'float', 'boolean', 'date'
	public function addField($name,$type) {
		# Build field
		$field = array(
			'name' => $name,
			'type' => $type
		);

		# Set ISO date format
		if ($field['type'] == "date") {
			$field['dateFormat'] = "Y-m-d";
		}

		# Add field to list
		array_push($this->_fields,$field);
	}

	## @method setDatasetSize($size)
	# Set how many records are returned in the dataset
	#
	# @param size Dataset size
	public function setDatasetSize($size) {
		$this->_datasetSize = $size;
	}

	## @method parseArrayRef($array)
	# Parse in the array of results and fix it up
	#
	# @param arrayref Array ref containing the results
	public function parseArray($array) {
		$this->_results = array();

		# Loop with array items
		foreach ($array as $aitem) {
			$item = array();

			# Loop with fields we want
			foreach ($this->_fields as $field) {
				# FIXME - typecast?
				$item[$field['name']] = $aitem[$field['name']];
			}
			
			array_push($this->_results,$item);
		}

	}

	## @method parseHash($hashref)
	# Parse in the hash of results and fix it up
	#
	# @param hashref Hash ref containing the results
	public function parseHash($hash) {
		$this->_results = array();

		foreach ($this->_fields as $field) {
			# FIXME - typecast?
			$this_results[$field['name']] = $hash[$field['name']];
		}
	}

	## @method export
	# Export response into something we return 
	#
	# @return JSON hash
	# @li result - Result code/status
	# @li data - Ref containing results
	# @li metaData - Metadata containing info about the results being returned
	# Metadata contains properties..
	# - root: root element, which is always 'data'
	# - fields: Optional field description, arrayref of hash refs, name = 'name', type 'type' and 'dateFormat' = 'Y-m-d'
	# - id: Optional ID field name
	# - totalProperty: Optional property name containing the number of records, always 'datasetSize'
	# @li datasetSize Optional, number of records we're rturning
	public function export() {

		# Build result
		$ret = array(
			'result' => $this->_status,

			# Additional stuff for other things to make life easier
			'success' => $this->_status == RES_OK ? 1 : 0,
			'metaData' => array(
				'successProperty' => 'success'
			)
		);

		# If we have results, add them
		if (isset($this->_results)) {
			$ret['data'] = $this->_results;
			$ret['metaData']['root'] = 'data';

			# If we have fields, set them up
			if (isset($this->_fields)) {
				$ret['metaData']['fields'] = $this->_fields;
			}
		}

		# Check if we have an ID set
		if (isset($this->_id)) {
			$ret['metaData']['totalProperty'] = 'datasetSize';
			$ret['datasetSize'] = $this->_datasetSize;
		}

		return $ret;
	}
}


# vim: ts=4
