<?php

namespace WPConnectr\PowerResource\Comments;

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
				'key' => 'comments',
				'name' => __( 'Comments', 'wp-connectr' ),
				'controller' => new Controller(),
			),
		);
	}
}
