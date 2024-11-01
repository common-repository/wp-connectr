<?php

namespace WPConnectr;

use WPConnectr\ThirdParty\League\Container\Container;
use WPConnectr\Plugin;
use WPConnectr\API\API;
use WPConnectr\Controller\Resources;
use WPConnectr\Logger;
use WPConnectr\ResourceManager;
use WPConnectr\Admin\Menu;
use WPConnectr\Admin\WelcomePage;
use WPConnectr\Capabilities;
use WPConnectr\Trigger\DataStore;
use WPConnectr\Trigger\Topics;
use WPConnectr\Controller\TriggerTopicsController;
use WPConnectr\PowerResource\User\Controller as UserController;
use WPConnectr\PowerResource\User\PowerResource as UserResource;
use WPConnectr\PowerResource\Attachment\Controller as AttachmentController;
use WPConnectr\PowerResource\Attachment\PowerResource as AttachmentResource;
use WPConnectr\PowerResource\PostType\Controller as PostTypeController;
use WPConnectr\PowerResource\PostType\PowerResource as PostTypeResource;
use WPConnectr\PowerResource\PostStatus\Controller as PostStatusController;
use WPConnectr\PowerResource\PostStatus\PowerResource as PostStatusResource;
use WPConnectr\PowerResource\Taxonomies\Controller as TaxonomyController;
use WPConnectr\PowerResource\Taxonomies\PowerResource as TaxonomyResource;
use WPConnectr\PowerResource\Post\Controller as PostController;
use WPConnectr\PowerResource\Post\PowerResource as PostResource;
use WPConnectr\PowerResource\Terms\Controller as TermsController;
use WPConnectr\PowerResource\Terms\PowerResource as TermsResource;
use WPConnectr\PowerResource\Comments\Controller as CommentsController;
use WPConnectr\PowerResource\Comments\PowerResource as CommentsResource;

defined('ABSPATH') || exit;

/**
 * Dependency Injection Container Service
 *
 * @since 1.0.0
 */
class ContainerService {

	protected static $instance;

	/**
	 * Get the singleton instance of this class.
	 *
	 * @return ContainerService
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new ContainerService();
		}

		return self::$instance;
	}

	public static function get_instance_of( $alias, bool $new = false ) {
		return self::get_instance()->get( $alias, $new );
	}

	/**
	 * Dependency Injection Container instance.
	 *
	 * @see https://container.thephpleague.com/4.x/ for documentation.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Constructor, initialises our container.
	 */
	public function __construct() {
			$this->container = new Container();
			// NOTE: If you're getting errors about "not managed by this container", it's a missing class but not the one reported.

			$this->register();
	}

	/**
	 * Retrieve an item/service from the container.
	 *
	 * @param string $alias Class name/alias.
	 * @param array  $args Optional arguments.
	 *
	 * @return mixed|object
	 */
	public function get( $alias, bool $new = false ) {
			return $this->container->get($alias, $new);
	}

	protected function register() {
		$this->container->addShared(
			Plugin::class,
			function () {
				$plugin = new Plugin( $this );

				return $plugin;
			}
		);

		$this->container->addShared(
			Capabilities::class,
			function () {
				return new Capabilities();
			}
		);

		$this->container->add(
			Installer::class,
			function () {
				return new Installer(
					$this->container->get(Logger::class)
				);
			}
		);

		$this->container->add(
			API::class,
			function () {
				return new API(
					$this,
					$this->container->get(ResourceManager::class)
				);
			}
		);

		$this->container->add(
			ACFRestApiSupport::class,
			function () {
				return new ACFRestApiSupport();
			}
		);

		$this->container->add(
			DynamicSchemaProvider::class,
			function () {
				return new DynamicSchemaProvider(
					$this->container->get(API::class)
				);
			}
		);

		$this->container->add(
			Menu::class,
			function () {
				return new Menu(
					$this->container->get( WelcomePage::class )
				);
			}
		);

		$this->container->add(
			WelcomePage::class,
			function () {
				return new WelcomePage();
			}
		);

		$this->container->add(
			Logger::class,
			function () {
				return new Logger();
			}
		);

		$this->container->add(
			ResourceManager::class,
			function () {
				return new ResourceManager(
						$this
				);
			}
		);

		$this->container->addShared(
			DataStore::class,
			function () {
				return DataStore::get_instance();
			}
		);

		$this->container->addShared(
			Topics::class,
			function () {
				return new Topics(
					$this->container->get(ResourceManager::class)
				);
			}
		);

		$this->container->add(
			TriggerTopicsController::class,
			function () {
				return new TriggerTopicsController(
					$this->container->get( Topics::class ),
					$this->container->get( ResourceManager::class )
				);
			}
		);

		$this->container->add(
			UserController::class,
			function () {
				return new UserController(
					$this->container->get(Logger::class),
					$this->container->get(ResourceManager::class)
				);
			}
		);
		$this->container->add(
			UserResource::class,
			function () {
				return new UserResource();
			}
		);

		$this->container->add(
			AttachmentController::class,
			function () {
				return new AttachmentController(
					'attachment'
				);
			}
		);
		$this->container->add(
			AttachmentResource::class,
			function () {
				return new AttachmentResource();
			}
		);

		$this->container->add(
			PostTypeController::class,
			function () {
				return new PostTypeController();
			}
		);
		$this->container->add(
			PostTypeResource::class,
			function () {
				return new PostTypeResource();
			}
		);

		$this->container->add(
			PostStatusController::class,
			function () {
				return new PostStatusController();
			}
		);
		$this->container->add(
			PostStatusResource::class,
			function () {
				return new PostStatusResource();
			}
		);

		$this->container->add(
			TaxonomyController::class,
			function () {
				return new TaxonomyController();
			}
		);
		$this->container->add(
			TaxonomyResource::class,
			function () {
				return new TaxonomyResource();
			}
		);

		$this->container->add(
			TermsController::class,
			function ( $taxonomy ) {
				return new TermsController( $taxonomy );
			}
		);
		$this->container->add(
			TermsResource::class,
			function () {
				return new TermsResource();
			}
		);

		$this->container->add(
			CommentsController::class,
			function () {
				return new CommentsController();
			}
		);
		$this->container->add(
			CommentsResource::class,
			function () {
				return new CommentsResource();
			}
		);

		$this->container->add(
			PostController::class,
			function ( $type ) {
				return new PostController( $type );
			}
		);
		$this->container->add(
			PostResource::class,
			function () {
				return new PostResource();
			}
		);

		$this->container->add(
			Resources::class,
			function () {
				return new Resources(
					$this->container->get(ResourceManager::class)
				);
			}
		);
	}
}
