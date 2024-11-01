<?php

namespace WPConnectr\Trigger;

use WPConnectr\Logger;
use WPConnectr\Trigger\Trigger;

defined('ABSPATH') || exit;

/**
 * DataStore class.
 *
 * @since 1.0.0
 */
class DataStore {
	/**
	 * Logger
	 *
	 * @var Logger
	 */
	protected $logger;

	public static function get_instance() {
		static $instance = null;

		if ( null === $instance ) {
			$instance = new self( Logger::get_instance() );
		}

		return $instance;
	}

	/**
	 * Constructor
	 *
	 * @param Logger $logger Logger instance.
	 */
	public function __construct( Logger $logger ) {
		$this->logger = $logger;
	}

	public function register() {
		add_action( 'wp_connector_db_initial_migration', array( $this, 'create_table' ) );
		add_action( 'wp_connector_plugin_deactivate', array( $this, 'drop_table' ) );
	}

	public function get_table_name() {
		global $wpdb;

		return "{$wpdb->prefix}wp_connector_triggers";
	}

	public function create_table() {
		$table = "CREATE TABLE {$this->get_table_name()} (
			id BIGINT(20) NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			status VARCHAR(255) NOT NULL,
			topic VARCHAR(255) NOT NULL,
			url text NOT NULL,
			failure_count SMALLINT(10) UNSIGNED NOT NULL DEFAULT 0,
			trigger_count SMALLINT(10) UNSIGNED NOT NULL DEFAULT 0,
			last_triggered_at DATETIME DEFAULT NULL,
			user_id BIGINT(20) UNSIGNED NOT NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		)";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $table );

		$this->logger->info( 'Trigger table created' );

		do_action( 'wp_connector_trigger_table_created' );

		return true;
	}

	public function drop_table() {
		global $wpdb;

		$table_name = $this->get_table_name();

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( $wpdb->prepare( "DROP TABLE IF EXISTS %s", $table_name ) );

		$this->logger->info( 'Trigger table dropped' );

		do_action( 'wp_connector_trigger_table_dropped' );

		return true;
	}

	public function read( Trigger &$trigger ) {
		global $wpdb;

		$prepared_query = $wpdb->prepare( "SELECT * FROM  %s WHERE id = %d", $this->get_table_name(), $trigger->get_id() );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$data = $wpdb->get_row( $prepared_query, ARRAY_A );

		if ( is_array( $data ) ) {
			$trigger->set_props(
				array(
					'name'              => $data['name'],
					'status'            => $data['status'],
					'topic'             => $data['topic'],
					'url'               => $data['url'],
					'failure_count'     => $data['failure_count'],
					'trigger_count'     => $data['trigger_count'],
					'last_triggered_at' => $data['last_triggered_at'],
					'user_id'           => $data['user_id'],
					'pending'           => false,
				)
			);
			$trigger->set_object_read( true );

			do_action( 'wp_connector_trigger_read', $trigger );
		} else {
			throw new \Exception( 'Invalid trigger ID' );
		}
	}

	/**
	 * Update a trigger.
	 *
	 * @param Trigger $trigger Trigger instance.
	 */
	public function update( &$trigger ) {
		global $wpdb;

		$data = array(
			'name'              => $trigger->get_name(),
			'status'            => $trigger->get_status(),
			'topic'             => $trigger->get_topic(),
			'url'               => $trigger->get_url(),
			'failure_count'     => $trigger->get_failure_count(),
			'last_triggered_at' => $trigger->get_last_triggered_at(),
			'user_id'           => $trigger->get_user_id(),
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
			$this->get_table_name(),
			$data,
			array(
				'id' => $trigger->get_id(),
			)
		); // WPCS: DB call ok.

		$trigger->apply_changes();

		wp_cache_delete( $trigger->get_id(), 'triggers' );

		do_action( 'wp_connector_trigger_updated', $trigger->get_id() );
	}

	/**
	 * Remove a trigger from the database.
	 *
	 * @since 3.3.0
	 * @param Trigger $trigger Trigger instance.
	 */
	public function delete( &$trigger ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->delete(
			$this->get_table_name(),
			array(
				'id' => $trigger->get_id(),
			),
			array( '%d' )
		); // WPCS: cache ok, DB call ok.

		wp_cache_delete( $trigger->get_id(), 'triggers' );
		do_action( 'wp_connector_trigger_deleted', $trigger->get_id(), $trigger );
	}
}
