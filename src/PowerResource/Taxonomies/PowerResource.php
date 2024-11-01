<?php

namespace WPConnectr\PowerResource\Taxonomies;

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
				'key' => 'taxonomy',
				'name' => __( 'Taxonomy', 'wp-connectr' ),
				'controller' => new Controller(),
			),
		);
	}
}
