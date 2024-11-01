<?php

namespace WPConnectr\PowerResource\Attachment;

use WPConnectr\PowerResource\Base;

defined( 'ABSPATH' ) || exit;

/**
 * Definition of the Field resource type.
 *
 */
class PowerResource extends Base {

	public function get_controllers() {
		$controllers = array(
			array(
				'key' => 'attachment',
				'name' => __( 'Attachment', 'wp-connectr' ),
				'controller' => new Controller( 'attachment' ),
			),
		);

		return $controllers;
	}
}
