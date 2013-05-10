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

include_once("lib/AdminAjaxJobs.class.php");
include_once("lib/WrapperFileHandler.class.php");
include_once("lib/import.php");
include_once("lib/fileutils.php");

class InstallTableAjaxJobs extends AdminAjaxJobs {
	var $_uploadFile;
	var $_totalItems;

	function InstallTableAjaxJobs($job) {
		parent::AdminAjaxJobs(get_class($this), $job, 1000);
	}

	function __executeJob() {
		$this->_uploadFile = $this->_args[0];

		$this->_processed = 0;
		$this->_failures = 0;

		$this->_totalItems = $this->__count_records();
		$this->_remaining = $this->_totalItems - $this->_completed;

		return $this->__perform_install_table_batch();
	}

	function __count_records() {
		$fh = file_open('admin/s_site_plugin/upload/' . $this->_uploadFile, 'rb');
		$count = 0;
		if ($fh !== FALSE) {
			while (($data = fgetcsv($fh, 4096, ",")) !== FALSE) {
				$count++;
			}
		}
		@fclose($fh);
		return $count;
	}

	function calculateProgress() {
		// nothing more to do!
	}

	function __perform_install_table_batch() {
		if (opendb_file_exists("admin/s_site_plugin/sql/" . $this->_job . ".install.class.php")) {
			$classname = "Install_" . $this->_job;

			include_once("admin/s_site_plugin/sql/" . $this->_job . ".install.class.php");
			$installPlugin = new $classname();

			// this is currently the only type we support.
			if ($installPlugin->getInstallType() == 'Install_Table') {
				if (check_opendb_table($installPlugin->getInstallTable())) {
					if ($this->_batchlimit > 0) {
						$fh = @file_open('admin/s_site_plugin/upload/' . $this->_uploadFile, 'rb');
						if ($fh !== FALSE) {
							$installPlugin->setRowRange($this->_completed + 1, $this->_completed + $this->_batchlimit);

							if (($header_row = fgetcsv($fh, 4096, ",")) !== FALSE) {
								$installPlugin->_handleRow($header_row);
							}

							while (!$installPlugin->isEndRowFound() && ($read_row_r = fgetcsv($fh, 4096, ",")) !== FALSE) {
								$installPlugin->_handleRow($read_row_r);
							}
							fclose($fh);

							$this->_processed = $installPlugin->getProcessedCount();
							$this->_completed = $installPlugin->getRowCount();
						} else {
							opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Upload file not accessible');
							return FALSE;
						}
					} else {
						return FALSE;
					}
				} else {
					opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Plugin table ' . strtoupper($installPlugin->getInstallTable()) . ' does not exist');
					return FALSE;
				}
			} else {
				return FALSE;
			}
		} else {
			opendb_logger(OPENDB_LOG_ERROR, __FILE__, __FUNCTION__, 'Site Plugin installation maintenance class not found');
			return FALSE;
		}
	}

	//	function summary_details(&$installPlugin) {
	//		$buffer .= "\n<h4>Summary Details</h4>";
	//		$buffer .= "\n<table><tr class=\"navbar\">";
	//		$buffer .= "\n<th>Processed</th><th>Inserted</th><th>Updated</th><th>Deleted</th></tr>";
	//		$buffer .= "\n<tr>";
	//		$buffer .= "<td class=\"oddRow\">".$installPlugin->getProcessedCount()."</td>";
	//		$buffer .= "<td class=\"oddRow\">".$installPlugin->getInsertCount()."</td>";
	//		$buffer .= "<td class=\"oddRow\">".$installPlugin->getUpdateCount()."</td>";
	//		$buffer .= "<td class=\"oddRow\">".$installPlugin->getDeleteCount()."</td>";
	//		$buffer .= "</tr></table>";
	//		
	//		$errors_r = $installPlugin->getErrors();
	//		if(is_not_empty_array($errors_r))
	//		{
	//			$buffer .= "\n<h4>Error Details</h4>";
	//			
	//			$buffer .= "\n<table>";
	//			reset($errors_r);
	//			$buffer .= "\n<tr class=\"navbar\"><th>Row No.</th><th>Error</th><th>Details</th></tr>";
	//			$toggle=TRUE;
	//			while(list(,$error_r) = each($errors_r))
	//			{
	//				$color = ($toggle?"oddRow":"evenRow");
	//				$toggle = !$toggle;
	//	
	//				$buffer .= "\n<tr><td class=\"$color\">".$error_r['rowcount']."</td><td class=\"$color\">".$error_r['error']."</td><td class=\"$color\">".$error_r['details']."</td></tr>";
	//			}
	//			$buffer .= "\n</table>";
	//		}
	//		
	//		return $buffer;
	//	}
}
?>