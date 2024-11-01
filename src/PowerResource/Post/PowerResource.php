<?php

namespace WPConnectr\PowerResource\Post;

use WPConnectr\PowerResource\Base;

defined( 'ABSPATH' ) || exit;

/**
 * Definition of the Field resource type.
 *
 */
class PowerResource extends Base {
	public function get_controllers() {
		$controllers = array();

		foreach ( get_post_types( array( 'show_in_rest' => true ), 'objects' ) as $post_type ) {
			if ( 'attachment' !== $post_type->name ) {
				$controller = array(
					'key'  => $post_type->name,
					'name' => $post_type->label,
					'controller' => new Controller( $post_type->name ),
				);

				$controllers[] = $controller;
			}
		}

		return $controllers;
	}
}
