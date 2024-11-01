<?php

namespace WPConnectr\Controller;

use WPConnectr\API\API;
use WPConnectr\ResourceManager;
use WPConnectr\Trigger\Topics;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Controller;
use WP_Error;

class TriggerTopicsController extends WP_REST_Controller {
  protected $namespace = API::REST_NAMESPACE;
  protected $resource_manager;

	public function __construct( Topics $topics, ResourceManager $resource_manager) {
	  $this->topics           = $topics;
	  $this->resource_manager = $resource_manager;
	}

	public function register_routes() {
	  register_rest_route(
			$this->namespace,
			'trigger_topics/', array(
				array(
				'args' => array(
					'resource_type' => array(
						'description' => __('Resource type to fetch topics for', 'wp-connectr'),
						'type' => 'string',
						'required' => false,
						'enum' => $this->resource_manager->get_enabled_controller_keys()
					),
					'search' => array(
						'description' => __('Filter topics to contain this string', 'wp-connectr'),
						'type' => 'string',
						'required' => false
					)
				),
				'methods' => WP_REST_Server::READABLE,
				'callback' => array($this, 'get_items'),
				'permission_callback' => function( WP_REST_Request $request ) {
						if ( current_user_can( 'wp_connector_use_triggers' ) ) {
							return true;
						} else {
							return new WP_Error( 'wp_connector_rest_cannot_read',
							__( 'You are not allowed to read trigger topics', 'wp-connectr' ),
							array( 'status' => 403 )
							);
						}
					},
				'schema' => array( $this, 'get_public_item_schema')
				)
			)
		);
	}

  /**
	 * Get the Trigger Topic's schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'trigger_topic',
			'type'       => 'object',
			'properties' => array(
				'key'  => array(
					'description' => __( 'The unique key (identifier) for the trigger topic.', 'wp-connectr' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
				'name' => array(
					'description' => __( 'A friendly name for the trigger topic.', 'wp-connectr' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Get all trigger topics.
	 *
	 * @param WP_REST_Request $request The incoming request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {

		$resource_type = $request['resource_type'];
		$response      = array();

		foreach ( $this->topics->get_topics() as $topic_key => $topic_name ) {
			// Filtering by resource type/name.
			$topic_length  = strpos( $topic_key, '.' ) === false ? 0 : strpos( $topic_key, '.' );
			$resource_name = substr( $topic_key, 0, $topic_length );
			if ( ( !empty($resource_type) ) && $resource_name !== $resource_type) {
				continue;
			}

			if ( isset( $request['search'] ) && strlen( $request['search'] ) > 0 ) {
				// Filter by search term (in key or in name).
				if ( false === stripos( $topic_key, $request['search'] ) && false === stripos( $topic_name, $request['search'] ) ) {
					continue;
				}
			}

			$response[] = $this->prepare_response_for_collection(
				new WP_REST_Response(
					array(
						'key'  => $topic_key,
						'name' => $topic_name,
					)
				)
			);
		}

		usort(
			$response,
			function( $a, $b ) {
				return strnatcasecmp( $a['name'], $b['name'] );
			}
		);

		return rest_ensure_response( $response );
	}
}
