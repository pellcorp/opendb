<?php
/* 	
	Open Media Collectors Database
	Copyright (C) 2001,2013 by Jason Pell

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/
include_once ('./lib/install.php');

class OpenDbUpgrader {
	var $_upgraderdir;
	var $_error_rs;
	var $_steps = NULL;
	var $_from_version;
	var $_to_version;

	/**
	    @param $to_version - if NULL, then upgrading to current OpenDb version.
	    @$steps_r = array(
	    	array(title=>'step title',
	    		description=>'step description',
	    		skippable=>[TRUE|FALSE]),
	*/
	function __construct($from_version, $to_version = NULL, $steps_r = NULL) {
		// derive a upgrade sql directory from the classname
		$this->_upgraderdir = './install/upgrade/' . $from_version;
		if ($to_version != NULL) {
			// if only one upgrader upgrades from a version, there is no need to specify a TO directory component.
			$tmpdir = $this->_upgraderdir . '_' . $to_version;
			if (is_dir ( $tmpdir ))
				$this->_upgraderdir .= '_' . $to_version;
		}
		
		if (is_array ( $steps_r )) {
			$this->_steps = $steps_r;
		} else {
			$this->_steps [] = array (
					'title' => NULL,
					'description' => NULL,
					'skippable' => FALSE );
		}
		
		$this->_from_version = $from_version;
		
		if (strlen ( $to_version ) > 0)
			$this->_to_version = $to_version;
		else
			$this->_to_version = get_opendb_version (); // defaults to current opendb version
	}

	/**
	*/
	function getDescription() {
		if (strlen ( $this->_from_version ) > 0 && strlen ( $this->_to_version ) > 0)
			return 'Upgrade from ' . $this->_from_version . ' to ' . $this->_to_version;
		else
			return 'Upgrade from ' . $this->_from_version;
	}

	function getFromVersion() {
		return $this->_from_version;
	}

	function getToVersion() {
		return $this->_to_version;
	}

	function getUpgraderDir() {
		return $this->_upgraderdir;
	}

	/**
		Return number of steps required.
	*/
	function getNoOfSteps() {
		return count ( $this->_steps );
	}

	/**
		Returns title of the step, default is:
			return 'Step '.$index;
	*/
	function getStepTitle($index) {
		$step_r = $this->_getStepElement ( $index );
		if (is_array ( $step_r )) {
			if ($step_r ['title'] != NULL)
				return $step_r ['title'];
			else
				return 'Step ' . $index;
		}
		
		//else
		return NULL;
	}

	/**
		Returns more information about the step, default is to return NULL
	*/
	function getStepDescription($index) {
		$step_r = $this->_getStepElement ( $index );
		if (is_array ( $step_r )) {
			if ($step_r ['description'] != NULL)
				return $step_r ['description'];
		}
		
		//else
		return NULL;
	}

	function isStepSkippable($index) {
		$step_r = $this->_getStepElement ( $index );
		if (is_array ( $step_r )) {
			if (is_bool ( $step_r ['skippable'] ))
				return $step_r ['skippable'];
		}
		
		//else
		return FALSE;
	}

	function _getStepElement($index) {
		if ($index > 0 && $index <= count ( $this->_steps )) {
			return $this->_steps [$index - 1];
		}
		
		//else
		return NULL;
	}

	/**
		Execute the given step.  

		Returns TRUE, if step completed without incident.   If step is only partially complete, return how many iterations remaining to complete it,
		this relates to the stepPart.
		Return FALSE, if at least one error occured.
	*/
	function executeStep($index, $stepPart = NULL) {
		$this->_step_remainder_count = NULL;
		
		$this->_error_rs = NULL;
		
		if ($index <= $this->getNoOfSteps ()) {
			$stepFuncName = 'executeStep' . $index;
			if (method_exists ( $this, $stepFuncName )) {
				return $this->$stepFuncName ( $stepPart );
			} else if (file_exists ( $this->getUpgraderDir () . '/step' . $index . '.sql' )) {
				$errors = NULL;
				if (exec_install_sql_file ( $this->getUpgraderDir () . '/step' . $index . '.sql', $errors )) {
					return TRUE;
				} else {
					$this->addErrors ( $errors );
					return FALSE;
				}
			} else {
				$this->addError ( $stepFuncName . ' does not exist' );
				return FALSE;
			}
		} else {
			$this->addError ( 'Invalid Step' );
			return FALSE;
		}
	}

	/**
		If an executeStep($index) returns FALSE, then this will return the
		number of errors recorded.
	*/
	function getNoOfErrors() {
		if (is_array ( $this->_error_rs ))
			return count ( $this->_error_rs );
		else
			return 0;
	}

	function getErrors() {
		return $this->_error_rs;
	}

	/**
	@param $detail - Any details about the message, which might include the SQL statement
	that failed to be executed.
	*/
	function addError($message, $detail = NULL) {
		$this->_error_rs [] = array (
				'error' => $message,
				'detail' => $detail );
	}

	/**
	Add errors with structure of:
	    error=>'',detail=>''
	*/
	function addErrors($errors) {
		if (is_array ( $errors )) {
			foreach ($errors as $error) {
				if (is_array ( $error ))
					$this->addError ( $error ['error'], $error ['detail'] );
				else
					$this->addError ( $error, NULL );
			}
		}
	}
}
?>
