<?php
/**
 * Currency per Product for WooCommerce - General Section Settings
 *
 * @version 1.2.1
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_CPP_Settings_General' ) ) :

class Alg_WC_CPP_Settings_General extends Alg_WC_CPP_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		$this->id   = '';
		$this->desc = __( 'General', 'currency-per-product-for-woocommerce' );
		parent::__construct();
		add_action( 'admin_init', array( $this, 'update_exchange_rates_now' ) );
	}

	/**
	 * update_exchange_rates_now.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function update_exchange_rates_now() {
		if ( isset( $_GET['alg_wc_cpp_update_exchange_rates'] ) ) {
			do_action( 'alg_wc_cpp_update_exchange_rates' );
			wp_safe_redirect( remove_query_arg( 'alg_wc_cpp_update_exchange_rates' ) );
			exit;
		}
	}

	/**
	 * add_settings.
	 *
	 * @version 1.2.1
	 * @since   1.0.0
	 * @todo    (maybe) hide exchange rates on corresponding "cart behaviour" option values
	 * @todo    (maybe) automatic currency exchange rates - make free?
	 * @todo    (maybe) automatic currency exchange rates - offsets
	 * @todo    (maybe) automatic currency exchange rates - rounding
	 * @todo    (maybe) automatic currency exchange rates - `alg_wc_cpp_currency_exchange_rates_calculate_by_invert`
	 * @todo    (maybe) split in sections
	 * @todo    (maybe) 2 additional currencies in free version instead of 1
	 * @todo    (maybe) JS "grab exchange rate"
	 * @todo    (maybe) 'alg_wc_cpp_custom_number' type for 'alg_wc_cpp_total_number'
	 */
	function add_settings( $settings ) {
		$currency_from  = get_woocommerce_currency();
		$all_currencies = get_woocommerce_currencies();
		$main_settings = array(
			array(
				'title'    => __( 'Currency per Product Options', 'currency-per-product-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_cpp_options',
			),
			array(
				'title'    => __( 'Currency per Product for WooCommerce', 'currency-per-product-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable plugin', 'currency-per-product-for-woocommerce' ) . '</strong>',
				'desc_tip' => __( 'Set and display prices for WooCommerce products in different currencies.', 'currency-per-product-for-woocommerce' ),
				'id'       => 'alg_wc_cpp_enabled',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_cpp_options',
			),
		);
		$shop_settings = array(
			array(
				'title'    => __( 'Shop Behaviour Options', 'currency-per-product-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_cpp_shop_options',
			),
			array(
				'title'    => __( 'Shop behaviour', 'currency-per-product-for-woocommerce' ),
				'id'       => 'alg_wc_cpp_shop_behaviour',
				'default'  => 'show_in_different',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'show_in_different'     => __( 'Show prices in different currencies (and set cart and checkout behaviour separately)', 'currency-per-product-for-woocommerce' ),
					'convert_shop_default'  => __( 'Convert to shop default currency (including cart and checkout)', 'currency-per-product-for-woocommerce' ),
				),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_cpp_shop_options',
			),
		);
		$cart_settings = array(
			array(
				'title'    => __( 'Cart and Checkout Behaviour Options', 'currency-per-product-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_cpp_cart_options',
			),
			array(
				'title'    => __( 'Cart and checkout behaviour', 'currency-per-product-for-woocommerce' ),
				'desc'     => '<br>' . __( 'This option is ignored and always set to "Convert to shop default currency", if you selected "Convert to shop default currency" as "Shop Behaviour" option.', 'currency-per-product-for-woocommerce' ),
				'id'       => 'alg_wc_cpp_cart_checkout',
				'default'  => 'convert_shop_default',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'convert_shop_default'  => __( 'Convert to shop default currency', 'currency-per-product-for-woocommerce' ),
					'leave_one_product'     => __( 'Leave product currency (allow only one product to be added to cart)', 'currency-per-product-for-woocommerce' ),
					'leave_same_currency'   => __( 'Leave product currency (allow only same currency products to be added to cart)', 'currency-per-product-for-woocommerce' ),
					'convert_last_product'  => __( 'Convert to currency of last product in cart', 'currency-per-product-for-woocommerce' ),
					'convert_first_product' => __( 'Convert to currency of first product in cart', 'currency-per-product-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Message', 'currency-per-product-for-woocommerce' ) . ': ' . __( 'Leave product currency (allow only one product to be added to cart)', 'currency-per-product-for-woocommerce' ),
				'id'       => 'alg_wc_cpp_cart_checkout_leave_one_product',
				'default'  => __( 'Only one product can be added to the cart. Clear the cart or finish the order, before adding another product to the cart.', 'currency-per-product-for-woocommerce' ),
				'type'     => 'textarea',
				'css'      => 'width:100%',
			),
			array(
				'title'    => __( 'Message', 'currency-per-product-for-woocommerce' ) . ': ' . __( 'Leave product currency (allow only same currency products to be added to cart)', 'currency-per-product-for-woocommerce' ),
				'id'       => 'alg_wc_cpp_cart_checkout_leave_same_currency',
				'default'  => __( 'Only products with same currency can be added to the cart. Clear the cart or finish the order, before adding products with another currency to the cart.', 'currency-per-product-for-woocommerce' ),
				'type'     => 'textarea',
				'css'      => 'width:100%',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_cpp_cart_options',
			),
		);
		$additional_settings = array(
			array(
				'title'    => __( 'Additional Options', 'currency-per-product-for-woocommerce' ),
				'desc'     => __( 'Save module\'s settings after changing this options to see new settings fields.', 'currency-per-product-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_cpp_additional_options',
			),
			array(
				'title'    => __( 'Currency per product authors', 'currency-per-product-for-woocommerce' ),
				'desc'     => __( 'Enable', 'currency-per-product-for-woocommerce' ),
				'id'       => 'alg_wc_cpp_by_users_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Currency per product authors user roles', 'currency-per-product-for-woocommerce' ),
				'desc'     => __( 'Enable', 'currency-per-product-for-woocommerce' ),
				'id'       => 'alg_wc_cpp_by_user_roles_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Currency per product categories', 'currency-per-product-for-woocommerce' ),
				'desc'     => __( 'Enable', 'currency-per-product-for-woocommerce' ),
				'id'       => 'alg_wc_cpp_by_product_cats_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Currency per product tags', 'currency-per-product-for-woocommerce' ),
				'desc'     => __( 'Enable', 'currency-per-product-for-woocommerce' ),
				'id'       => 'alg_wc_cpp_by_product_tags_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_cpp_additional_options',
			),
		);
		$advanced_settings = array(
			array(
				'title'    => __( 'Advanced Options', 'currency-per-product-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_cpp_advanced_options',
			),
			array(
				'title'    => __( 'Fix mini cart', 'currency-per-product-for-woocommerce' ),
				'desc'     => __( 'Enable', 'currency-per-product-for-woocommerce' ),
				'desc_tip' => __( 'Enable this option if you have issues with currency symbol in mini cart. It will recalculate cart totals on each page load.', 'currency-per-product-for-woocommerce' ),
				'id'       => 'alg_wc_cpp_fix_mini_cart',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Currency reports', 'currency-per-product-for-woocommerce' ),
				'desc'     => __( 'Enable', 'currency-per-product-for-woocommerce' ),
				'desc_tip' => __( 'This will add currency selection to admin bar in reports.', 'currency-per-product-for-woocommerce' ),
				'id'       => 'alg_wc_cpp_currency_reports_enabled',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_cpp_advanced_options',
			),
		);
		$desc = ( 'auto' === get_option( 'alg_wc_cpp_exchange_rate_update', 'manual' ) ?
			'<a href="' . add_query_arg( 'alg_wc_cpp_update_exchange_rates', '1' ) . '">' . __( 'Update now', 'currency-per-product-for-woocommerce' ) . '</a>. ' . ( 0 != ( $exchange_rate_cron_time = get_option( 'alg_wc_cpp_exchange_rate_cron_time', 0 ) ) ?
				sprintf( __( '%s until next update.', 'currency-per-product-for-woocommerce' ), human_time_diff( $exchange_rate_cron_time ) ) : '' ) :
			''
		);
		$exchange_rates_settings = array(
			array(
				'title'    => __( 'Exchange Rates Updates Options', 'currency-per-product-for-woocommerce' ),
				'desc'     => __( 'Exchange rates for currencies <strong>won\'t be used</strong> if "Cart and Checkout Behaviour" is set to one of "Leave product currency ..." options.', 'currency-per-product-for-woocommerce' ) . ' ' . $desc,
				'type'     => 'title',
				'id'       => 'alg_wc_cpp_exchange_rate_update_options',
			),
			array(
				'title'    => __( 'Exchange rates updates', 'currency-per-product-for-woocommerce' ),
				'desc_tip' => __( 'Possible values: Enter rates manually; Update rates automatically.', 'currency-per-product-for-woocommerce' ),
				'id'       => 'alg_wc_cpp_exchange_rate_update',
				'default'  => 'manual',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'manual' => __( 'Enter rates manually', 'currency-per-product-for-woocommerce' ),
					'auto'   => __( 'Update rates automatically', 'currency-per-product-for-woocommerce' ),
				),
				'desc'     => apply_filters( 'alg_wc_cpp', sprintf(
					__( 'To enable automatic exchange rates, please get <a target="_blank" href="%s">Currency per Product for WooCommerce Pro</a> plugin.',
						'currency-per-product-for-woocommerce' ),
					'https://wpfactory.com/item/currency-per-product-for-woocommerce/' ), 'settings' ),
				'custom_attributes' => apply_filters( 'alg_wc_cpp', array( 'disabled' => 'disabled' ), 'settings' ),
			),
			array(
				'title'    => __( 'Update rate', 'currency-per-product-for-woocommerce' ),
				'id'       => 'alg_wc_cpp_exchange_rate_update_rate',
				'default'  => 'daily',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => array(
					'hourly'     => __( 'Update Hourly', 'currency-per-product-for-woocommerce' ),
					'twicedaily' => __( 'Update Twice Daily', 'currency-per-product-for-woocommerce' ),
					'daily'      => __( 'Update Daily', 'currency-per-product-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Update server', 'currency-per-product-for-woocommerce' ),
				'id'       => 'alg_wc_cpp_currency_exchange_rates_server',
				'default'  => 'yahoo',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select',
				'options'  => alg_wc_cpp_get_currency_exchange_rate_servers(),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_cpp_exchange_rate_update_options',
			),
		);
		$currencies_settings = array(
			array(
				'title'    => __( 'Currencies Options', 'currency-per-product-for-woocommerce' ),
				'desc'     => sprintf( __( 'Your shop base currency %s will be automatically added to the currencies list on product edit page, so you <strong>don\'t need</strong> to add it to the list below.', 'currency-per-product-for-woocommerce' ),
					'<code>' . $currency_from . '</code>' ),
				'type'     => 'title',
				'id'       => 'alg_wc_cpp_currencies_options',
			),
			array(
				'title'    => __( 'Total currencies', 'currency-per-product-for-woocommerce' ),
				'id'       => 'alg_wc_cpp_total_number',
				'default'  => 1,
				'type'     => 'number',
				'desc'     => apply_filters( 'alg_wc_cpp', sprintf(
					__( 'To add more than one additional currency, please get <a target="_blank" href="%s">Currency per Product for WooCommerce Pro</a> plugin.',
						'currency-per-product-for-woocommerce' ),
					'https://wpfactory.com/item/currency-per-product-for-woocommerce/' ), 'settings' ),
				'custom_attributes' => apply_filters( 'alg_wc_cpp', array( 'readonly' => 'readonly' ), 'settings_array' ),
			),
		);
		if ( 'yes' === get_option( 'alg_wc_cpp_by_users_enabled', 'no' ) ) {
			$users_as_options = alg_wc_cpp_get_users_as_options();
		}
		if ( 'yes' === get_option( 'alg_wc_cpp_by_user_roles_enabled', 'no' ) ) {
			$user_roles_as_options = alg_wc_cpp_get_user_roles_options();
		}
		if ( 'yes' === get_option( 'alg_wc_cpp_by_product_cats_enabled', 'no' ) ) {
			$product_cats_as_options = alg_wc_cpp_get_terms( 'product_cat' );
		}
		if ( 'yes' === get_option( 'alg_wc_cpp_by_product_tags_enabled', 'no' ) ) {
			$product_tags_as_options = alg_wc_cpp_get_terms( 'product_tag' );
		}
		$total_number = apply_filters( 'alg_wc_cpp', 1, 'value_total_number' );
		for ( $i = 1; $i <= $total_number; $i++ ) {
			$currency_to = get_option( 'alg_wc_cpp_currency_' . $i, $currency_from );
			$custom_attributes = array(
				'currency_from'        => $currency_from,
				'currency_to'          => $currency_to,
				'multiply_by_field_id' => 'alg_wc_cpp_exchange_rate_' . $i,
			);
			if ( $currency_from == $currency_to ) {
				$custom_attributes['disabled'] = 'disabled';
			}
			$currencies_settings = array_merge( $currencies_settings, array(
				array(
					'title'    => __( 'Currency', 'currency-per-product-for-woocommerce' ) . ' #' . $i,
					'id'       => 'alg_wc_cpp_currency_' . $i,
					'default'  => $currency_from,
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'options'  => $all_currencies,
				),
				array(
					'desc'     => __( 'Exchange rate', 'currency-per-product-for-woocommerce' ),
					'id'       => 'alg_wc_cpp_exchange_rate_' . $i,
					'default'  => 1,
					'type'     => 'number',
					'custom_attributes' => array( 'step' => '0.000001', 'min'  => '0', ),
				),
			) );
			if ( 'yes' === get_option( 'alg_wc_cpp_by_users_enabled', 'no' ) ) {
				$currencies_settings = array_merge( $currencies_settings, array(
					array(
						'desc'     => __( 'Product authors', 'currency-per-product-for-woocommerce' ),
						'id'       => 'alg_wc_cpp_users_' . $i,
						'default'  => '',
						'type'     => 'multiselect',
						'options'  =>  $users_as_options,
						'class'    => 'chosen_select',
					),
				) );
			}
			if ( 'yes' === get_option( 'alg_wc_cpp_by_user_roles_enabled', 'no' ) ) {
				$currencies_settings = array_merge( $currencies_settings, array(
					array(
						'desc'     => __( 'Product authors user roles', 'currency-per-product-for-woocommerce' ),
						'id'       => 'alg_wc_cpp_user_roles_' . $i,
						'default'  => '',
						'type'     => 'multiselect',
						'options'  =>  $user_roles_as_options,
						'class'    => 'chosen_select',
					),
				) );
			}
			if ( 'yes' === get_option( 'alg_wc_cpp_by_product_cats_enabled', 'no' ) ) {
				$currencies_settings = array_merge( $currencies_settings, array(
					array(
						'desc'     => __( 'Product categories', 'currency-per-product-for-woocommerce' ),
						'id'       => 'alg_wc_cpp_product_cats_' . $i,
						'default'  => '',
						'type'     => 'multiselect',
						'options'  =>  $product_cats_as_options,
						'class'    => 'chosen_select',
					),
				) );
			}
			if ( 'yes' === get_option( 'alg_wc_cpp_by_product_tags_enabled', 'no' ) ) {
				$currencies_settings = array_merge( $currencies_settings, array(
					array(
						'desc'     => __( 'Product tags', 'currency-per-product-for-woocommerce' ),
						'id'       => 'alg_wc_cpp_product_tags_' . $i,
						'default'  => '',
						'type'     => 'multiselect',
						'options'  =>  $product_tags_as_options,
						'class'    => 'chosen_select',
					),
				) );
			}
		}
		$currencies_settings = array_merge( $currencies_settings, array(
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_cpp_currencies_options',
			),
		) );
		return array_merge( $main_settings, $shop_settings, $cart_settings, $additional_settings, $advanced_settings, $exchange_rates_settings, $currencies_settings, $settings );
	}

}

endif;

return new Alg_WC_CPP_Settings_General();
