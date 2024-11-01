<?php

namespace WPConnectr\PowerResource\User;

use WPConnectr\PowerResource\Base;

defined( 'ABSPATH' ) || exit;


/**
 * Definition of the Product resource type.
 *
 */
class PowerResource extends Base {
	public function get_controllers() {
		return array(
			array(
				'key'        => 'user',
				'name'       => __( 'User', 'wp-connectr' ),
				'controller' => $this->container->get( 'WPConnectr\PowerResource\User\Controller' ),
			),
		);
	}
}
