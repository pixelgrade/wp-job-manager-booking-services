<?php
/**
 * Handle the widgets the we offer
 */

function wpjm_booking_services_register_widgets() {
	register_widget( 'WPJM_Booking_Services_Widget' );
}
add_action( 'widgets_init', 'wpjm_booking_services_register_widgets' );

class WPJM_Booking_Services_Widget extends WP_Widget {

	function __construct() {
		$widget_settings = array(
			'id' => 'wpjm_booking_services',
			'name' => '&#x1F536; ' . esc_html__( 'Listing', 'wp-job-manager-booking-services' ) . ' &raquo; ' . esc_html__( 'Booking Services', 'wp-job-manager-booking-services' ),
			'args' => array( 'description' => esc_html__( 'The 3rd party booking services forms.', 'wp-job-manager-booking-services' ) ),
			);

		//let others mess with these
		$widget_settings = apply_filters( 'wp_job_manager_booking_services_widget_settings', $widget_settings );

		parent::__construct(
			$widget_settings['id'], // Base ID
			$widget_settings['name'], // Name
			$widget_settings['args']// Args
		);
	}

	public function widget( $args, $instance ) {
		global $post;

		$opentable_ID = get_post_meta( get_the_ID(), '_booking_services_opentable', true );

		if ( ! empty ( $opentable_ID ) ) {
			echo $args['before_widget'];

			//The OpenTable JS script that generates the booking widget
			printf( '<script type="text/javascript" src="https://secure.opentable.com/frontdoor/default.aspx?rid=%1$s&restref=%1$s&bgcolor=F6F6F3&titlecolor=0F0F0F&subtitlecolor=0F0F0F&btnbgimage=https://secure.opentable.com/frontdoor/img/ot_btn_red.png&otlink=FFFFFF&icon=dark&mode=short&hover=1"></script>', $opentable_ID );

			echo $args['after_widget'];
		}
	}

	public function form( $instance ) {
		echo '<p>' . $this->widget_options['description'] . '</p>';
	}
} // class WPJM_Booking_Services_Widget