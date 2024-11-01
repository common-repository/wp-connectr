<?php

namespace WPConnectr;

use WPConnectr\Logger;

defined( 'ABSPATH' ) || exit;

/**
 * WPConnectr Installer.
 * Responsible for installing the plugin when first activated,
 * and also responsible for managing database upgrades that occur
 * when users update the plugin to a new version.
 *
 */
class Installer {

	/**
	 * Database version (used for install/upgrade tasks if required).
	 */
	const DB_VERSION = 1;

	/**
	 * Name of the wp_option record that stores the installed version number.
	 */
	const DB_VERSION_OPTION_NAME = 'wp_connector_version';

	/**
	 * Logger instance.
	 *
	 * @var Logger
	 */
	protected $logger;

	/**
	 * Installer constructor.
	 *
	 * @param Logger $logger The Logger.
	 */
	public function __construct( Logger $logger ) {
		$this->logger = $logger;
	}

	/**
	 * Instructs the installer to initialise itself.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_init', array( $this, 'install_or_update' ), 100 );

		// Register the activation hook.
		\register_activation_hook( \WP_CONNECTR_PLUGIN_FILE, array( $this, 'activate' ) );

		// Register the deactivation hook.
		\register_deactivation_hook( \WP_CONNECTR_PLUGIN_FILE, array( $this, 'deactivate' ) );
	}

	/**
	 * Get the currently installed database version number.
	 *
	 * @return int
	 */
	public function get_db_version() {
		return intval( get_option( self::DB_VERSION_OPTION_NAME ) );
	}

	/**
	 * Set the installed database version number to the specified version number.
	 *
	 * @param int $version Optional database version number, defaults to the newest version number if not specified.
	 *
	 * @return void
	 */
	public function set_db_version( $version = self::DB_VERSION ) {
		update_option( self::DB_VERSION_OPTION_NAME, $version );
		$this->logger->info( 'Database version set to %d.', array( $this->get_db_version() ) );
	}

	/**
	 * Whether or not the installed database version is up to date.
	 *
	 * @return bool
	 */
	public function is_up_to_date() {
		return self::DB_VERSION === $this->get_db_version();
	}

	/**
	 * Install the plugin if required, and perform database upgrade routines if required.
	 * Executed on every admin/dashboard page load (via the `admin_init` hook).
	 * As per http://core.trac.wordpress.org/ticket/14170 it's far better to use
	 * an upgrade routine fired on `admin_init`.
	 *
	 * @return void
	 */
	public function install_or_update() {
		$installed_version = $this->get_db_version();
		if ( self::DB_VERSION === $installed_version ) {
			// Database version already up-to-date -> nothing to do.
			return;
		}

		if ( 0 === $installed_version ) {
			// Initial plugin installation/activation, or a user has deactivated and reactivated the plugin.
			$installed_version = 1;
			$this->logger->info( 'New installation or reactivation. Database version set to 1.' );
			do_action('wp_connector_db_initial_migration');
		}

		if ( 1 === self::DB_VERSION) {
			// On first launch we don't need db upgrades, but we will have built in support
			$this->set_db_version(1);
			return;
		}

		$this->logger->info(
			'Database upgrade from version %d to %d starting...',
			array( $installed_version, self::DB_VERSION )
		);

		foreach ( range( 1, self::DB_VERSION - 1 ) as $start ) {
			if ( $start === $installed_version ) {
				$next = $start + 1;
				/**
				 * Perform Database Upgrade Tasks to migrate to the next DB version.
				 *
				 * @internal
				 */
				do_action( "wp_connector_db_upgrade_v_{$start}_to_{$next}" );
				$installed_version++;
				$this->set_db_version( $installed_version );
			}
		}

		// Database upgrade routines complete. Update installed version.
		$this->logger->info( 'Database upgrade completed.' );
	}

	/**
	 * Plugin activation tasks.
	 * Executed whenever the plugin is activated.
	 *
	 * @return void
	 */
	public function activate() {
		$this->logger->info( 'Plugin activation started.' );

		// Install Capabilities
		$capabilities = new Capabilities();
		$capabilities->install_roles();

		/**
		 * Perform tasks when the plugin is activated.
		 *
		 * @internal
		 * @since 2.0.0
		 */
		do_action( 'wp_connector_plugin_activate' );
		$this->logger->info( 'Plugin activation completed.' );
	}


	/**
	 * Plugin deactivation tasks.
	 * Executed whenever the plugin is deactivated.
	 * NOTE: deactivation is not the same as deletion or uninstall, as a user may temporarily deactivate
	 * the plugin and then activate it again so no data should be deleted here.
	 *
	 * @return void
	 */
	public function deactivate() {
		$this->logger->info( 'Plugin deactivation started.' );

		// Re-set the database version so that if the user reactivates the plugin, then our installer is run again.
		$this->set_db_version( 0 );

		$capabilities = new Capabilities();
		$capabilities->remove_roles();

		/**
		 * Perform tasks when the plugin is deactivated.
		 *
		 * @internal
		 * @since 2.0.0
		 */
		do_action( 'wp_connector_plugin_deactivate' );
		$this->logger->info( 'Plugin deactivation completed.' );
	}
}
