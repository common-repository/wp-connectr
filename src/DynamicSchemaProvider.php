<?php

namespace WPConnectr;

use WPConnectr\API\API;
use WPConnectr\Helper\Humanizer;

class DynamicSchemaProvider {
	const SKIPPED_PROPERTIES = array( 'context' );
	const SUPPORTED_TYPES    = array( 'string', 'integer', 'number', 'boolean', 'array', 'object' );
	const SUPPORTED_FORMATS  = array( 'date-time', 'uri', 'email' );

	public static function json2swagger( $json_schema, $remove_readonly = false ) {
		$swagger    = array();
		$properties = array();
		$required   = array();

		foreach ( $json_schema as $propname => $props ) {
			if ( in_array( $propname, self::SKIPPED_PROPERTIES, true ) ) {
				continue;
			}

			$item = self::_swagger_items_object( $props, $propname, $remove_readonly );

			if ( !empty( $item ) ) {
				if ( in_array( $item['type'], self::SUPPORTED_TYPES, true ) ) {
					$properties[ $propname ] = $item;
				}

				if ( isset( $props['required'] ) && $props['required'] ) {
					$properties[ $propname ]['x-ms-visibility'] = 'important';

					$required[] = '' . $propname;
				}
			}
		}

		$swagger['type']       = 'object';
		$swagger['properties'] = $properties;

		if ( ! empty( $required ) ) {
			$swagger['required'] = $required;
		}

		if ( empty( $swagger['properties'] ) ) {
			$swagger['properties'] = json_decode( '{}' );
		}

		return $swagger;
	}

	/**
	 * Semi-private function to convert a json schema items object into a swagger items object
	 *
	 * @param array $jitem The json schema items object
	 * @param string $title The name or title you'd like to use
	 * @return array The items object in swagger
	 */
	public static function _swagger_items_object( $jitem, $title = null, $remove_readonly = false ) {
		$item = array();

		if ( '_typecasted' === $title && true === $jitem ) {
			return array( 'type' => 'boolean', 'x-ms-visibility' => 'internal', 'x-ms-summary' => 'Forced to object?' );
		}

		if ( !is_array( $jitem ) || !array_key_exists( 'type', $jitem ) ) {
			return $jitem;
		}


		if ( !empty( $jitem['title'] ) ) {
			$item[ 'x-ms-summary' ] = $jitem['title'];
		} elseif ( !empty( $title ) && !is_numeric( $title ) ) {
			$item['x-ms-summary'] = (string) new Humanizer($title);
		}

		// Copy all array keys x-* from $jitem into $item
		foreach ( $jitem as $key => $value ) {
			if ( 0 === strpos( $key, 'x-' ) ) {
				$item[ $key ] = $value;
			}
		}

		if ( is_array( $jitem['type'] ) ) {
			if ( count( $jitem['type'] ) === 1 ) {
				$jitem['type'] = $jitem['type'][0];
			} else {
				// Power Automate doesn't support multiple types, so we need to work out how this should be handled
				if ( isset( $jitem[ 'items' ] ) && in_array( 'array', $jitem['type'], true ) ) {
					$jitem['type'] = 'array';
				} elseif ( in_array( 'string', $jitem['type'], true ) ) {
					$jitem['type'] = 'string';
				}

			}
		}

		if ( in_array( $jitem['type'], self::SUPPORTED_TYPES, true ) ) {
			$item['type'] = $jitem['type'];
		} else {
			$item['type'] = 'string';
		}

		if ( isset( $jitem['format'] ) && in_array( $jitem['format'], self::SUPPORTED_FORMATS, true ) ) {
			$item['format'] = $jitem['format'];
		}

		$item['description'] = isset( $jitem['description'] ) ? $jitem['description'] : null;
		$item['readOnly']    = isset( $jitem['readonly'] )    ? !!$jitem['readonly']  : null;
		$item['enum']        = isset( $jitem['enum'] )        ? $jitem['enum']        : null;
		$item['default']     = isset( $jitem['default'] )     ? $jitem['default']     : null;

		if ( 'array' === $item['type'] ) {
			$array_items = isset( $jitem['items'] ) ? $jitem['items'] : array();
			$item['items'] = self::_swagger_items_object( $array_items, null, $remove_readonly );
			if ( isset($jitem['default']) && !is_array( $jitem['default'] ) ) {
				$item['default'] = array( $jitem['default'] );
			}
		}

		if ( 'object' === $item['type'] && isset( $jitem['properties'] ) ) {
			$props = array();

			$numeric_keys = true;

			foreach ( $jitem['properties'] as $propname => $jprops ) {
				if ( !is_numeric( $propname ) && '_typecasted' !== $propname ) {
					$numeric_keys = false;
				}

				$props[ $propname ] = self::_swagger_items_object( $jprops, $propname, $remove_readonly );
			}

			if ( $numeric_keys && ! empty( $props ) ) {
				$props[ '_typecasted' ] = array( 'type' => 'boolean', 'x-ms-visibility' => 'internal', 'x-ms-summary' => 'Forced to object?' );
			}

			$item['properties'] = array_filter( $props );
		}

		if ( $remove_readonly && $item['readOnly'] ) {
			return array();
		}

		// Trim out anything with an empty value
		return array_filter( $item );
	}

