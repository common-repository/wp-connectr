<?php

namespace WPConnectr\PowerResource\Post;

defined('ABSPATH') || exit;

use WP_REST_Posts_Controller;
use WPConnectr\API\API;
use WPConnectr\Controller\ControllerListenerTrait;
use WPConnectr\Controller\RestfulRoutesTrait;
use WPConnectr\Logger;
use WPConnectr\Controller\NoMixedTypeOutputTrait;

class Controller extends WP_REST_Posts_Controller {
	use NoMixedTypeOutputTrait;
	use RestfulRoutesTrait;
	use ControllerListenerTrait;

	protected $namespace;
	protected $logger;

	public function __construct( $type ) {
		parent::__construct( $type );

		$this->namespace = API::REST_NAMESPACE;
		$this->logger    = Logger::get_instance();
	}

	public function get_endpoint_args_for_delete() {
		return array(
			'force' => array(
				'type'        => 'boolean',
				'default'     => false,
				'description' => __( 'Whether to bypass trash and force deletion.', 'wp-connectr' ),
			),
		);
	}
}
