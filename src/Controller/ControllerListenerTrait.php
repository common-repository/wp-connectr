<?php

namespace WPConnectr\Controller;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

defined( 'ABSPATH' ) || exit;

/**
 * Adds logging. Allows us to debug requests
 * from Power Automate, and will be used to give an audit trail
 *
 */
trait ControllerListenerTrait {

	/**
	 * Item Create.
	 *
	 * @uses WC_REST_CRUD_Controller::create_item() as parent::create_item() Create a single item.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response REST API Response.
	 */
	public function create_item( $request ) {
		$response = parent::create_item( $request );
		if ( is_a( $response, 'WP_Error' ) ) {
			$this->log_error_response( $request, $response );
		}
		return $response;
	}

	/**
	 * Item Fetch
	 *
	 * @uses WC_REST_CRUD_Controller::get_item() as parent::get_item() Get a single item.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response REST API Response
	 */
	public function get_item( $request ) {
		$response = parent::get_item( $request );
		if ( is_a( $response, 'WP_Error' ) ) {
			$this->log_error_response( $request, $response );
		}
		return $response;
	}

	/**
	 * Item Delete.
	 *
	 * @uses WC_REST_CRUD_Controller::delete_item() as parent::delete_item() Delete a single item.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ) {
		$response = parent::delete_item( $request );
		if ( is_a( $response, 'WP_Error' ) ) {
			$this->log_error_response( $request, $response );
		}
		return $response;
	}

	/**
	 * Item update.
	 *
	 * @uses WC_REST_CRUD_Controller::update_item() as parent::update_item() Update a single item.

	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {
		$response = parent::update_item( $request );
		if ( is_a( $response, 'WP_Error' ) ) {
			$this->log_error_response( $request, $response );
		}
		return $response;
	}

	/**
	 * Log a REST API response error.
	 *
	 * @param WP_REST_Request $request REST API Request.
	 * @param WP_Error        $error REST API Error Response.
	 *
	 * @return void
	 */
	protected function log_error_response( $request, $error ) {
		if ( ! isset( $this->resource_type ) ) {
			$this->resource_type = 'unknown';
		}

		$this->logger->error(
			'REST API Error Response for Request Route: %s. Request Method: %s. Resource Type: %s. Error Code: %s. Error Message: %s',
			array(
				$request->get_route(),
				$request->get_method(),
				$this->resource_type,
				$error->get_error_code(),
				$error->get_error_message(),
			)
		);
	}
}
