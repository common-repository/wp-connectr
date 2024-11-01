<?php

namespace WPConnectr\Controller;

use WPConnectr\DynamicSchemaProvider;
use WPConnectr\SchemaTypecaster;

defined('ABSPATH') || exit;

/**
 * JSON Schema allows more flexible output types than allowed by Power Automate.
 * This trait will find all attributes that are 'mixed' type as defined by the schema
 * and typecast them to string so that Power Automate can get the data it expects.
 *
 * It also ensures the output matches the schema.
 */
trait NoMixedTypeOutputTrait {
	/**
	 * Class using this trait must implement a get_item_schema function.
	 *
	 * @return array Array describing schema
	 */
	abstract public function get_item_schema();

	/**
	 * Prepares the entry for response
	 *
	 * @param  array $unprepared_entry
	 * @return array|bool
	 */
	public function prepare_entry_for_response( $unprepared_entry ) {
		$entry = parent::prepare_entry_for_response( $unprepared_entry );
		if ( is_wp_error( $entry ) || !$entry ) {
			return $entry;
		}

		$wp_schema = $this->get_item_schema();

		$schema = DynamicSchemaProvider::json2swagger( $wp_schema['properties'] );

		$typecasted = SchemaTypecaster::typecast_to_schema( $entry, $schema );

		return $this->prepare_typecasted_entry_for_response( $typecasted );
	}

	/**
	 * Prepares a typecasted entry for response.
	 *
	 * This method takes a typecasted entry as input and returns it as is.
	 * It is used to prepare the entry for response, ensuring that it is in the correct format.
	 *
	 * Designed to be overridden by the class using this trait.
	 *
	 * @param mixed $typecasted_entry The typecasted entry to be prepared for response.
	 * @return mixed The prepared typecasted entry.
	 */
	public function prepare_typecasted_entry_for_response( $typecasted_entry ) {
		return $typecasted_entry;
	}

	/**
	 * Prepares the item for response
	 *
	 * @param WP_Post $post
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $post, $request ) {
		$response = parent::prepare_item_for_response( $post, $request );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return $this->typecast_response( $response );
	}

	/**
	 * Typecasts the response to match the schema.
	 * Super sexy method here.
	 *
	 * @param WP_REST_Response $response
	 * @return WP_REST_Response
	 */
	public function typecast_response( $response ) {
		$wp_schema = $this->get_item_schema();

		$this->logger->debug( 'Typecasting response ' );

		$schema = DynamicSchemaProvider::json2swagger( $wp_schema['properties'] );

		$data = $response->get_data();

		$response->set_data( SchemaTypecaster::typecast_to_schema( $data, $schema ) );

		return $response;
	}


}
