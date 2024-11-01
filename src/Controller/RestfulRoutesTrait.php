<?php

namespace WPConnectr\Controller;

use WP_REST_Server;

defined('ABSPATH') || exit;

trait RestfulRoutesTrait {
	/**
	 * Defines what methods are available on resource collections through this controller
	 *
	 * @var array
	 */
	public function collection_methods() {
		return array(
			WP_REST_Server::READABLE,
			WP_REST_Server::CREATABLE,
		);
	}


	/**
	 * Defines what methods are available on resource members
	 *
	 * @return array
	 */
	public function member_methods() {
		return array(
			WP_REST_Server::READABLE,
			'PATCH',
			WP_REST_Server::DELETABLE,
		);
	}

	/**
	 * Override this if your member methods use a different param name for 'id'
	 *
	 * @return string
	 */
	protected function get_member_method_id_param_name() {
		return apply_filters( "wp_connector_{$this->rest_base}_id_param_name", 'id' );
	}

	/**
	 * Override this if your member methods use a different regex for 'id'
	 *
	 * Expected format: (?P<id>[\\d]+)
	 *
	 * - Includes paratheses
	 * - Includes ?P<id_param_expected>
	 * - Fully valid regex
	 *
	 * @return string
	 */
	protected function get_member_method_regex() {
		return '(?P<' . $this->get_member_method_id_param_name() . '>[\\d]+)';
	}

	/**
	 * Requirements to use:
	 *
	 * $namespace     - rest namespace, e.g. "wp-connectr/v1"
	 * $rest_base - base for rest route, e.g. "coupons"
	 */

	/**
	 * Class using this trait must implement a get_path_params function.
	 *
	 * @return array Array describing required path params from original resource
	 */
	public function get_path_params() {
		return apply_filters( "wp_connector_{$this->rest_base}_path_params", array() );
	}

	/**
	 * Register the routes based on namespace and rest base
	 */
	public function register_routes() {
		// Collection Routes
		$collection_routes = array();
		if ( in_array(WP_REST_Server::READABLE, $this->collection_methods(), true)) {
			$collection_routes[] = array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => array_merge($this->get_path_params(),
																	$this->get_collection_params()),
			);

			// Power Automate doesn't allow dynamic schema for GET requests, so we make a POST here
			register_rest_route( $this->namespace, 'resources/' . $this->rest_base . '/query',
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'get_items_via_post_hack' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'schema'              => array( $this, 'get_public_collection_schema' ),
					'args'                => array_merge( $this->get_path_params(),
																								$this->get_collection_params()
																							),
				)
			);
		}

		if ( in_array(WP_REST_Server::CREATABLE, $this->collection_methods(), true)) {
			$collection_routes[] = array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'                => array_merge($this->get_path_params(),
					$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE )),
			);
		}

		$collection_routes['schema'] = array( $this, 'get_public_collection_schema' );

		register_rest_route( $this->namespace, 'resources/' . $this->rest_base, $collection_routes );

		// Member routes
		$member_routes = array();

		if (in_array(WP_REST_Server::READABLE, $this->member_methods(), true)) {
			$member_routes[] = array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => array_merge(
					$this->get_path_params(),
					array(
						'context' => $this->get_context_param(
							array( 'default' => 'view' )
						),
					)
				),
			);
		}

		$editable_methods = array( 'POST', 'PUT', 'PATCH' );
		if ( ! empty( array_intersect( $editable_methods, $this->member_methods() ) ) ) {
			$member_routes[] = array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'            => array_merge($this->get_path_params(),
															$this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE )),
			);
		}

		if (in_array(WP_REST_Server::DELETABLE, $this->member_methods(), true)) {
			$member_routes[] = array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				'args'                => array_merge(
					$this->get_path_params(),
					$this->get_endpoint_args_for_delete()
					),
			);
		}

		$member_routes['schema'] = array( $this, 'get_public_item_schema' );

		register_rest_route( $this->namespace, 'resources/' . $this->rest_base . "/{$this->get_member_method_regex()}", $member_routes);

		$this->register_additional_routes();
	}

	/**
	 * Overrides get_items to merge POST params into GET because of dynamic schema limitations
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_items_via_post_hack( $request ) {
		$post_params = $request->get_body_params();
		$get_params  = $request->get_query_params();

		$request->set_query_params( array_merge( $post_params, $get_params ) );

		return $this->get_items( $request );
	}

	public function get_endpoint_args_for_item_schema( $method = WP_REST_Server::CREATABLE ) {
		$endpoint_args = parent::get_endpoint_args_for_item_schema( $method );

		/**
		 * Filter the endpoint args for this resource.
		 *
		 * @param array $endpoint_args The endpoint args.
		 */
		return apply_filters( "wp_connector_{$this->rest_base}_endpoint_args", $endpoint_args );
	}

	/**
	 * Specifies any arguments for the DELETE method.
	 * Override if you want to allow any arguments here.
	 *
	 * @return array
	 */
	public function get_endpoint_args_for_delete() {
		return apply_filters( "wp_connector_{$this->rest_base}_delete_args", array() );
	}

	/**
	 * Implement this method in your class to register any additional routes you might need
	 *
	 * @return void
	 */
	public function register_additional_routes() {
		do_action( "wp_connector_{$this->rest_base}_register_additional_routes", $this );
	}

	/**
	 * Override this if you need to present a different schema for collections than the members,
	 * make sure to apply the filter.
	 *
	 * @return array
	 */
	public function get_public_collection_schema() {
		$collection_schema = $this->get_public_item_schema();

		/**
		 * Filter the collection schema for this resource.
		 *
		 * @param array $collection_schema The schema for the collection.
		 */
		return apply_filters( "wp_connector_{$this->rest_base}_collection_schema", $collection_schema );
	}

	public function schema_path() {
		return apply_filters( "wp_connector_{$this->rest_base}_schema_path", $this->rest_base );
	}

}
