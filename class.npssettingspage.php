<?php

	class NPSSettingsPage {
		private $options;
		private $list_data;
		private $general_settings_key = 'nps_questions_settings';
		private $result_settings_key = 'nps_result_settings';
		private $plugin_options_key = 'nps_plugin_options';
		private $plugin_settings_tabs = array();
		
		function __construct() {
			add_action( 'init', array( $this, 'load_settings' ) );
			add_action( 'admin_init', array( $this, 'register_general_settings' ) );
			add_action( 'admin_init', array( $this, 'register_result_settings' ) );
			add_action( 'admin_menu', array( $this, 'add_admin_menus' ) );
		}
		
		// Load settings from database
		function load_settings() {
			$this->options = get_option('nps_options', nps_get_defaultvalues());
			if (empty($this->options) || 
				 (!empty($this->options) && (!isset($this->options['style']) || empty($this->options['style']))) || 
				 (!empty($this->options) && (!isset($this->options['q1']) || empty($this->options['q1']))) || 
				 (!empty($this->options) && (!isset($this->options['btn']) || empty($this->options['btn'])))) add_action( 'admin_notices', array( $this, 'nps_admin_notices') );
		}
		
		// Show a notice: Some settings are wrong - Loyalty Score Calculator will not work properly
		function nps_admin_notices() {
			echo "<div id='notice' class='updated fade'><p>" . 
				__('Some settings are wrong - Loyalty Score Calculator will not work properly. Please fix the settings.', 'nps_language_domain') . 
				" <a href='" . admin_url( 'options-general.php?page=nps_plugin_options' ) . "'>" . 
				__("Loyalty Score Calculator Options", "nps_language_domain") . "</a></p></div>";
		}
		
		// Add general settings page
		function register_general_settings() {
			$this->plugin_settings_tabs[$this->general_settings_key] = __("General settings", "nps_language_domain");
			
			register_setting($this->general_settings_key, "nps_options", array($this, 'sanitize'));
			add_settings_section( 'section_general', __('General Plugin Settings', 'nps_language_domain'), array( $this, 'section_general_desc' ), $this->general_settings_key );
			add_settings_field( 'q1', __('The ultimate question', 'nps_language_domain'), array( $this, 'field_general_option_q1' ), $this->general_settings_key, 'section_general' );
			add_settings_field( 'btn', __('The button text', 'nps_language_domain'), array( $this, 'field_general_option_btn' ), $this->general_settings_key, 'section_general' );
			add_settings_field( 'style', __('The selected style', 'nps_language_domain'), array( $this, 'field_general_option_style' ), $this->general_settings_key, 'section_general' );
			add_settings_field( 'custom', __('The custom style', 'nps_language_domain'), array( $this, 'field_general_option_custom' ), $this->general_settings_key, 'section_general' );
		}
		
		// Add submitted data result page
		function register_result_settings() {
			$this->plugin_settings_tabs[$this->result_settings_key] = __('Submitted data results', 'nps_language_domain');
			
			register_setting( $this->result_settings_key, "nps_options", array($this, 'sanitize'));
			add_settings_section( 'section_result', __('Results', 'nps_language_domain'), array( $this, 'section_result_desc' ), $this->result_settings_key );
			add_settings_field( 'result_option', __('Results of the questionnaire', 'nps_language_domain'), array( $this, 'field_result_option' ), $this->result_settings_key, 'section_result' );
		}
				
		// Sanitize each settings field if needed
		public function sanitize( $input ) {
			$new_input = array();
			$temp = nps_get_defaultvalues();
			$new_input['style'] = $temp['style'];
			$new_input['q1'] = $temp['q1'];
			$new_input['btn'] = $temp['btn'];
			$new_input['custom'] = $temp['custom'];
			if (isset($input['inputtype'])) {
				switch ($input['inputtype']) {
					case 'general':
						if (isset($input['q1']) && isset($input['btn'])) {
							$new_input['q1'] = sanitize_text_field($input['q1']);
							$new_input['btn'] = sanitize_text_field($input['btn']);
							$new_input['style'] = sanitize_text_field($input['style']);
							$new_input['custom'] = nps_sanitize_css_field($input['custom']);
						}
						break;
				}
			}
			return $new_input;
		}
		
		// Description of the tab pages
		function section_general_desc() { echo __('General settings for Loyalty Score Calculator. Use this shortcode anywhere in your site to display the form:', 'nps_language_domain') . " <b>[nps_calculator]</b>"; }
		function section_result_desc() { echo __('In this page you can show the result of the question - and reset the submitted data. The table represents the total number of the answers in each groups. The final score is your NPS score (Net Promoter Score).', 'nps_language_domain'); }
		
		// General fields callback, renders inputs
		function field_general_option_q1() {
			printf(
				'<input type="text" id="q1" size="40" name="nps_options[q1]" value="%s" /><br/>' . __("This question will be display for the visitors", "nps_language_domain"),
				isset( $this->options['q1'] ) ? esc_attr( $this->options['q1']) : ''
			);
			printf('<input type="hidden" id="nps_admin_designpath" value="' . NPS__PLUGIN_URL . '"/>');
		}
		
		function field_general_option_btn() {
			printf(
				'<input type="text" id="btn" name="nps_options[btn]" value="%s" /><br/>' . __("This text will be display on the form submit button", "nps_language_domain"),
				isset( $this->options['btn'] ) ? esc_attr( $this->options['btn']) : ''
			);
		}
		
		function field_general_option_custom() {
			printf(
				'<textarea rows="4" cols="50" id="custom" name="nps_options[custom]">%s</textarea><br/>' . __("If you choose custom design above, this css settings will be used", "nps_language_domain"),
				isset( $this->options['custom'] ) ? esc_attr( $this->options['custom']) : ''
			);
		}
		
		function field_result_option() {
			require_once( NPS__PLUGIN_DIR . 'class.results.php' );
			add_thickbox();
			$myres = new NPSResults();
			echo $myres->getResultPageContent();
		}
		
		function field_general_option_style() {
			$retval = '<select id="style" name="nps_options[style]" onchange="nps_admin_select_desgin(this);">';
			$selval = isset( $this->options['style'] ) ? esc_attr( $this->options['style']) : '1';
			$sel = ' selected="selected" ';
			$selimg = '<img src="' . NPS__PLUGIN_URL . 'styles/previews/' . $selval . '_preview.png"/>';
			if ($selval == 1) $retval .= '<option value="1"' . $sel . '>Elegant</option>';
			else $retval .= '<option value="1">Elegant</option>';
			if ($selval == 2) $retval .= '<option value="2"' . $sel . '>Creative</option>';
			else $retval .= '<option value="2">Creative</option>';
			if ($selval == 3) $retval .= '<option value="3"' . $sel . '>Modern</option>';
			else $retval .= '<option value="3">Modern</option>';
			if ($selval == 4) $retval .= '<option value="4"' . $sel . '>Custom</option>';
			else $retval .= '<option value="4">Custom</option>';
			$retval .= '</select>';
			printf(
				$retval . '<br/>' . __("The selected style. If you choose 'custom' the css style below will be used. Preview:", "nps_language_domain") . '<br/><div id="nps_admin_selecteddesign">%s</div>',
				$selimg
			);
		}
		
		// Add an options page under Settings
		function add_admin_menus() {
			$hook_suffix = add_options_page(
				__('Loyalty Score Calculator Options', 'nps_language_domain'), 
				__('Loyalty Score Calculator', 'nps_language_domain'), 
				'manage_options', 
				$this->plugin_options_key, 
				array($this, 'plugin_options_page')
			);
			add_action( 'load-' . $hook_suffix , array( $this, 'nps_load_function' ) );
		}
		
		public function nps_load_function() {
			// Current admin page is the options page for our plugin, so do not display the notice
			// (remove the action responsible for this)
			remove_action( 'admin_notices', array( $this, 'nps_admin_notices') );
		}
		
		// Plugin Options page rendering
		function plugin_options_page() {
			$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->general_settings_key;
			$this->options = get_option('nps_options', nps_get_defaultvalues());
			if (!current_user_can('manage_options'))	{
				wp_die( __('You do not have sufficient permissions to access this page.', 'nps_language_domain') );
			}
			?>
			<div class="wrap">
				<h2>
					<?php
						echo '<img src="' . NPS__PLUGIN_URL . '/logo.png" alt="' . __('Loyalty Score Calculator Plugin Logo', 'nps_language_domain') . '"/>&nbsp;';
						echo __('Loyalty Score Calculator Plugin Settings', 'nps_language_domain');
					?>
				</h2>
				<?php $this->plugin_options_tabs(); ?>
				<form method="post" action="options.php?valami=1" name="nps_adminsettings_form">
					<input type="hidden" name="nps_options[inputtype]" value="general"/>
					<?php wp_nonce_field( 'update-options' ); ?>
					<?php settings_fields( $tab ); ?>
					<?php do_settings_sections( $tab ); ?>
					<?php if ($tab != $this->result_settings_key) submit_button(); ?>
				</form>
			</div>
			<?php
		}
				
		// Renders our tabs in the plugin options page
		function plugin_options_tabs() {
			$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->general_settings_key;
			echo '<h2 class="nav-tab-wrapper">';
			foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
				$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
				echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->plugin_options_key . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';	
			}
			echo '</h2>';
		}
			
}