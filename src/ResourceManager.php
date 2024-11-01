<?php

namespace WPConnectr;

use WPConnectr\API\API;
use WPConnectr\ContainerService;
use WPConnectr\PowerResource\User\PowerResource as UserResource;
use WPConnectr\PowerResource\Attachment\PowerResource as AttachmentResource;
use WPConnectr\PowerResource\PostType\PowerResource as PostTypeResource;
use WPConnectr\PowerResource\PostStatus\PowerResource as PostStatusResource;
use WPConnectr\PowerResource\Post\PowerResource as PostResource;
use WPConnectr\PowerResource\Taxonomies\PowerResource as TaxonomyResource;
use WPConnectr\PowerResource\Terms\PowerResource as TermsResource;
use WPConnectr\PowerResource\Comments\PowerResource as CommentsResource;

defined('ABSPATH') || exit;

/**
 * Resource Manager.
 *
 * Responsible for loading and accessing Resource Type definitions.
 *
 */
class ResourceManager {

	/**
	 * @var PowerResource[]
	 */
	protected $resources = array();

	/**
	 * @var ContainerService
	 */
	protected $container;

	public function __construct( ContainerService $container ) {
		$this->container = $container;
	}

	public function register() {

		if ( !API::available() ) {
			return;
		}

		// ADD ALL INCLUDED RESOURCES TO THIS LIST
		$resources[] = $this->container->get(UserResource::class);
		$resources[] = $this->container->get(AttachmentResource::class);
		$resources[] = $this->container->get(PostTypeResource::class);
		$resources[] = $this->container->get(PostStatusResource::class);
		$resources[] = $this->container->get(PostResource::class);
		$resources[] = $this->container->get(TaxonomyResource::class);
		$resources[] = $this->container->get(TermsResource::class);
		$resources[] = $this->container->get(CommentsResource::class);

		// Filter: wp-connectr_resources
		//   Allows you to add or remove resources
		$this->resources = apply_filters('wp-connectr_resources', $resources);

		$this->validate_resources();

		// Register all resources
		foreach ( $this->resources as $resource ) {
			$resource->register();
		}
	}

	/**
	 * Validate resources.
	 *
	 * @return void
	 * @throws WPConnectrError If resources or controllers are not valid
	 */
	protected function validate_resources() {
		foreach ( $this->resources as $resource ) {
			if ( ! $resource instanceof PowerResource\Base ) {
				throw new \Exception( 'Resource must be an instance of PowerResource\Base' );
			}

			$controllers = $resource->get_controllers();
			if ( ! is_array( $controllers ) ) {
				throw new \Exception( 'Resource controllers must be an array' );
			}

			foreach ( $controllers as $controller_definition ) {
				// Validate that all controller definitions have array keys: key, name, controller
				if ( ! is_array( $controller_definition ) ) {
					throw new \Exception( 'Resource controller definition must be an array' );
				}

				if ( ! isset( $controller_definition[ 'key' ] ) || ! isset( $controller_definition[ 'name' ] ) || ! isset( $controller_definition[ 'controller' ] ) ) {
					throw new \Exception( 'Resource controller definition must have keys: key, name, controller' );
				}

				$controller = $controller_definition[ 'controller' ];
				if ( ! $controller instanceof \WP_REST_Controller ) {
					throw new \Exception( 'Resource controller must be an instance of WP_REST_Controller' );
				}
			}
		}
	}

	/**
	 * Get all active/enabled resource types.
	 *
	 * @return Base[]
	 */
	public function get_enabled() {
		$enabled = array();
		foreach ( $this->get_all() as $resource ) {
			if ( $resource->is_enabled() ) {
				$enabled[] = $resource;
			}
		}
		return $enabled;
	}

	public function get_enabled_controller_keys() {
		$controllers = array();
		foreach ( $this->get_enabled() as $resource ) {
			$resource_controllers = $resource->get_controllers();

			foreach ( $resource_controllers as $controller_def ) {
				$key = $controller_def['key'];
				if ( ! isset( $controllers[ $key ] ) ) {
					$controllers[ $key ] = $controller_def;
				}
			}
		}

		ksort( $controllers );

		return array_keys( $controllers );
	}

	public function get_resource_abilities() {
		$abilities = array();
		foreach ( $this->get_enabled() as $resource ) {
			$resource_controllers = $resource->get_controllers();

			foreach ( $resource_controllers as $controller_def ) {
				$member_methods = is_callable( array( $controller_def['controller'], 'member_methods' ) ) ? $controller_def['controller']->member_methods() : array();
				$collection_methods = is_callable( array( $controller_def['controller'], 'collection_methods' ) ) ? $controller_def['controller']->collection_methods() : array();

				$name = "${controller_def['name']} (${controller_def['key']})";

				$abilities[] = array(
					'name' => $name,
					'key' => $controller_def['key'],
					'member_methods' => $member_methods,
					'collection_methods' => $collection_methods,
				);
			}
		}

		// Sort by name
		usort( $abilities, function( $a, $b ) {
			return strcmp( $a['name'], $b['name'] );
		} );

		return $abilities;
	}

	public function get_all() {
		if (empty($this->resources)) {
			$this->register();
		}
		return $this->resources;
	}

	/**
	 * Get a list of enabled/active resources.
	 *
	 * @return array
	 */
	public function get_enabled_sorted_controllers() {
		$controllers = array();
		foreach ( $this->get_enabled() as $resource ) {
			$resource_controllers = $resource->get_controllers();

			foreach ( $resource_controllers as $controller_def ) {
				$key = $controller_def['key'];
				if ( ! isset( $controllers[ $key ] ) ) {
					$controllers[ $key ] = $controller_def;
				}
			}
		}

		ksort( $controllers );

		return array_values( $controllers );
	}
}
