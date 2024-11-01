<?php

namespace WPConnectr\PowerResource\User;

defined('ABSPATH') || exit;

use WP_REST_Users_Controller;
use WPConnectr\API\API;
use WPConnectr\Controller\ControllerListenerTrait;
use WPConnectr\Controller\NoMixedTypeOutputTrait;
use WPConnectr\Controller\RestfulRoutesTrait;
use WPConnectr\Logger;
use WPConnectr\ResourceManager;
use WP_REST_Server;

class Controller extends WP_REST_Users_Controller {
	use RestfulRoutesTrait;
	use ControllerListenerTrait;
	use NoMixedTypeOutputTrait;

	protected $namespace;

	protected $logger;

	public function __construct( Logger $logger, ResourceManager $resourceManager ) {
		parent::__construct();

		$this->namespace       = API::REST_NAMESPACE;
		$this->logger          = $logger;
		$this->resourceManager = $resourceManager;
	}

	public function get_endpoint_args_for_delete() {
		return array(
			'force'    => array(
				'type'        => 'boolean',
				'default'     => true,
				'description' => __( 'Required to be true, as users do not support trashing.', 'wp-connectr' ),
			),
			'reassign' => array(
				'type'              => 'integer',
				'description'       => __( 'Reassign the deleted user\'s posts and links to this user ID.', 'wp-connectr' ),
				'required'          => false, // This is not required, the wp-core has it wrong.
				'sanitize_callback' => array( $this, 'check_reassign' ),
			),
		);
	}

	public function get_collection_params() {
		$query_params = parent::get_collection_params();

		// We want to specify all roles for the user in our schema
		$all_wp_roles = wp_roles()->get_names();

		$query_params['roles']['items']['enum'] = array_keys( $all_wp_roles );

		// Clean up has_publshed_posts
		$query_params['has_published_posts']['items']['enum'] = array_values( $query_params['has_published_posts']['items']['enum'] );

		return $query_params;
	}

	public function get_endpoint_args_for_item_schema( $method = WP_REST_Server::CREATABLE ) {
		$args = parent::get_endpoint_args_for_item_schema( $method );

		// We want to specify all roles and capabilities for the user in our schema
		$all_wp_roles = wp_roles()->get_names();

		$args['roles']['items']['enum'] = array_keys( $all_wp_roles );

		return $args;
	}
}
