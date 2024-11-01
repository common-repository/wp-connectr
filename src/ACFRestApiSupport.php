<?php

namespace WPConnectr;

defined('ABSPATH') || exit;

class ACFRestApiSupport {
	public function register() {
		if ( !class_exists('acf') ) {
			return;
		}

		require_once 'ACF/Rest_Request.php';
		require_once 'ACF/Rest_Api.php';

		new ACF\Rest_Api();
	}
}
