<?php

namespace WPConnectr\Admin;

class Menu {
	const WP_CONNECTR_ADMIN_MENU_SLUG = '_wp_connector_admin_menu';

	private $welcome_page;

	public function __construct( WelcomePage $welcome_page ) {
		$this->welcome_page = $welcome_page;
	}

	public function register() {
		$this->welcome_page->register();

		add_action('admin_menu', array( $this, 'addMenu' ) );

		do_action( 'wp_connector_admin_menu_registered' );
	}

	public function addMenu() {
		$page_title = esc_html__( 'WP Connectr', 'wp-connectr' );
		$menu_title = esc_html__( 'Power Automate', 'wp-connectr' );

		$menu_position = 100;

		$user_capabilities = 'manage_options';

		$icon = '<svg viewBox="0 0 110 75" id="svg1" xmlns="http://www.w3.org/2000/svg">
			<path
				d="M 101.59778,4.6641794e-7 H 78.156064 c -3.23299,0 -6.04518,2.17585003358206 -6.86625,5.29594013358206 l -1.42662,4.2696 -1.42662,-4.2696 C 67.615494,2.1758505 64.793044,0.01027047 61.570324,0.01027047 H 7.1124431 c -2.206643,0 -4.290123,1.02634003 -5.634635,2.77113013 -1.34451198,1.74478 -1.80636798,4.02327 -1.24187798,6.15807 L 20.188081,68.775391 c 0.821077,3.12009 3.643526,5.28568 6.86625,5.28568 h 31.077733 c 0,0 0.041,0 0.0616,0 h 23.43146 c 0.14369,0 0.26685,-0.0308 0.41054,-0.0308 0.12316,0 0.24632,-0.0205 0.36948,-0.0308 2.8943,-0.31817 5.337,-2.37086 6.08623,-5.2241 L 108.3922,9.1447206 c 0.18475,-0.6466 0.28738,-1.33425 0.28738,-2.03217 0,-3.92063 -3.18167,-7.10230013 -7.09204,-7.10230013 z M 61.570324,7.1125706 l 7.85154,23.5443604 0.44133,1.33425 11.65928,34.95732 H 58.091014 L 56.058844,60.862271 38.138859,7.1125706 H 61.580584 Z M 27.064594,66.948501 7.1124431,7.1125706 H 30.554168 L 50.506324,66.948501 Z m 58.25536,-11.02295 -11.72087,-35.15232 4.55698,-13.6606604 h 23.431456 z"
				fill="#a7aaad" id="path4" /></svg>';

		add_menu_page(
			$page_title,
			$menu_title,
			$user_capabilities,
			self::WP_CONNECTR_ADMIN_MENU_SLUG,
			array( $this->welcome_page, 'render' ), // TODO: Refactor for additional pages
			'data:image/svg+xml;base64,' . base64_encode( $icon ),
			$menu_position
		);
	}
}
