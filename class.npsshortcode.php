<?php

	class NPSShortCode {
		
		private $atts;
		private $content;
		private $options;
	
		public function __construct($atts, $content) {
			$this->atts = $atts;
			$this->content = $content;
			$this->options = get_option('nps_options', nps_get_defaultvalues());
		}
		
		public function __destruct() {
		}
		
		// Return the whole form for display it on the screen
		public function get_display_content() {
			$retval = "";
			$retval .= '<div class="nps_shortcode_container" id="nps_shortcode_container">';
			// Display content of the widget
			$retval .= '<div class="nps_form_container">';
			// If content is set then display it
			if (strlen($this->content) > 0) $retval .= '<div class="nps_form_content">' . $this->content . '</div>';
			$retval .= $this->get_form_display();
			$retval .= '</div>'; // .nps_form_container
			$retval .= '</div>'; // .nps_shortcode_container
			return $retval;
		}
		
		// Generate the question form
		private function get_form_display() {
			$retval = "";
			if ($this->options['style'] == 4) {
				$retval .= '<style type="text/css">' . $this->options['custom'] . '</style>';
			} else {
				$retval .= '<link rel="stylesheet" href="' . NPS__PLUGIN_URL . 'styles/' . $this->options['style'] . '.css' . '" type="text/css" media="all" />';
			}
			$retval .= nps_getfilecontent("styles/form.data");
			$retval = str_replace(';;javascript;;', $this->get_formsubmit_javascript(), $retval);
			$retval = str_replace(';;ajaxurl;;', NPS_URL_WP_AJAX, $retval);
			$retval = str_replace(';;q1;;', $this->options['q1'], $retval);
			$retval = str_replace(';;a1;;', $this->get_form_answer(1), $retval);
			$retval = str_replace(';;formid;;', $this->get_form_id(), $retval);
			$retval = str_replace(';;b1;;', $this->options['btn'], $retval);
			return $retval;
		}
		
		private function get_formsubmit_javascript() {
			$retval = nps_getfilecontent("formsubmit.js");
			return $retval;
		}
		
		private function get_form_answer($atype) {
			$retval = "";
			switch ($atype) {
				default:
					$retval = __('Choose a value: ', 'nps_language_domain');
					$retval .= '<select id="nps_form_answer" name="nps_form_answer">';
					for ($i = 0; $i < 11; $i++) {
						$retval .= '<option value="' . $i . '">' . $i . '</option>';
					}
					$retval .= '</select>';
					break;
			}
			return $retval;
		}
		
		private function get_form_id($length = 10) {
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$charactersLength = strlen($characters);
			$randomString = '';
			for ($i = 0; $i < $length; $i++) {
				$randomString .= $characters[rand(0, $charactersLength - 1)];
			}
			return $randomString;
		}
	
	}
?>