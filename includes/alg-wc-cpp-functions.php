<?php
/**
 * Currency per Product for WooCommerce - Functions
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! function_exists( 'alg_wc_cpp_is_wc_version_below_3' ) ) {
	/**
	 * alg_wc_cpp_is_wc_version_below_3.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @todo    (maybe) save as constant
	 */
	function alg_wc_cpp_is_wc_version_below_3() {
		return version_compare( get_option( 'woocommerce_version', null ), '3.0.0', '<' );
	}
}

if ( ! function_exists( 'alg_wc_cpp_get_product_id_or_variation_parent_id' ) ) {
	/**
	 * alg_wc_cpp_get_product_id_or_variation_parent_id.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_wc_cpp_get_product_id_or_variation_parent_id( $_product ) {
		if ( ! $_product || ! is_object( $_product ) ) {
			return 0;
		}
		if ( alg_wc_cpp_is_wc_version_below_3() ) {
			return $_product->id;
		} else {
			return ( $_product->is_type( 'variation' ) ) ? $_product->get_parent_id() : $_product->get_id();
		}
	}
}

if ( ! function_exists( 'alg_wc_cpp_get_product_display_price' ) ) {
	/**
	 * alg_wc_cpp_get_product_display_price.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_wc_cpp_get_product_display_price( $_product, $price = '', $qty = 1 ) {
		if ( alg_wc_cpp_is_wc_version_below_3() ) {
			return $_product->get_display_price( $price, $qty );
		} else {
			return wc_get_price_to_display( $_product, array( 'price' => $price, 'qty' => $qty ) );
		}
	}
}

if ( ! function_exists( 'alg_wc_cpp_get_terms' ) ) {
	/**
	 * alg_wc_cpp_get_terms.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_wc_cpp_get_terms( $args ) {
		if ( ! is_array( $args ) ) {
			$_taxonomy = $args;
			$args = array(
				'taxonomy'   => $_taxonomy,
				'orderby'    => 'name',
				'hide_empty' => false,
			);
		}
		global $wp_version;
		if ( version_compare( $wp_version, '4.5.0', '>=' ) ) {
			$_terms = get_terms( $args );
		} else {
			$_taxonomy = $args['taxonomy'];
			unset( $args['taxonomy'] );
			$_terms = get_terms( $_taxonomy, $args );
		}
		$_terms_options = array();
		if ( ! empty( $_terms ) && ! is_wp_error( $_terms ) ){
			foreach ( $_terms as $_term ) {
				$_terms_options[ $_term->term_id ] = $_term->name;
			}
		}
		return $_terms_options;
	}
}

if ( ! function_exists( 'alg_wc_cpp_get_user_roles_options' ) ) {
	/**
	 * alg_wc_cpp_get_user_roles_options.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_wc_cpp_get_user_roles_options() {
		global $wp_roles;
		$all_roles = ( isset( $wp_roles ) && is_object( $wp_roles ) ) ? $wp_roles->roles : array();
		$all_roles = apply_filters( 'editable_roles', $all_roles );
		$all_roles = array_merge( array(
			'guest' => array(
				'name'         => __( 'Guest', 'currency-per-product-for-woocommerce' ),
				'capabilities' => array(),
			) ), $all_roles );
		$all_roles_options = array();
		foreach ( $all_roles as $_role_key => $_role ) {
			$all_roles_options[ $_role_key ] = $_role['name'];
		}
		return $all_roles_options;
	}
}

if ( ! function_exists( 'alg_wc_cpp_is_user_role' ) ) {
	/**
	 * alg_wc_cpp_is_user_role.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  bool
	 * @todo    (maybe) `super_admin`
	 */
	function alg_wc_cpp_is_user_role( $user_role, $user_id = 0 ) {
		$_user = ( 0 == $user_id ) ? wp_get_current_user() : get_user_by( 'id', $user_id );
		if ( ! isset( $_user->roles ) || empty( $_user->roles ) ) {
			$_user->roles = array( 'guest' );
		}
		if ( ! is_array( $_user->roles ) ) {
			return false;
		}
		if ( is_array( $user_role ) ) {
			$_intersect = array_intersect( $user_role, $_user->roles );
			return ( ! empty( $_intersect ) );
		} else {
			return ( in_array( $user_role, $_user->roles ) );
		}
	}
}

if ( ! function_exists( 'alg_wc_cpp_get_users_as_options' ) ) {
	/**
	 * alg_wc_cpp_get_users_as_options.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @todo    (maybe) "ID" to translations
	 */
	function alg_wc_cpp_get_users_as_options() {
		$users = array();
		foreach ( get_users( 'orderby=display_name' ) as $user ) {
			$users[ $user->ID ] = $user->display_name . ' ' . '[ID:' . $user->ID . ']';
		}
		return $users;
	}
}
