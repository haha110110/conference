<?php
/**
 * Plugin Name: Conference Manager
 * Description: Mobile-first conference registration and check-in system with WeChat Pay.
 * Version: 1.0.0
 * Author: Gemini CLI
 * Text Domain: conf-manager
 * Domain Path: /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin constants
define( 'CONF_MANAGER_VERSION', '1.0.0' );
define( 'CONF_MANAGER_PATH', plugin_dir_path( __FILE__ ) );
define( 'CONF_MANAGER_URL', plugin_dir_url( __FILE__ ) );

require_once CONF_MANAGER_PATH . 'includes/class-conf-manager.php';

/**
 * Activation hook
 */
function activate_conf_manager() {
	// Register Staff role
	add_role(
		'conference_staff',
		__( 'Conference Staff', 'conf-manager' ),
		array(
			'read'         => true,
			'edit_posts'   => false,
			'delete_posts' => false,
		)
	);

	require_once CONF_MANAGER_PATH . 'includes/class-conf-db.php';
	$db = new Conf_DB();
	$db->create_tables();
}
register_activation_hook( __FILE__, 'activate_conf_manager' );

/**
 * Deactivation hook
 */
function deactivate_conf_manager() {
	// We don't remove the role or tables by default to avoid data loss
}
register_deactivation_hook( __FILE__, 'deactivate_conf_manager' );

/**
 * Initialize the plugin
 */
function run_conf_manager() {
	$plugin = new Conf_Manager();
	$plugin->run();
}
run_conf_manager();
