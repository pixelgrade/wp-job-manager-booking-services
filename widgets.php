<?php
/**
 * Handle the widgets the we offer
 */

function wpjm_booking_services_register_widgets() {
	register_widget( 'WPJM_Booking_Services_Widget' );
}
add_action( 'widgets_init', 'wpjm_booking_services_register_widgets' );

class WPJM_Booking_Services_Widget extends WP_Widget {
	private $defaults = array();

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

		//only put in the default title if the user hasn't saved anything in the database e.g. $instance is empty (as a whole)
		$placeholders = $this->get_placeholder_strings();

		//first the OpenTable field
		if ( get_option( 'job_manager_enable_opentable_reservations' ) ) {
			$opentable_ID = get_post_meta( get_the_ID(), '_booking_services_opentable', true );

			if ( ! empty ( $opentable_ID ) ) {
				$title    = apply_filters( 'wpjm_booking_services_title', empty( $instance ) ? $placeholders['opentable_title'] : $instance['opentable_title'], $instance, $this->id_base );
				$subtitle = apply_filters( 'wpjm_booking_services_subtitle', empty( $instance ) ? $placeholders['opentable_subtitle'] : $instance['opentable_subtitle'], $instance, $this->id_base );
				echo $args['before_widget']; ?>
				<h3 class="widget_title">
					<?php
					echo $title;

					if ( ! empty( $subtitle ) ) { ?>
						<span class="widget_subtitle">
						<?php echo $subtitle; ?>
					</span>
					<?php } ?>
				</h3>

				<?php
				//The OpenTable JS script that generates the booking widget
				printf( '<script type="text/javascript" src="https://secure.opentable.com/frontdoor/default.aspx?rid=%1$s&restref=%1$s&bgcolor=F6F6F3&titlecolor=0F0F0F&subtitlecolor=0F0F0F&btnbgimage=https://secure.opentable.com/frontdoor/img/ot_btn_red.png&otlink=FFFFFF&icon=dark&mode=short&hover=1"></script>', $opentable_ID );

				echo $args['after_widget'];
			}
		}

		//the Resurva field
		if ( get_option( 'job_manager_enable_resurva_reservations' ) ) {
			$resurva_URL   = get_post_meta( get_the_ID(), '_booking_services_resurva', true );
			$resurva_title = '';

			if ( ! empty ( $resurva_URL ) ) {
				$title    = apply_filters( 'wpjm_booking_services_title', empty( $instance ) ? $placeholders['resurva_title'] : $instance['resurva_title'], $instance, $this->id_base );
				$subtitle = apply_filters( 'wpjm_booking_services_subtitle', empty( $instance ) ? $placeholders['resurva_subtitle'] : $instance['resurva_subtitle'], $instance, $this->id_base );
				echo $args['before_widget']; ?>
				<h3 class="widget_title">
					<?php
					echo $title;

					if ( ! empty( $subtitle ) ) { ?>
						<span class="widget_subtitle">
						<?php echo $subtitle; ?>
					</span>
					<?php } ?>
				</h3>

				<script id="resurva-embed" type="text/javascript">
					// <![CDATA[
					(function (d, s, id) {
						var js, rjs = d.getElementById('resurva-embed');
						if (d.getElementById(id)) return;
						js = d.createElement(s);
						js.id = id;
						js.src = "<?php echo esc_url( $resurva_URL ); ?>";
						js.src += "?key=<?php echo esc_attr( $resurva_title ); ?>";
						rjs.parentNode.insertBefore(js, rjs);
					}(document, 'script', 'resurva-js'));
					// ]]>
				</script>

				<?php echo $args['after_widget'];
			}
		}

