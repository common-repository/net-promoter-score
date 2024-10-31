<?php
	/*
		Plugin Name: Loyalty Score Calculator
		Plugin URI: http://marketingv8.wordpress.com
		Description: Use short codes to insert Loyalty Score Calculator to your posts, pages or any widget area. Easy to use: select your language and pick a style.
		Version: 1.0.2
		Text Domain: nps_language_domain
		Domain Path: /languages/ 
		Author: Marketing V8
		Author URI: http://marketingv8.wordpress.com
		License: GPL2
	*/
	
	// Global constants
	define( 'NPS_VERSION', '1.0.1' );
	define( 'NPS__MINIMUM_WP_VERSION', '4.0' );
	define( 'NPS__SUBMIT_TABLE_NAME', 'nps_submitted' );
	define( 'NPS__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	define( 'NPS__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	if (!defined('NPS_URL_WP_AJAX')) {
		if ($_SERVER['SERVER_PORT'] == 80)
			define('NPS_URL_WP_AJAX', admin_url('admin-ajax.php', 'http'));
		else if ($_SERVER['SERVER_PORT'] == 443)
			define('NPS_URL_WP_AJAX', admin_url('admin-ajax.php', 'https'));
		else
			define('NPS_URL_WP_AJAX', admin_url('admin-ajax.php'));
	} 
	
	// Multi language support
	load_plugin_textdomain('nps_language_domain', false, basename( dirname( __FILE__ ) ) . '/languages');
	 
	// Run the install scripts upon plugin activation
	function nps_install() {
		global $wpdb;
		$table_name = $wpdb->prefix . NPS__SUBMIT_TABLE_NAME;
		if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
			$charset_collate = $wpdb->get_charset_collate();
			$sql = "CREATE TABLE $table_name (
				`id` varchar(36) NOT NULL,
				`submit_time` int(11) NOT NULL,
				`nps_value` tinyint(4) NOT NULL,
				PRIMARY KEY (`id`)
			) $charset_collate;";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}
	}
	register_activation_hook(__FILE__,'nps_install');
	
	// Admin page init
	if( is_admin() ) {
		require_once( NPS__PLUGIN_DIR . 'class.npssettingspage.php' ); 
		$my_settings_page = new NPSSettingsPage();
	}
		
	// Admin JavaScript file
	function nps_admin_scripts_import($hook) {
		if ('settings_page_nps_plugin_options' == $hook) {
			wp_enqueue_script('nps_custom_script', NPS__PLUGIN_URL . 'scripts.js');
		}
	}
	add_action( 'admin_enqueue_scripts', 'nps_admin_scripts_import' );
	
	// Front-End Javascript file
	function nps_fe_scripts_import($hook) {
		wp_enqueue_script('json2');
		wp_enqueue_script('jquery');
	}
	add_action( 'wp_enqueue_scripts', 'nps_fe_scripts_import' );
	
	// Ajax actions
	add_action('wp_ajax_nps_ajaxActions', 'nps_ajaxActions');
	add_action('wp_ajax_nopriv_nps_ajaxActions', 'nps_ajaxActions');
		
	// Shortcode
	add_shortcode('nps_calculator', 'nps_shortcode_handler');
	
	// Manage Reset Table functionality on admin pages
	if (isset($_GET) && !empty($_GET) && is_admin() && $_GET['page'] == "nps_plugin_options") {
		require_once( NPS__PLUGIN_DIR . 'class.resetdata.php' );
		$ajaxcall = new NPSResetData($_GET);
		$ajaxcall->doResetData();
	}
	
	function nps_shortcode_handler($atts, $content = null) {
		$retval = "";
		// User custom content
		if (!is_null($content)) $mycontent = do_shortcode($content);
		else $mycontent = "";
		// Generate shortcode
		require_once( NPS__PLUGIN_DIR . 'class.npsshortcode.php' );
		$generator = new NPSShortCode(array(), $mycontent);
		$retval = $generator->get_display_content();
		return $retval;
	}
	
	// Run ajax submit action
	function nps_ajaxActions() {
		if (!empty($_POST) && isset($_POST['action']) && $_POST['action'] == 'nps_ajaxActions') {
			parse_str($_POST['form_data'], $fd);
			require_once( NPS__PLUGIN_DIR . 'class.formsubmit.php' );
			$ajaxcall = new NPSAjaxFormSubmit($fd);
			$res = $ajaxcall->ajax_submit($fd);
			echo $res['msg'];
			$ajaxcall = null;
		}
		exit;
	}
		
	// Get default settings
	function nps_get_defaultvalues() {
		$retval = array();
		$options = get_option( 'nps_options', array() );
		if (!isset($options['q1'])) {
			$retval['q1'] = __('Would you recommend us to a friend?', 'nps_language_domain');
		} else {
			$retval['q1'] = $options['q1'];
		}
		if (!isset($options['btn'])) {
			$retval['btn'] = __('Submit', 'nps_language_domain');
		} else {
			$retval['btn'] = $options['btn'];
		}
		if (!isset($options['style'])) {
			$retval['style'] = 1;
		} else {
			$retval['style'] = $options['style'];
		}
		if (!isset($options['custom'])) {
			$retval['custom'] = nps_get_defaultcustomstyle();
		} else {
			$retval['custom'] = $options['custom'];
		}
		return $retval;
	}
	
	// Get the default values for the custom style textarea
	function nps_get_defaultcustomstyle() {
		$retval = "";
		$retval = nps_getfilecontent("styles/custom.css");
		return $retval;
	}
	
	// Get file content
	function nps_getfilecontent($filename) {
		$retval = "";
		ob_start();		
		include $filename;
		$retval = ob_get_contents();
		ob_end_clean();
		return $retval;
	}
	
	// Sanitizer for css input
	function nps_sanitize_css_field($cssinput) {
		$retval = $cssinput;
		$retval = wp_filter_nohtml_kses($retval);
		return $retval;
	}
?>