	/**
	 * The API instance
	 *
	 * @var API
	 */
	private $api;

	/**
	 * The input data
	 *
	 * @var array
	 */
	private $input_data;

	/**
	 * The data, typically the response body
	 *
	 * @var array
	 */
	private $data;

	public function __construct( API $api ) {
		$this->api = $api;

		$this->input_data = array();
		$this->data       = array();
	}

	public function register() {
		add_filter( 'rest_post_dispatch', array( $this, 'translate_power_automate_options_request' ), 10, 3);
		add_filter( 'wp_connector_dynamic_schema', array( $this, 'filter_schema' ), 10, 1 );
	}

	/**
	 * Accepts a schema and converts it to a format compatible with Power Automate.
	 *
	 * @param array $raw_schema Must contain 'schema' key
	 * @return void
	 */
	public function filter_schema( $raw_schema ) {
		if ( ! $this->api->is_our_request() ) {
			return $raw_schema;
		}

		$jschema      = rgar( $raw_schema, 'schema', array( 'properties' => array() ) );
		$schema_props = self::json2swagger( $jschema['properties'] );

		return array( 'schema' => $schema_props );
	}

	/**
	 * Translates OPTIONS requests to our API to a format compatible with Power Automate.
	 *
	 * Violates normal WP schema standards.
	 *
	 * @param WP_HTTP_Response $response
	 * @param WP_REST_Server $wp_rest_server
	 * @param WP_REST_Request $request
	 * @return WP_HTTP_Response
	 */
	public function translate_power_automate_options_request( $response, $wp_rest_server, $request ) {
		if ( $request->get_method() !== 'OPTIONS' || ! $this->api->is_our_request() ) {
			return $response;
		}

		$this->set_input_data( $response->get_data() );
		$this->set_data( array() );

		if ( isset( $this->input_data['schema'] ) ) {
			$jschema      = $this->input_data['schema'];
			$schema_props = self::json2swagger( $jschema['properties'] );

			$schema          = $schema_props;
			$schema['title'] = (string) new Humanizer( $jschema['title'] );
			$schema['type']  = $jschema['type'];

			$this->set_data_prop( 'schema', $schema );
		}

		if ( array_key_exists( 'methods', $this->input_data ) ) {
			foreach ( $this->input_data['methods'] as $method ) {
				$jschema         = $this->first_matching_endpoint( $method );
				$method          = strtolower( $method );
				$remove_readonly = false;

				if ( 'get' !== $method ) {
					$remove_readonly = true;
				}

				$swagger = self::json2swagger( $jschema, $remove_readonly );

				$this->set_data_prop( $method, $swagger );
			}
		}

		$response->set_data( $this->get_data() );

		return $response;
	}

	protected function get_data() {
		return $this->data;
	}

	protected function set_data( $data ) {
		$this->data = $data;

		return $data;
	}

	protected function set_data_prop( $key, $value ) {
		$this->data[ $key ] = $value;

		return $this->data[ $key ];
	}

	protected function get_input_data() {
		return $this->input_data;
	}

	protected function set_input_data( $value ) {
		$this->input_data = $value;

		return $value;
	}


	/**
	 * Returns the input schema expected for the first endpoint found matching the method given
	 *
	 * @param string $method The method that we want to match
	 * @return array An array describing the input parameters expected for the given method
	 */
	private function first_matching_endpoint( $method ) {
		$endpoints = $this->input_data['endpoints'];
		$args      = array();

		if ( empty( $endpoints ) ) {
			return $args;
		}

		foreach ( $endpoints as $endpoint ) {
			if ( in_array( $method, $endpoint['methods'], true ) ) {
				$args = $endpoint['args'];
				break;
			}
		}

		return $args;
	}
}
