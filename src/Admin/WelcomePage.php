<?php

namespace WPConnectr\Admin;

use WP_REST_Server;
use WPConnectr\ResourceManager;
use WPConnectr\ContainerService;

defined('ABSPATH') || exit;

class WelcomePage {
	const WP_CONNECTR_WELCOME_PAGES = array( 'wp-connectr-welcome' );

	public function register() {
	}

	public function render_actions_table() {
		$container = ContainerService::get_instance();

		$resource_manager = $container->get( ResourceManager::class );

		$abilities = $resource_manager->get_resource_abilities();

		?>
		<table class="resource_abilities">
			<thead>
				<th class="resc"></th>
				<th>List</th>
				<th>Create</th>
				<th>Read</th>
				<th>Update</th>
				<th>Delete</th>
			</thead>
			<tbody>
				<?php foreach ( $abilities as $resource ) : ?>
					<tr>
						<td class="resc"><?php echo esc_html( $resource['name'], 'wp-connectr' ); ?></td>
						<td><?php echo in_array( WP_REST_Server::READABLE, $resource['collection_methods']) ? '✅' : '❌'; ?></td>
						<td><?php echo in_array( WP_REST_Server::CREATABLE, $resource['collection_methods']) ? '✅' : '❌'; ?></td>
						<td><?php echo in_array( WP_REST_Server::READABLE, $resource['member_methods']) ? '✅' : '❌'; ?></td>
						<td><?php
						echo (
							in_array( WP_REST_Server::EDITABLE, $resource['member_methods']) ||
							in_array( 'PATCH', $resource['member_methods'] )
						) ? '✅' : '❌'; ?></td>
						<td><?php echo in_array( WP_REST_Server::DELETABLE, $resource['member_methods']) ? '✅' : '❌'; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	public function render() {
		$version = WP_CONNECTR_VERSION;

		?>
		<div class="wrap about-wrap" id="wp-connectr-welcome-header">
			<div class="col">
				<img class="logo" style="width: 150px;" src="<?php echo esc_html( plugins_url( 'assets/images/wp-connectr-logo.png', WP_CONNECTR_PLUGIN_FILE ) ); ?>" alt="WP Connectr Logo" />
			</div>
			<div class="col">
				<h1>
					<?php
						printf(
							// translators: %s: Plugin Version.
							esc_html__( 'You are running WPConnectr %s', 'wp-connectr' ),
							esc_html( $version )
						);
					?>
				</h1>
				<div class="about-text">
					<?php esc_html_e( 'Enhance your WordPress site with Power Automate!', 'wp-connectr' ); ?>
				</div>
			</div>
		</div>

		<div class="about-wrap">

			<!--
			<h2 class="about-headline-callout">Building a Flow</h2>

			<div class="feature-video"  style="text-align:center;">
				<iframe width='560' height='315'
						src=''
						frameborder='0'
						allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share'
						allowfullscreen></iframe>

				<p style="text-align:center; padding-top: 1em;"><a class="button button-primary button-hero" href="#" rel="noopener noreferrer external" target="_blank">Read more: Building your first flow<span class='screen-reader-text'> <?php esc_attr_e( 'This link opens in a new window.', 'wp-connectr' ); ?></span></a></p>
			</div>
				-->

			<h2 id="wp-connectr-summary">Our Goal: Be The World's Best Power Automate Integration for WordPress.</h2>

			<div class="feature-section two-col has-2-columns is-fullwidth">
				<div class="col column">
					<h3>To Start, Set Up A Connection</h3>
					<p>You will be using a WordPress user account to run your flows. This account will need to have the proper permissions to do what you want to do.</p>
					<p>You are required to use an Application Password to connect to your site. This will allow you to revoke access at any time without changing your password.</p>
					<p>A connection is how you authenticate with WordPress through Power Automate. You specify the connection details separately from your flows, so you can reuse them across multiple flows.</p>
				</div>
				<div class="col column">
					<h4>How to create a connection:</h4>
					<p>In order to follow the steps below, make sure you have a user account on your WordPress site with the proper permissions and have created an Application Password.</p>

					<ol class="ol-decimal">
						<li>Go to the <a href="https://make.powerautomate.com/" target="_blank">Power Automate website</a> and click 'Create flow'</li>
						<li>Choose a trigger for your flow.</li>
						<li>If you'd like to trigger flows from WordPress, you will need the premium plugin.(Coming Soon!)</li>
						<li>Select WP Connectr as your connector, then choose from an available action.</li>
						<li>Select the type of resource you want to work with, then complete the rest of the configuration.</li>
					</ol>
				</div>
			</div>

			<div class="centered-div">
				WP Connectr is a free plugin, but is a premium connector in Power Automate, which means you'll need a Power Automate plan that supports premium connectors to use it. Many times, this is bundled with existing licensing.
			</div>

			<div class="feature-section two-col has-2-columns is-fullwidth">
				<div class="col column">
					<h4>What is a Flow?</h4>
					<p>A flow is a series of steps that automates a task. For example, you could create a flow that automatically sends an email when a new form submission is received.</p>
					<p>Power Automate is Microsoft's task automation service. This plugin provides actions for WordPress to help you do more with your WordPress site.</p>
					<p>All requests from Power Automate are logged, so you'll always know what's happening with your WordPress site.</p>
				</div>
				<div class="col column">
					<h3>Build a Flow</h3>

					<ol class="ol-decimal">
						<li>Go to the <a href="https://make.powerautomate.com/" target="_blank">Power Automate website</a> and click 'Create flow'</li>
						<li>Choose a trigger for your flow.</li>
						<li>If you'd like to trigger flows from WordPress, you will need the premium plugin. (Coming Soon!)</li>
						<li>Select WP Connectr for WordPress as your connector, then choose from an available action.</li>
						<li>Select the type of resource you want to work with, then complete the rest of the configuration.</li>
					</ol>
				</div>
			</div>


			<hr />

			<div class="feature-section two-col has-2-columns is-fullwidth">
				<div class="col column">
					<h3>What can this site do with Power Automate?</h3>
					<p>Your website has been enhanced with powerful capabilities that can be accessed by Power Automate. The table to the right displays what is supported on this website.</p>
				</div>
				<div class="col column">
					<?php $this->render_actions_table(); ?>
				</div>
			</div>

			<div class="feature-section two-col has-2-columns is-fullwidth">
				<div class="col column">
					<img style="width: 450px;" src="<?php echo esc_html( plugins_url( 'assets/images/coming-soon.png', WP_CONNECTR_PLUGIN_FILE ) ); ?>" alt="Coming soon!" />
				</div>
				<div class="col column">
					<h3>Do more with Triggers</h3>
					<p>Actions are great, but triggers are where the magic happens. Keep an eye out for a premium plugin that will enable triggers in Power Automate.</p>
					<h4 style="font-style: italic;">Coming Soon!</h4>
				</div>
			</div>

			<hr />

			<div class="feature-section two-col has-2-columns is-fullwidth">
				<div class="col column">
					<h3>Our other products</h3>

					<p>Reenhanced is a world leader in connecting WordPress to Microsoft products. Check out our other Power Automate products!</p>
				</div>
				<div class="col column">
					<ul class="products-list">
						<li>
							<a href="https://reenhanced.com/products/gravity-forms-power-automate-professional">
								<img src="<?php echo esc_html( plugins_url( 'assets/images/gravity-forms-power-automate-professional.png', WP_CONNECTR_PLUGIN_FILE ) ); ?>" alt="Gravity Forms Power Automate Professional" />
								<h4>Gravity Forms Power Automate Professional</h4>
							</a>
						</li>

						<li>
							<a href="https://reenhanced.com/products/power-automate-for-woocommerce">
								<img src="<?php echo esc_html( plugins_url( 'assets/images/power-automate-for-woocommerce.png', WP_CONNECTR_PLUGIN_FILE ) ); ?>" alt="Power Automate for WooCommerce" />
								<h4>Power Automate for WooCommerce</h4>
							</a>
						</li>

						<li>
							<a href="https://reenhanced.com/products/power-automate-for-wpforms/">
								<img src="<?php echo esc_html( plugins_url( 'assets/images/power-automate-for-wpforms.png', WP_CONNECTR_PLUGIN_FILE ) ); ?>" alt="Power Automate for WPForms" />
								<h4>Power Automate for WPForms</h4>
							</a>
						</li>

						<li>
							<a href="https://reenhanced.com/products/power-form-7/">
								<img src="<?php echo esc_html( plugins_url( 'assets/images/power-form-7.png', WP_CONNECTR_PLUGIN_FILE ) ); ?>" alt="Power Automate for Contact Form 7" />
								<h4>Power Form 7: Power Automate for Contact Form 7</h4>
							</a>
						</li>
					</ul>
				</div>
			</div>
		</div>

		<?php
	}
}
