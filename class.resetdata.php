<?php

	class NPSResetData {
		
		private $myget;
			
		public function __construct($myget) {
			$this->myget = $myget;
		}
		
		public function __destruct() {
			$this->myget = null;
		}
		
		// Handle the ajax call - insert the answers to the database
		public function doResetData() {
			$retval = "";
			if ($this->isValidPage()) {
				global $wpdb;
				$retval = __('Reset Complete', 'nps_language_domain');
				$table_name = $wpdb->prefix . NPS__SUBMIT_TABLE_NAME;
				$res = $wpdb->get_results("TRUNCATE $table_name");
				if (strlen($wpdb->last_error) > 0) $retval = __('Some error occurs, the reset not completed', 'nps_language_domain');
			}
			return $retval;
		}

		private function isValidPage() {
			return ($this->myget['page'] == "nps_plugin_options" && $this->myget['tab'] == "nps_result_settings" && $this->myget['settings-updated'] == "true");
		}
		
	}
?>