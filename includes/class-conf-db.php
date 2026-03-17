<?php
/**
 * DB management class
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Conf_DB {

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;
		$this->table_attendees    = $wpdb->prefix . 'conf_attendees';
		$this->table_transactions = $wpdb->prefix . 'conf_transactions';
	}

	/**
	 * Create tables
	 */
	public function create_tables() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql_attendees = "CREATE TABLE $this->table_attendees (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			order_id bigint(20) NOT NULL,
			name varchar(255) NOT NULL,
			phone varchar(50) NOT NULL,
			company varchar(255) DEFAULT '',
			job_title varchar(255) DEFAULT '',
			six_digit_code varchar(6) NOT NULL,
			qr_code_url text DEFAULT '',
			checkin_status varchar(50) DEFAULT 'unconfirmed',
			material_status varchar(50) DEFAULT 'not_distributed',
			refund_status varchar(50) DEFAULT 'none',
			refund_time datetime DEFAULT NULL,
			checkin_time datetime DEFAULT NULL,
			material_time datetime DEFAULT NULL,
			staff_id bigint(20) DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY order_id (order_id),
			KEY six_digit_code (six_digit_code),
			KEY refund_status (refund_status)
		) $charset_collate;";

		$sql_transactions = "CREATE TABLE $this->table_transactions (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			order_id bigint(20) NOT NULL,
			attendee_id bigint(20) DEFAULT NULL,
			type varchar(50) NOT NULL,
			amount decimal(10,2) NOT NULL,
			transaction_id varchar(100) NOT NULL,
			staff_id bigint(20) DEFAULT NULL,
			log_time datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY order_id (order_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql_attendees );
		dbDelta( $sql_transactions );
	}
}
