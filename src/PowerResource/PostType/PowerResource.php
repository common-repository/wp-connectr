<?php

namespace WPConnectr\PowerResource\PostType;

use WPConnectr\PowerResource\Base;

defined( 'ABSPATH' ) || exit;

/**
 * Definition of the Field resource type.
 *
 */
class PowerResource extends Base {

	public function get_controllers() {
		return array(
			array(
				'key' => 'post_type',
				'name' => __( 'Post Type', 'wp-connectr' ),
				'controller' => new Controller(),
			),
		);
	}
}
