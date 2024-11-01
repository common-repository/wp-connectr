<?php

namespace WPConnectr\PowerResource\Terms;

use WPConnectr\Logger;
use WPConnectr\PowerResource\Base;

defined( 'ABSPATH' ) || exit;

/**
 * Definition of the Field resource type.
 *
 */
class PowerResource extends Base {

	public function get_controllers() {
		$controllers = array();

		foreach ( get_taxonomies( array( 'show_in_rest' => true ), 'objects' ) as $taxonomy ) {
			$controller = array(
				'key'  => $taxonomy->name,
				'name' => $taxonomy->label,
				'controller' => new Controller( $taxonomy->name ),
			);

			$controllers[] = $controller;
		}

		return $controllers;
	}
}
