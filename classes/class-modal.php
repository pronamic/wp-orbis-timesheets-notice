<?php

/**
 * Modal
 */
class OTN_Modal {
	/**
	 * Construct
	 */
	public function __construct() {

		// Get data for AJAX request
		add_action( 'wp_ajax_get_data', array( $this, 'get_data' ) );

		// Add modal to footer
		add_action( 'wp_footer', array( $this, 'add_modal' ) );
	}

	/**
	 * Check for manager role
	 */
	public function is_manager() {
		$user_id = get_current_user_id();

		$management_users = array( 2, 3, 4, 7 );

		if ( in_array( $user_id, $management_users ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get data for AJAX request
	 */
	public function get_data() {
		check_ajax_referer( 'otn_get_data_nonce', 'nonce' );

		// Default response.
		$response = false;

		// User
		$user             = get_current_user_id();
		$should_register  = 0;

		// Dates
		$start_date     = strtotime( 'monday this week' );
		$end_date       = strtotime( 'today -1 day' );
		$week_day       = date( 'N', $end_date );
		$today_week_day = date( 'N' );
		$week_number    = date( 'W' );

		// Week days
		if ( 1 == $today_week_day ) {
			// Monday, checking for nothing
			$should_register = 0;

		} elseif (
			2 == $today_week_day
				||
			3 == $today_week_day
		) {
			// Tuesday or wednesday, checking for monday till friday from last week
			$start_date = strtotime( 'Monday last week' );
			$end_date   = strtotime( 'Friday last week' );

			$should_register = 40;

			// Koningsdag, bevrijdingsdag, hemelvaart, pinkster
			if (
				17 === $week_number
					||
				18 === $week_number
					||
				19 === $week_number
					||
				21 === $week_number
			) {
				$should_register = 32;
			}

			// Reuel
			if ( 13 === $user ) {
				$should_register = 36;

				// Koningsdag
				if (
					17 === $week_number
				) {
					$should_register = 32;
				}

				// Bevrijdingsdag, hemelvaart, pinkster
				if (
					18 === $week_number
						||
					19 === $week_number
						||
					21 === $week_number
				) {
					$should_register = 28;
				}
			}

			// Karel-Jan
			if ( 3 === $user ) {
				$should_register = 0;
			}

		} elseif (
			4 == $today_week_day
				||
			5 == $today_week_day
		) {
			// Thursday or friday, checking for monday till wednesday this week
			$start_date = strtotime( '-3 days' );
			$end_date   = strtotime( '-1 day' );

			$should_register = 22;

			// Koningsdag, bevrijdingsdag, hemelvaart, pinkster
			if (
				17 === $week_number
					||
				18 === $week_number
					||
				21 === $week_number
			) {
				$should_register = 14;
			}

			// Reuel
			if ( 13 === $user ) {
				$should_register = 20;

				// Koningsdag, bevrijdingsdag, hemelvaart, pinkster
				if (
					17 === $week_number
				) {
					$should_register = 16;
				}

				// Koningsdag, bevrijdingsdag, hemelvaart, pinkster
				if (
					18 === $week_number
						||
					21 === $week_number
				) {
					$should_register = 12;
				}
			}

			// Karel-Jan
			if ( 3 === $user ) {
				$should_register = 0;
			}

		} elseif ( 6 == $today_week_day ) {
			// Saterday, checking for friday this week
			$start_date = strtotime( 'Monday this week' );
			$end_date   = strtotime( 'Friday this week' );

			$should_register = 40;

			// Koningsdag, bevrijdingsdag, hemelvaart, pinkster
			if (
				17 === $week_number
					||
				18 === $week_number
					||
				19 === $week_number
					||
				21 === $week_number
			) {
				$should_register = 32;
			}

			// Reuel
			if ( 13 === $user ) {
				$should_register = 36;

				// Koningsdag
				if (
					17 === $week_number
				) {
					$should_register = 32;
				}

				// Bevrijdingsdag, hemelvaart, pinkster
				if (
					18 === $week_number
						||
					19 === $week_number
						||
					21 === $week_number
				) {
					$should_register = 28;
				}
			}

			// Karel-Jan
			if ( 3 === $user ) {
				$should_register = 0;
			}

		} elseif ( 7 == $today_week_day ) {
			// Sunday, checking for nothing
			$should_register = 0;
		}

		// Get registered hours.
		$total_hours = $this->get_hours( $start_date, $end_date, $user );

		// Set response to true if total hours are less than should register.
		if ( $total_hours < $should_register ) {
			$response = true;
		}

		if ( $response ) {
			$headers   = array( 'Content-Type: text/html; charset=UTF-8' );
			$headers[] = 'From: Orbis <noreply@pronamic.nl>';

			$body = 'Niet alle uren geboekt door: ' . get_current_user_id();

			wp_mail( 'kareljantolsma@pronamic.nl', 'Niet alle uren geboekt.', $body, $headers );
		}

		// Resturn response. True for modal. False for no modal.
		wp_send_json_success( $response );
	}

	/**
	 * Get registered hours.
	 *
	 * Get registered hours for specific date range for user.
	 *
	 * @param $start_date
	 * @param $end_date
	 * @param $user
	 */
	public function get_hours( $start_date, $end_date, $user ) {
		// Global
		global $wpdb;

		// Build query
		$query = 'WHERE 1 = 1';

		if ( $start_date ) {
			$query .= $wpdb->prepare( ' AND date >= %s', date( 'Y-m-d', $start_date ) );
		}

		if ( $end_date ) {
			$query .= $wpdb->prepare( ' AND date <= %s', date( 'Y-m-d', $end_date ) );
		}

		if ( $user ) {
			$query .= $wpdb->prepare( ' AND user_id = %d', $user );
		}

		$query_hours =  "
			SELECT
				number_seconds
			FROM
				$wpdb->orbis_timesheets
			$query
		";

		$result = $wpdb->get_results( $query_hours );

		$total_seconds = 0;

		foreach ( $result as $row ) {
			$total_seconds += $row->number_seconds;
		}

		$total_hours = $total_seconds / 60 / 60;

		return $total_hours;
	}

	/**
	 * Modal
	 *
	 * Show modal on work registration page or if day is friday.
	 */
	public function add_modal() {
		$page_id = 21280;

		if ( ! is_page( $page_id ) ) {
			$url = get_permalink( $page_id );

			$today_week_day = date( 'N' );
			$date           = strtotime( '-2 days' );
			$backdrop       = true;

			if ( 1 == $today_week_day ) {
				$date = strtotime( '-4 days' );
			} elseif ( 2 === $today_week_day ) {
				$date = strtotime( '-4 days' );
			}

			if ( 3 == $today_week_day ) {
				$backdrop = 'static';
			}
			
			$date = date( 'Y-m-d', $date );

			$url = add_query_arg( 'date', $date, $url );

			?>
			<div class="modal fade" id="orbisTimesheetsNoticeModal" backdrop="<?php echo esc_attr( $backdrop ); ?>" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-body text-center">
							<p class="h2">
								Je hebt de afgelopen dagen te weinig uren geboekt.
							</p>

							<a class="btn btn-primary btn-lg" href="<?php echo esc_url( $url ); ?>">Registreer ze nu!</a>
						</div>
					</div>
				</div>
			</div>

			<?php
		}
	}
}
