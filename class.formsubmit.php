<?php

	class NPSAjaxFormSubmit {
		
		private $formdata;
			
		public function __construct($formdata) {
			$this->formdata = $formdata;
		}
		
		public function __destruct() {
			$this->formdata = null;
		}
		
		// Handle the ajax call - insert the answers to the database
		public function ajax_submit() {
			$result = array();
			$result['msg'] = __('Thanks to rate us!', 'nps_language_domain');
			$res = $this->insertNewData();
			if ($res == 0) $result['msg'] = __('Some errors occured, please try again later!', 'nps_language_domain');
			return $result;
		}
		
		// Insert new line to the database
		private function insertNewData() {
			$retval = 1;
			global $wpdb;
			$table_name = $wpdb->prefix . NPS__SUBMIT_TABLE_NAME;
			$uuid = $this->getUUID();
			if (strlen($uuid) == 36) {
				$wpdb->insert(
					$table_name, 
					array(
						'id' => $uuid,
						'submit_time' => time(),
						'nps_value' => $this->formdata['nps_form_answer']
					),
					array( 
						'%s',
						'%d',
						'%d'
					)
				);
				if (strlen($wpdb->last_error) > 0) $retval = 0;
			} else {
				$retval = 0;
			}
			return $retval;
		}
		
		// Retval a new UUID or empty string
		private function getUUID() {
			global $wpdb;
			$retval = $wpdb->get_var("select uuid();");
			if (strlen($retval) != 36) $retval = "";
			return $retval;
		}
	}
?>