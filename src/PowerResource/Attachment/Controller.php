<?php

namespace WPConnectr\PowerResource\Attachment;

defined('ABSPATH') || exit;

use WP_REST_Attachments_Controller;
use WPConnectr\API\API;
use WPConnectr\Controller\ControllerListenerTrait;
use WPConnectr\Controller\RestfulRoutesTrait;
use WPConnectr\Logger;
use WPConnectr\Controller\NoMixedTypeOutputTrait;
use WP_REST_Server;
use WP_Error;

class Controller extends WP_REST_Attachments_Controller {
	use NoMixedTypeOutputTrait;
	use RestfulRoutesTrait;
	use ControllerListenerTrait;

	protected $namespace;
	protected $logger;

	public function __construct( $type ) {
		parent::__construct( $type );

		$this->namespace = API::REST_NAMESPACE;
		$this->logger    = Logger::get_instance();
	}

	public function update_item( $request ) {
		return parent::edit_media_item( $request );
	}

	public function get_endpoint_args_for_delete() {
		return array(
			'force' => array(
				'type'        => 'boolean',
				'default'     => false,
				'description' => __( 'Whether to bypass trash and force deletion.', 'wp-connectr' ),
			),
		);
	}

	public function update_item_permissions_check( $request ) {
		// Needed to copy from wp core to avoid infinite loop
		if ( ! current_user_can( 'upload_files' ) ) {
			return new WP_Error(
				'rest_cannot_edit_image',
				__( 'Sorry, you are not allowed to upload media on this site.', 'wp-connectr' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return parent::update_item_permissions_check( $request );
	}

	public function get_endpoint_args_for_item_schema( $method = WP_REST_Server::CREATABLE ) {
		if ( WP_REST_Server::EDITABLE === $method ) {
			$args = parent::get_edit_media_item_args();
		} else {
			$args = parent::get_endpoint_args_for_item_schema( $method );
		}

		return $args;
	}

}
