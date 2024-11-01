<?php

namespace WPConnectr;

use WPConnectr\Logger;

class SchemaTypecaster {
	/**
	 * Forces the data object to match the types specified by the schema.
	 * The $data object must be described by $schema_props
	 *
	 * @param array $data the data to be converted
	 * @param array $schema The schema that describes the data
	 * @return array The updated data
	 */
	public static function typecast_to_schema( $data, $schema ) {

		if ( array_key_exists('properties', $schema)) {
				$schemaProps = $schema['properties'];
		} else {
				$schemaProps = $schema;
		}

		foreach ($schemaProps as $propname => $props) {
			if ( ! array_key_exists( $propname, $data ) ) {
				// Skip properties that are not in the data
				Logger::log_message('Skipping key because not in data: %s, schema: %s ', array($propname, var_export($props, true)), 'debug');
				continue;
			}

			$original_data = $data[ $propname ];

			$data[ $propname ] = self::typecast_property( $data[ $propname ], $props );

			if ( $original_data !== $data[ $propname ] ) {
				Logger::log_message(
					sprintf( '[!!!] WP Connectr typecasted (%s) %s from %s to %s',
						$props[ 'type' ],
						$propname,
						var_export( $original_data, true ),
						var_export( $data[ $propname ], true)
					),
					'debug'
				);
			}
		}

		return $data;
	}

	/**
	 * Typecasts a single property to match the schema
	 *
	 * @param mixed $data
	 * @param array $props
	 * @return mixed
	 */
	private static function typecast_property( $data, $props ) {
		if ( is_null( $data ) ) {
			return $data;
		}

		switch ( $props['type'] ) {
			case 'integer':
				if ( ! is_int( $data ) ) {
					$data = intval( $data );
				}
				break;

			case 'number':
				if ( ! is_float( $data ) ) {
					$data = floatval( $data );
				}
				break;

			case 'boolean':
				if ( ! is_bool( $data ) ) {
					$data = boolval( $data );
				}
				break;

			case 'array':
				if ( array_key_exists( 'items', $props ) ) {
					$typecasted = array();
					foreach ( $data as $item ) {
						$typecasted[] = self::typecast_property( $item, $props[ 'items' ] );
					}
					$data = $typecasted;
				}
				break;

			case 'object':
				if ( array_key_exists( 'properties', $props ) ) {
					$data = json_decode( wp_json_encode( $data ), true );
					$data = self::typecast_to_schema( $data, $props[ 'properties' ] );
				}
				break;

			default:
				# Default behavior returns a string see DynamicSchemaProvider for more info
				# This also covers the case where type is 'string'
				if ( ! is_string( $data ) ) {
					$data = wp_json_encode( $data );
				}
				break;
		}

		return $data;
	}
}
