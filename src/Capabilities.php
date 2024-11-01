<?php

namespace WPConnectr;

use WP_Roles;

defined('ABSPATH') || exit;

class Capabilities {
	public function register() {
		add_action('wp_connector_db_initial_migration', array($this, 'install_roles'));
		add_action('wp_connector_plugin_deactivate', array($this, 'remove_roles'));
	}

	public function get_capabilities() {
		$capabilities = array(
			'wp_connector_manage',
			'wp_connector_use_triggers',
			'wp_connector_use_actions',
		);

		return $capabilities;
	}

	public function install_roles() {
		global $wp_roles;

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		add_role(
			'wp_connector_user',
			'WP Connectr User',
			array(
				'list_users'             => true,
				'read'                   => true,
				'read'                   => true,
				'read_private_pages'     => true,
				'read_private_posts'     => true,
				'edit_posts'             => true,
				'edit_pages'             => true,
				'edit_published_posts'   => true,
				'edit_published_pages'   => true,
				'edit_private_pages'     => true,
				'edit_private_posts'     => true,
				'edit_others_posts'      => true,
				'edit_others_pages'      => true,
				'publish_posts'          => true,
				'publish_pages'          => true,
				'delete_posts'           => true,
				'delete_pages'           => true,
				'delete_private_pages'   => true,
				'delete_private_posts'   => true,
				'delete_published_pages' => true,
				'delete_published_posts' => true,
				'delete_others_posts'    => true,
				'delete_others_pages'    => true,
				'manage_categories'      => true,
				'manage_links'           => true,
				'moderate_comments'      => true,
				'upload_files'           => true,
				'export'                 => true,
				'import'                 => true,
				'list_users'             => true,
				'edit_theme_options'     => true,
			),
		);

		$caps = $this->get_capabilities();

		foreach ( $caps as $cap ) {
			$wp_roles->add_cap( 'administrator', $cap );
			$wp_roles->add_cap( 'wp_connector_user', $cap );
		}
	}

	public function remove_roles() {
		global $wp_roles;

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		$caps = $this->get_capabilities();

		foreach ( $caps as $cap ) {
			$wp_roles->remove_cap( 'wp_connector_user', $cap );
			$wp_roles->remove_cap( 'administrator', $cap );
		}

		remove_role( 'wp_connector_user' );
	}
}