		//the Guestful field
		if ( get_option( 'job_manager_enable_guestful_reservations' ) ) {
			$guestful_ID   = get_post_meta( get_the_ID(), '_booking_services_guestful', true );

			if ( ! empty ( $guestful_ID ) ) {
				$title    = apply_filters( 'wpjm_booking_services_title', empty( $instance ) ? $placeholders['guestful_title'] : $instance['guestful_title'], $instance, $this->id_base );
				$subtitle = apply_filters( 'wpjm_booking_services_subtitle', empty( $instance ) ? $placeholders['guestful_subtitle'] : $instance['guestful_subtitle'], $instance, $this->id_base );
				echo $args['before_widget']; ?>
				<h3 class="widget_title">
					<?php
					echo $title;

					if ( ! empty( $subtitle ) ) { ?>
						<span class="widget_subtitle">
						<?php echo $subtitle; ?>
					</span>
					<?php } ?>
				</h3>

				<?php
				$query_args = array(
						'rid' => $guestful_ID,
				);

				$url = esc_url( add_query_arg( $query_args, 'https://www.guestful.com/widgets/responsive/js/script-loader.js' ) ); ?>

				<div style="text-align:center;width: 100%; height: 300px;">
					<script type="text/javascript" class="guestful-widget-loader" src="<?php echo $url; ?>"></script>
				</div>

				<?php echo $args['after_widget'];
			}
		}
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @return null
	 */
	public function form( $instance ) {
		$original_instance = $instance;

		//Defaults
		$instance = wp_parse_args(
				(array) $instance,
				$this->defaults );

		$placeholders = $this->get_placeholder_strings();

		//the OpenTable settings
		if ( get_option( 'job_manager_enable_opentable_reservations' ) ) :
			$title = esc_attr( $instance['opentable_title'] );
			//if the user is just creating the widget ($original_instance is empty)
			if ( empty( $original_instance ) && empty( $title ) ) {
				$title = $placeholders['opentable_title'];
			}

			$subtitle = esc_attr( $instance['opentable_subtitle'] );
			//if the user is just creating the widget ($original_instance is empty)
			if ( empty( $original_instance ) && empty( $subtitle ) ) {
				$subtitle = $placeholders['opentable_subtitle'];
			} ?>

			<p class="section-title"><?php esc_html_e( 'OpenTable Widget', 'wp-job-manager-booking-services' ); ?></p>
			<p>
				<label for="<?php echo $this->get_field_id( 'opentable_title' ); ?>"><?php esc_html_e( 'Title:', 'wp-job-manager-booking-services' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'opentable_title' ); ?>" name="<?php echo $this->get_field_name( 'opentable_title' ); ?>" type="text" value="<?php echo $title; ?>" placeholder="<?php echo esc_attr( $placeholders['opentable_title'] ); ?>"/>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'opentable_subtitle' ); ?>"><?php esc_html_e( 'Subtitle:', 'wp-job-manager-booking-services' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'opentable_subtitle' ); ?>" name="<?php echo $this->get_field_name( 'opentable_subtitle' ); ?>" type="text" value="<?php echo $subtitle; ?>" placeholder="<?php echo esc_attr( $placeholders['opentable_subtitle'] ); ?>"/>
			</p>

		<?php endif;

		//the Resurva settings
		if ( get_option( 'job_manager_enable_resurva_reservations' ) ) :
			$title = esc_attr( $instance['resurva_title'] );
			//if the user is just creating the widget ($original_instance is empty)
			if ( empty( $original_instance ) && empty( $title ) ) {
				$title = $placeholders['resurva_title'];
			}

			$subtitle = esc_attr( $instance['resurva_subtitle'] );
			//if the user is just creating the widget ($original_instance is empty)
			if ( empty( $original_instance ) && empty( $subtitle ) ) {
				$subtitle = $placeholders['resurva_subtitle'];
			} ?>

			<p class="section-title"><?php esc_html_e( 'Resurva Widget', 'wp-job-manager-booking-services' ); ?></p>
			<p>
				<label for="<?php echo $this->get_field_id( 'resurva_title' ); ?>"><?php esc_html_e( 'Title:', 'wp-job-manager-booking-services' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'resurva_title' ); ?>" name="<?php echo $this->get_field_name( 'resurva_title' ); ?>" type="text" value="<?php echo $title; ?>" placeholder="<?php echo esc_attr( $placeholders['resurva_title'] ); ?>"/>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'resurva_subtitle' ); ?>"><?php esc_html_e( 'Subtitle:', 'wp-job-manager-booking-services' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'resurva_subtitle' ); ?>" name="<?php echo $this->get_field_name( 'resurva_subtitle' ); ?>" type="text" value="<?php echo $subtitle; ?>" placeholder="<?php echo esc_attr( $placeholders['resurva_subtitle'] ); ?>"/>
			</p>

		<?php endif;

		//the Guestful settings
		if ( get_option( 'job_manager_enable_guestful_reservations' ) ) :
			$title = esc_attr( $instance['guestful_title'] );
			//if the user is just creating the widget ($original_instance is empty)
			if ( empty( $original_instance ) && empty( $title ) ) {
				$title = $placeholders['guestful_title'];
			}

			$subtitle = esc_attr( $instance['guestful_subtitle'] );
			//if the user is just creating the widget ($original_instance is empty)
			if ( empty( $original_instance ) && empty( $subtitle ) ) {
				$subtitle = $placeholders['guestful_subtitle'];
			} ?>

			<p class="section-title"><?php esc_html_e( 'Guestful Widget', 'wp-job-manager-booking-services' ); ?></p>
			<p>
				<label for="<?php echo $this->get_field_id( 'guestful_title' ); ?>"><?php esc_html_e( 'Title:', 'wp-job-manager-booking-services' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'guestful_title' ); ?>" name="<?php echo $this->get_field_name( 'guestful_title' ); ?>" type="text" value="<?php echo $title; ?>" placeholder="<?php echo esc_attr( $placeholders['guestful_title'] ); ?>"/>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'guestful_subtitle' ); ?>"><?php esc_html_e( 'Subtitle:', 'wp-job-manager-booking-services' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'guestful_subtitle' ); ?>" name="<?php echo $this->get_field_name( 'guestful_subtitle' ); ?>" type="text" value="<?php echo $subtitle; ?>" placeholder="<?php echo esc_attr( $placeholders['guestful_subtitle'] ); ?>"/>
			</p>

		<?php endif;

	}

