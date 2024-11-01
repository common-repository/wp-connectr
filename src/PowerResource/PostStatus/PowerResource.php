<?php

namespace WPConnectr\PowerResource\PostStatus;

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
				'key' => 'post_status',
				'name' => __( 'Post Status', 'wp-connectr' ),
				'controller' => new Controller(),
			),
		);
	}
}
