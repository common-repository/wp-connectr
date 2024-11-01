<?php

namespace WPConnectr\PowerResource\Terms;

defined('ABSPATH') || exit;

use WP_REST_Server;
use WP_REST_Terms_Controller;
use WPConnectr\API\API;
use WPConnectr\Controller\ControllerListenerTrait;
use WPConnectr\Controller\RestfulRoutesTrait;
use WPConnectr\Logger;
use WPConnectr\Controller\NoMixedTypeOutputTrait;

class Controller extends WP_REST_Terms_Controller {
	use NoMixedTypeOutputTrait;
	use RestfulRoutesTrait;
	use ControllerListenerTrait;

	protected $namespace;
	protected $logger;

	public function __construct( $taxonomy ) {
		parent::__construct( $taxonomy );

		$this->namespace = API::REST_NAMESPACE;
		$this->logger    = Logger::get_instance();
	}

	public function member_methods() {
		return array(
			WP_REST_Server::READABLE,
			'PATCH',
			WP_REST_Server::DELETABLE,
		);
	}

	public function collection_methods() {
		return array(
			WP_REST_Server::READABLE,
			WP_REST_Server::CREATABLE,
		);
	}

	public function get_endpoint_args_for_delete() {
		return array(
			'force' => array(
				'type'        => 'boolean',
				'default'     => false,
				'description' => __( 'Required to be true, as terms do not support trashing.', 'wp-connectr' ),
			),
		);
	}
}
