<?php

namespace WPConnectr\PowerResource\Taxonomies;

defined('ABSPATH') || exit;

use WP_REST_Server;
use WP_REST_Taxonomies_Controller;
use WPConnectr\API\API;
use WPConnectr\Controller\ControllerListenerTrait;
use WPConnectr\Controller\RestfulRoutesTrait;
use WPConnectr\Logger;
use WPConnectr\Controller\NoMixedTypeOutputTrait;

class Controller extends WP_REST_Taxonomies_Controller {
	use NoMixedTypeOutputTrait;
	use RestfulRoutesTrait;
	use ControllerListenerTrait;

	protected $namespace;
	protected $logger;

	public function __construct() {
		parent::__construct();

		$this->logger    = Logger::get_instance();
		$this->namespace = API::REST_NAMESPACE;
		$this->rest_base = 'taxonomies';
	}

	public function collection_methods() {
		return array(
			WP_REST_Server::READABLE,
		);
	}

	public function member_methods() {
		return array(
			WP_REST_Server::READABLE,
		);
	}

	protected function get_member_method_regex() {
		return '(?P<taxonomy>[\w-]+)';
	}

	public function get_items( $request ) {
		$response = parent::get_items( $request );

		$data = $response->get_data();
		$data = array_values( $data );
		$response->set_data( $data );

		return $response;
	}
}
