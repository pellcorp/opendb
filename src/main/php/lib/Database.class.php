<?php
/*
 Open Media Collectors Database
Copyright (C) 2001-2012 by Jason Pell

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

if (extension_loaded('mysqli')) {
	include_once('lib/database/mysqli.inc.php');
} else if (extension_loaded('mysql')) {
	include_once('lib/database/mysql.inc.php');
} else {
	die('MySQL extension is not available');
}

include_once("lib/databaseutils.php");
include_once("lib/logging.php");

class Database {
	var $_dblink;
	var $_dbconfig;

	function Database($dbserver_conf_r) {
		if (is_array($dbserver_conf_r)) {
			$this->_dbconfig = $dbserver_conf_r;

			$link = db_connect($dbserver_conf_r['host'], $dbserver_conf_r['username'], $dbserver_conf_r['passwd'], $dbserver_conf_r['dbname']);

			if ($link !== FALSE) {
				$this->_dblink = $link;
			} else {
				// opendb logger relies on the opendb config to return valid config for logging even if
				// no db connection!
				opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, db_error());
			}
		}
	}

	function isConnected() {
		return $this->_dblink != NULL;
	}

	function ping() {
		if ($this->isConnected()) {
			return _db_ping($this->_dblink);
		} else {
			return FALSE;
		}
	}

	function close() {
		if ($this->isConnected()) {
			_db_close($this->_dblink);
			$this->_dblink = NULL;
		}
	}

	function errno() {
		if ($this->isConnected()) {
			return _db_errno($this->_dblink);
		} else {
			return _db_errno();
		}
	}

	function error() {
		$errno = NULL;
		if ($this->isConnected()) {
			$errno = _db_error($this->_dblink);
		} else {
			$errno = _db_error();
		}

		if (strlen($errno) == 0) {
			return NULL;
		} else {
			return $errno;
		}
	}

	function query($sql) {
		if ($this->isConnected()) {
			// expand any prefixes, display any debugging, etc
			if (strlen($this->_dbconfig['table_prefix']) > 0) {
				$sql = parse_sql_statement($sql, $this->_dbconfig['table_prefix']);
			}

			if ($this->_dbconfig['debug-sql'] === TRUE) {
				echo ('<p class="debug-sql">SQL: ' . $sql . '</p>');
			}

			return _db_query($this->_dblink, $sql);
		} else {
			return FALSE;
		}
	}

	function affectedRows() {
		if ($this->isConnected()) {
			return _db_affected_rows($this->_dblink);
		} else {
			return FALSE;
		}
	}

	function lastInsertId() {
		if ($this->isConnected()) {
			return _db_insert_id($this->_dblink);
		} else {
			return FALSE;
		}
	}

	function freeResult($result) {
		if ($this->isConnected()) {
			return _db_free_result($result);
		} else {
			return FALSE;
		}
	}

	function fetchAssoc($result) {
		if ($this->isConnected()) {
			return _db_fetch_assoc($result);
		} else {
			return FALSE;
		}
	}

	function fetchRow($result) {
		if ($this->isConnected()) {
			return _db_fetch_row($result);
		} else {
			return FALSE;
		}
	}

	function fieldName($result, $field_offset) {
		if ($this->isConnected()) {
			return _db_field_name($result, $field_offset);
		} else {
			return FALSE;
		}
	}

	function numRows($result) {
		if ($this->isConnected()) {
			return _db_num_rows($result);
		} else {
			return FALSE;
		}
	}

	function numFields($result) {
		if ($this->isConnected()) {
			return _db_num_fields($result);
		} else {
			return FALSE;
		}
	}
}
