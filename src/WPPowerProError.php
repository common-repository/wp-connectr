<?php

namespace WPConnectr;

use WP_Error;

defined('ABSPATH') || exit;

class WPConnectrError extends WP_Error {
	public function __construct( $message, $data = '') {
		parent::__construct('wp-connectr-general-error', $message, $data);
	}
}
