<?php

include_once("./lib/AdminAjaxJobs.class.php");
include_once("./lib/WrapperFileHandler.class.php");
include_once("./lib/import.php");

class InstallTableAjaxJobs extends AdminAjaxJobs {
	var $_uploadFile;
	var $_totalItems;

	function __construct($job) {
		parent::__construct(get_class($this), $job, 1000);
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
		$fh = @fopen('./admin/s_site_plugin/upload/' . $this->_uploadFile, 'rb');
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
		if (file_exists("./admin/s_site_plugin/sql/" . $this->_job . ".install.class.php")) {
			$classname = "Install_" . $this->_job;

			include_once("./admin/s_site_plugin/sql/" . $this->_job . ".install.class.php");
			$installPlugin = new $classname();

			// this is currently the only type we support.
			if ($installPlugin->getInstallType() == 'Install_Table') {
				if (check_opendb_table($installPlugin->getInstallTable())) {
					if ($this->_batchlimit > 0) {
						$fh = @fopen('./admin/s_site_plugin/upload/' . $this->_uploadFile, 'rb');
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
}
?>
