<?php
/**
 * Plugin Name: WP Job Manager - Booking Services
 * Plugin URI:  https://github.com/pixelgrade/wp-job-manager-booking-services
 * Description: Add fields for booking services like OpenTable to your listings.
 * Author:      PixelGrade
 * Author URI:  https://pixelgrade.com
 * Version:     1.0.1
 * Text Domain: wp-job-manager-booking-services
 */

// Bail if accessed directly - big no no
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Job_Manager_Booking_Services {

	private static $instance;

	public static function instance() {
		if ( ! isset ( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function __construct() {
		// Define constants
		define( 'WPJM_BOOKING_SERVICES_VERSION', '1.0.1' );

		//include the widgets
		include_once( plugin_dir_path( __FILE__ ) . '/widgets.php' );

		$this->setup_actions();
	}

	private function setup_actions() {
		add_action( 'init', array( $this, 'init' ) );

		add_filter( 'job_manager_settings', array( $this, 'settings' ) );

		//Add fields to the frontend submit form
		add_filter( 'submit_job_form_fields', array( $this, 'submit_job_booking_services_fields' ) );

		//Save fields data for the frontend submit form
		add_filter( 'submit_job_form_fields_get_job_data', array( $this, 'update_job_data_booking_services' ), 10, 2 );

		// Add booking fields to the WP admin area
		add_filter( 'job_manager_job_listing_data_fields', array( $this, 'add_job_data_booking_services_fields' ) );

		// Save an empty value when the booking services fields are not in $_POST since WPJM doesn't do this for us
		add_action( 'job_manager_save_job_listing', array( $this, 'save_job_listing_data' ), 21, 2 );
	}

	/**
	 * Load translation files
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function init() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wp-job-manager-booking-services' );
		load_textdomain( 'wp-job-manager-booking-services', WP_LANG_DIR . "/wp-job-manager-booking-services/wp-job-manager-booking-services-$locale.mo" );
		load_plugin_textdomain( 'wp-job-manager-booking-services', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Add Settings
	 *
	 * @since 1.0.0
	 *
	 * @param  array $settings
	 * @return array
	 */
	public function settings( $settings = array() ) {
		//OpenTable
		$wpjm_booking_settings[] = array(
				'name' 		=> 'job_manager_enable_opentable_reservations',
				'std' 		=> '',
				'label' 	=> __( 'Booking Services', 'wp-job-manager-booking-services' ),
				'cb_label'  => __( 'Enable OpenTable Reservations', 'wp-job-manager-booking-services' ),
				'desc'		=> __( 'Let your users add their OpenTable restaurant ID for online reservations. <a href="https://www.otrestaurant.com/marketing/ReservationWidget" target="_blank">Read More<a/>', 'wp-job-manager-booking-services' ),
				'type'      => 'checkbox'
		);

		//Resurva
		$wpjm_booking_settings[] = array(
				'name' 		=> 'job_manager_enable_resurva_reservations',
				'std' 		=> '',
				'label' 	=> '',
				'cb_label'  => __( 'Enable Resurva Bookings', 'wp-job-manager-booking-services' ),
				'desc'		=> __( 'Let your users add their Resurva URL for online bookings. <a href="https://resurva.com/" target="_blank">Read More<a/>', 'wp-job-manager-booking-services' ),
				'type'      => 'checkbox'
		);

		//let others mess around with the settings
		$wpjm_booking_settings = apply_filters( 'wp_job_manager_booking_services_settings', $wpjm_booking_settings);

		$settings['job_listings'][1] = array_merge( $settings['job_listings'][1], $wpjm_booking_settings );

		return $settings;
	}

	/**
	 * Add the booking services fields to the submission form
	 *
	 * @since 1.0.0
	 *
	 * @param 	array $fields 	List of settings fields.
	 * @return	array			Altered list of settings fields.
	 */
	public function submit_job_booking_services_fields( $fields ) {

		//first the OpenTable field
		if ( get_option( 'job_manager_enable_opentable_reservations' ) ) {
			$fields['job']['job_booking_services_opentable'] = array(
					'label'       => esc_html__( 'OpenTable Restaurant ID', 'wp-job-manager-booking-services' ),
					'description' => wp_kses( __( 'Your restaurant\'s ID. Find out how to <a href="https://www.otrestaurant.com/" target="_blank">get one</a>.', 'wp-job-manager-booking-services' ), wp_kses_allowed_html() ),
					'type'        => 'text',
					'required'    => false,
					'placeholder' => esc_html__( 'e.g. 324789', 'wp-job-manager-booking-services' ),
					'priority'    => 9.1
			);
		}

		//Now Resurva
		if ( get_option( 'job_manager_enable_resurva_reservations' ) ) {
			$fields['job']['job_booking_services_resurva'] = array(
					'label'       => esc_html__( 'Resurva URL', 'wp-job-manager-booking-services' ),
					'description' => wp_kses( __( 'Your Resurva URL (the URL you used to access your booking widget). Find out how to <a href="https://resurva.com/" target="_blank">get one</a>.', 'wp-job-manager-booking-services' ), wp_kses_allowed_html() ),
					'type'        => 'text',
					'required'    => false,
					'placeholder' => esc_html__( 'e.g. https://pixelgrade.resurva.com/', 'wp-job-manager-booking-services' ),
					'priority'    => 9.2
			);
		}

		return $fields;
	}

	/**
	 * Save submit.
	 *
	 * Save the booking services fields when a listing is submitted
	 *
	 * @since 1.0.0
	 *
	 * @param 	int $job_ID
	 * @param 	array $values List of submitted values.
	 */
	public function update_job_data_booking_services( $job_ID, $values ) {
		//first the OpenTable field
		if ( get_option( 'job_manager_enable_opentable_reservations' ) ) {
			$value = isset( $values['job']['job_booking_services_opentable'] ) ? $values['job']['job_booking_services_opentable'] : '';

			//clean it up a little bit
			$value = trim( $value );

			update_post_meta( $job_ID, '_booking_services_opentable', $value );
		}

		//the Resurva field
		if ( get_option( 'job_manager_enable_resurva_reservations' ) ) {
			$value = isset( $values['job']['job_booking_services_resurva'] ) ? $values['job']['job_booking_services_resurva'] : '';

			//clean it up a little bit
			$value = trim( $value );

			update_post_meta( $job_ID, '_booking_services_resurva', $value );
		}

	}

	/**
	 * Booking services fields.
	 *
	 * Add the fields to the admin area.
	 *
	 * @since 1.0.0
	 *
	 * @param 	array $fields 	List of settings fields.
	 * @return	array			Altered list of settings fields.
	 */
	public function add_job_data_booking_services_fields( $fields ) {
		//first the OpenTable field
		if ( get_option( 'job_manager_enable_opentable_reservations' ) ) {
			$fields['_booking_services_opentable'] = array(
					'label'       => esc_html__( 'OpenTable Restaurant ID', 'wp-job-manager-booking-services' ),
					'placeholder' => '',
					'priority'    => 11.1
			);
		}

		//the Resurva field
		if ( get_option( 'job_manager_enable_resurva_reservations' ) ) {
			$fields['_booking_services_resurva'] = array(
					'label'       => esc_html__( 'Resurva URL', 'wp-job-manager-booking-services' ),
					'placeholder' => '',
					'priority'    => 11.2
			);
		}

		return $fields;

	}

	/**
	 * Save booking fields data in the WP admin area
	 *
	 * When they are empty we need to update them by hand since WP Job Manager won't do it for us
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_ID The current post ID.
	 * @param object $post The current post object.
	 */
	public function save_job_listing_data( $post_ID, $post ) {

		if ( get_option( 'job_manager_enable_opentable_reservations' ) ) {
			//first the OpenTable field
			if ( ! isset( $_POST['_booking_services_opentable'] ) ) {
				update_post_meta( $post_ID, '_booking_services_opentable', '' );
			}
		}

		if ( get_option( 'job_manager_enable_resurva_reservations' ) ) {
			//the Resurva field
			if ( ! isset( $_POST['_booking_services_resurva'] ) ) {
				update_post_meta( $post_ID, '_booking_services_resurva', '' );
			}
		}

	}
}


/**
 * Fire things up.
 *
 * Use this function instead of a global. It's just good practice.
 *
 * @since 1.0.0
 *
 * @return object WP_Job_Manager_Booking_Services instance
 */
function wp_job_manager_booking_services() {
	return WP_Job_Manager_Booking_Services::instance();
}

wp_job_manager_booking_services();