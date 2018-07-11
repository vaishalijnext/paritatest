<?php
/**
 * Currency per Product for WooCommerce - Crons Class
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_CPP_Crons' ) ) :

class Alg_WC_CPP_Crons {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		if ( 'auto' === get_option( 'alg_wc_cpp_exchange_rate_update', 'manual' ) ) {
			add_action( 'init',                             array( $this, 'schedule_the_events' ) );
			add_action( 'admin_init',                       array( $this, 'schedule_the_events' ) );
			add_action( 'alg_wc_cpp_update_exchange_rates', array( $this, 'update_the_exchange_rates' ) );
		}
	}

	/**
	 * On an early action hook, check if the hook is scheduled - if not, schedule it.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function schedule_the_events() {
		$event_hook        = 'alg_wc_cpp_update_exchange_rates';
		$selected_interval = get_option( 'alg_wc_cpp_exchange_rate_update_rate', 'daily' );
		$update_intervals  = array( 'hourly', 'twicedaily', 'daily' );
		foreach ( $update_intervals as $interval ) {
			$event_timestamp = wp_next_scheduled( $event_hook, array( $interval ) );
			if ( $selected_interval === $interval ) {
				update_option( 'alg_wc_cpp_exchange_rate_cron_time', $event_timestamp );
			}
			if ( ! $event_timestamp && $selected_interval === $interval ) {
				wp_schedule_event( time(), $selected_interval, $event_hook, array( $selected_interval ) );
			} elseif ( $event_timestamp && $selected_interval !== $interval ) {
				wp_unschedule_event( $event_timestamp, $event_hook, array( $interval ) );
			}
		}
	}

	/**
	 * On the scheduled action hook, run a function.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function update_the_exchange_rates( $interval ) {
		$currency_from = get_woocommerce_currency();
		$total_number = apply_filters( 'alg_wc_cpp', 1, 'value_total_number' );
		for ( $i = 1; $i <= $total_number; $i++ ) {
			$currency_to = get_option( 'alg_wc_cpp_currency_' . $i, $currency_from );
			update_option( 'alg_wc_cpp_exchange_rate_' . $i, alg_wc_cpp_get_exchange_rate( $currency_from, $currency_to ) );
		}
	}

}

endif;

return new Alg_WC_CPP_Crons();
