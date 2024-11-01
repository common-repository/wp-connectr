<?php

namespace WPConnectr\Controller;

defined('ABSPATH') || exit;

use WP_REST_Controller;
use WPConnectr\API\API;
use WPConnectr\ResourceManager;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class Resources extends WP_REST_Controller {
	protected $namespace = API::REST_NAMESPACE;
	protected $resource_manager;

	public function __construct( ResourceManager $resource_manager ) {
		$this->resource_manager = $resource_manager;
	}

	public function register_routes() {
		register_rest_route(
		$this->namespace,
		'resources/', array(
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_items' ),
				'permission_callback' => function( WP_REST_Request $request ) {
					if ( current_user_can( 'read' ) ) {
						return true;
					} else {
						return new WP_Error( 'rest_api_resource_get_resources',
						__( 'You are not allowed to read resources.', 'wp-connectr' ),
						array( 'status' => 403 )
						);
					}
				},
				'args' => array(
						'member_method' => array(
							'type' => 'string',
							'description' => __( 'Limit response to resources that support this member method', 'wp-connectr' ),
							'enum' => array( 'PATCH', WP_REST_Server::READABLE, WP_REST_Server::DELETABLE, 'get', 'delete', 'patch' ),
						),
						'collection_method' => array(
							'type' => 'string',
							'description' => __( 'Limit response to resources that support this collection method', 'wp-connectr' ),
							'enum' => array( WP_REST_Server::CREATABLE, WP_REST_Server::READABLE, 'get', 'post' ),
						)
					),
				'schema' => array( $this, 'get_public_item_schema'),
			),
		) );
	}

	/**
	 * Get the resource schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'resource',
			'type'       => 'array',
			'items'      => array(
				'type' => 'object',
				'properties' => array(
					'key'  => array(
						'description' => __( 'The resource topic', 'wp-connectr' ),
						'type'        => 'string',
						'context'     => array( 'view' ),
					),
					'name' => array(
						'description' => __( 'The resource name', 'wp-connectr' ),
						'type'        => 'string',
						'context'     => array( 'view' ),
					),
					'resource' => array(
						'type'        => 'string',
						'description' => __('The path where we can fetch the schema', 'wp-connectr'),
					),
					'member_methods' => array(
						'type' => 'array',
						'description' => __('The methods that can be used on a member of this resource', 'wp-connectr'),
						'items' => array(
							'type' => 'string',
							'enum' => array( 'POST', 'PUT', 'PATCH', WP_REST_Server::READABLE, WP_REST_Server::EDITABLE, WP_REST_Server::DELETABLE ),
						)
					),
					'collection_methods' => array(
						'type' => 'array',
						'description' => __('The methods that can be used on a collection of this resource', 'wp-connectr'),
						'items' => array(
							'type' => 'string',
							'enum' => array( WP_REST_Server::CREATABLE, WP_REST_Server::READABLE ),
						)
					)
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Get all resources
	 *
	 * @param WP_REST_Request $request The incoming request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$response = array();
		$member_method = $request->get_param( 'member_method' );
		$collection_method = $request->get_param( 'collection_method' );

		$controllers = $this->resource_manager->get_enabled_sorted_controllers();

		foreach ($controllers as $handle) {
			// We can't support regex in the schema path in Power Automate, so we need to skip advertising those resources.
			if ( preg_match( '/\[.*\]/', $handle['controller']->schema_path() ) ) {
				continue;
			}

			$supported_member_methods = method_exists( $handle['controller'], 'member_methods' ) ? $handle['controller']->member_methods() : array();
			$supported_collection_methods = method_exists( $handle['controller'], 'collection_methods' ) ? $handle['controller']->collection_methods() : array();

			$resource = array(
				'key'                => $handle[ 'key' ],
				'name'               => "${handle[ 'name' ]} (${handle[ 'key' ]})",
				'resource'           => $handle['controller']->schema_path(),
				'member_methods'     => $supported_member_methods,
				'collection_methods' => $supported_collection_methods,
			);

			if ( $this->controller_provides( $handle['controller'], $collection_method, $member_method ) ) {
				$response[] = $resource;
			}
		}

		return rest_ensure_response( $response );
	}

	protected function controller_provides( $controller, $collection_method, $member_method ) {
		// If we don't have a collection_method or member_method, we can't check if the resource supports it
		if ( empty( $collection_method ) && empty( $member_method ) ) {
			return true;
		}

		// If the controller doesn't define its capabilities, we can't check if the resource supports it
		if ( ! method_exists( $controller, 'member_methods' ) || ! method_exists( $controller, 'collection_methods' ) ) {
			return true;
		}

		$member_methods = $controller->member_methods();
		$collection_methods = $controller->collection_methods();

		// $member_methods comes in as 'GET', 'POST', 'PUT', 'PATCH', or 'DELETE'
		// We need to match this to WP_REST_Server::READABLE, WP_REST_Server::CREATABLE, etc.
		$method = isset( $member_method ) && strtoupper( $member_method );
		switch ( $method ) {
			case 'GET':
				$method = WP_REST_Server::READABLE;
				break;
			case 'POST':
			case 'PUT':
			case 'PATCH':
				$method = 'PATCH';
				break;
			case 'DELETE':
				$method = WP_REST_Server::DELETABLE;
				break;
			default:
				$method = 'noop';
				break;
		}

		if ( ( in_array( $method, $member_methods ) ) ||
		     ( isset( $collection_method ) && in_array( strtoupper( $collection_method ), $collection_methods ) ) ) {
			return true;
		}

		return false;
	}
}
