<?php

namespace WPConnectr\Trigger;

use WPConnectr\ResourceManager;

defined('ABSPATH') || exit;

class Topics {
	protected $resource_manager;

	protected $topics = array();

	public function __construct(ResourceManager $resource_manager) {
		$this->resource_manager = $resource_manager;
	}

	public function register() {
		do_action( 'wp_connector_register_topics', $this );
	}

	public function add_topic( $key, $topic ) {
		$this->topics[ $key ] = $topic;
	}

	public function get_topics() {
		if ( ! empty( $this->topics ) ) {
			return $this->topics;
		}

		$this->topics = apply_filters( 'wp_connector_get_topics', $this->topics );

		if ( empty( $this->topics ) ) {
			$this->topics = array(
				'' => __( 'Purchase license at reenhanced.com for trigger support', 'wp-connectr' )
			);
		}

		return $this->topics;
	}
}
