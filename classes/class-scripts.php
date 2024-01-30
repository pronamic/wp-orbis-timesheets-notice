<?php

/**
 * Scripts
 */
class OTN_Scripts {

	/**
	 * Construct
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Register
	 */
	public function register() {
		wp_register_script(
			'otn',
			OTN_URL . 'js/otn.js',
			array(),
			'1.0.0',
			true
		);
	}

	public function show_message() {
		$day_of_week = date( 'w' );
		$date        = filter_input( INPUT_GET, 'date', FILTER_SANITIZE_STRING );

		if ( ! is_page( 21280 ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Enqueue
	 */
	public function enqueue() {
		if ( ! $this->show_message() ) {
			return;
		}

		// AJAX
		$data = array(
			'ajaxAction' => 'get_data',
			'ajaxURL'    => admin_url( 'admin-ajax.php' ),
			'ajaxNonce'  => wp_create_nonce( 'otn_get_data_nonce' ),
		);

		wp_localize_script( 'otn', 'OTNData', $data );

		// Enqueue
		wp_enqueue_script( 'otn' );
	}
}
