<?php
	
	class NPSResults {
		
		public function __construct() {
		}
		
		public function __destruct() {
		}
		
		// Handle the ajax call - insert the answers to the database
		public function getResultPageContent() {
			$retval = "";
			$data = $this->getAllSubmittedData();
			if (count($data) == 0) {
				$retval = __('No answers yet.', 'nps_language_domain');
			} else if ($data[0]->error != 1) {
				
				$counter = 0; // total answers
				$final_value = 0; // final score
				$detractors = 0; // score 0-6
				$passives = 0; // score 7-8
				$promoters = 0; // score 9-10
				foreach ( $data as $dbrow ) {
					$counter++;
					if ($dbrow->nps_value < 7) $detractors++;
					else if ($dbrow->nps_value < 9) $passives++;
					else $promoters++;
				}
				$final_value = (($promoters - $detractors) / $counter) * 100;
				$retval = $this->getFormattedResultPage($final_value, $detractors, $passives, $promoters, $counter);
			} else {
				$retval = __('Error occurs while get results. Try it later', 'nps_language_domain');
			}
			return $retval;
		}
		
		private function getFormattedResultPage($final_value, $detractors, $passives, $promoters, $counter) {
			$retval = "";
			$retval = nps_getfilecontent("resultpage.data");
			$retval = str_replace(";;detractors;;", $detractors, $retval);
			$retval = str_replace(";;passives;;", $passives, $retval);
			$retval = str_replace(";;promoters;;", $promoters, $retval);
			$retval = str_replace(";;totalanswers;;", $counter, $retval);
			$retval = str_replace(";;final_value;;", $final_value, $retval);
			$retval = str_replace(";;table_h1;;", __('Detractors (score 0-6)', 'nps_language_domain'), $retval);
			$retval = str_replace(";;table_h2;;", __('Passives (score 7-8)', 'nps_language_domain'), $retval);
			$retval = str_replace(";;table_h3;;", __('Promoters (score 9-10)', 'nps_language_domain'), $retval);
			$retval = str_replace(";;table_h4;;", __('Total answers', 'nps_language_domain'), $retval);
			$retval = str_replace(";;final_desc;;", __('Your Final Score is', 'nps_language_domain'), $retval);
			$retval = str_replace(";;btntext;;", __('Reset Answers', 'nps_language_domain'), $retval);
			$retval = str_replace(";;popup_title;;", __('WARNING', 'nps_language_domain'), $retval);
			$retval = str_replace(";;popup_desc;;", __('If you click on the Reset button, you will erase all the answers from the database. You can\'t revert this action.', 'nps_language_domain'), $retval);
			$retval = str_replace(";;popup_btn;;", __('Reset', 'nps_language_domain'), $retval);
			$retval = str_replace(";;ajaxurl;;", __('Reset', 'nps_language_domain'), $retval);
			return $retval;
		}
		
		// Get all lines from the database table
		private function getAllSubmittedData() {
			global $wpdb;
			$retval = array(new stdClass());
			$retval[0]->error = 0;
			$table_name = $wpdb->prefix . NPS__SUBMIT_TABLE_NAME;
			$res = $wpdb->get_results("SELECT * FROM $table_name WHERE 1");
			if (strlen($wpdb->last_error) > 0) $retval[0]->error = 1;
			else $retval = $res;
			return $retval;
		}
		
	}
?>