	/**
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                    = $old_instance;
		$instance['opentable_title']           = strip_tags( $new_instance['opentable_title'] );
		$instance['opentable_subtitle']        = strip_tags( $new_instance['opentable_subtitle'] );

		$instance['resurva_title']           = strip_tags( $new_instance['resurva_title'] );
		$instance['resurva_subtitle']        = strip_tags( $new_instance['resurva_subtitle'] );

		$instance['guestful_title']           = strip_tags( $new_instance['guestful_title'] );
		$instance['guestful_subtitle']        = strip_tags( $new_instance['guestful_subtitle'] );

		return $instance;
	}

	private function get_placeholder_strings() {
		$placeholders = apply_filters( 'wpjm_booking_services_widget_backend_placeholders', array() );

		$placeholders = wp_parse_args(
				(array) $placeholders,
				array(
						'opentable_title'           => esc_html__( 'Book a Table', 'wp-job-manager-booking-services' ),
						'opentable_subtitle'        => esc_html__( 'Your dinner is a few clicks away.', 'wp-job-manager-booking-services' ),
						'resurva_title'           => esc_html__( 'Book Online Now', 'wp-job-manager-booking-services' ),
						'resurva_subtitle'        => esc_html__( 'It just takes a few seconds.', 'wp-job-manager-booking-services' ),
						'guestful_title'           => esc_html__( 'Reserve Your Seat', 'wp-job-manager-booking-services' ),
						'guestful_subtitle'        => esc_html__( 'For a memorable dining experience', 'wp-job-manager-booking-services' ),
				) );

		return $placeholders;
	}
} // class WPJM_Booking_Services_Widget