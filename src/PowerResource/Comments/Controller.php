<?php

namespace WPConnectr\PowerResource\Comments;

defined('ABSPATH') || exit;

use WP_REST_Server;
use WP_REST_Comments_Controller;
use WPConnectr\API\API;
use WPConnectr\Controller\ControllerListenerTrait;
use WPConnectr\Controller\RestfulRoutesTrait;
use WPConnectr\Logger;
use WPConnectr\Controller\NoMixedTypeOutputTrait;

class Controller extends WP_REST_Comments_Controller {
	use NoMixedTypeOutputTrait;
	use RestfulRoutesTrait;
	use ControllerListenerTrait;

	protected $namespace;
	protected $logger;

	public function __construct() {
		parent::__construct();

		$this->logger    = Logger::get_instance();
		$this->namespace = API::REST_NAMESPACE;
		$this->rest_base = 'comments';
	}

	public function collection_methods() {
		return array(
			WP_REST_Server::READABLE,
			WP_REST_Server::CREATABLE,
		);
	}

	public function member_methods() {
		return array(
			WP_REST_Server::READABLE,
			'PATCH',
			WP_REST_Server::DELETABLE,
		);
	}

	public function get_endpoint_args_for_delete() {
		return array(
			'force' => array(
				'type'        => 'boolean',
				'default'     => false,
				'description' => __( 'Whether to bypass trash and force deletion.', 'wp-connectr' ),
			),
			'password' => array(
				'type'        => 'string',
				'description' => __( 'The password for the parent post of the comment (if the post is password protected).', 'wp-connectr' ),
			),
		);
	}
}
