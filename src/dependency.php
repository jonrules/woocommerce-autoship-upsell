<?php

// is woocommerce running
if ( !function_exists( 'wc_as_check_is_wc_running' ) ) {
	function wc_as_check_is_wc_running() {
		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			return true;
		} else {
			return false;

		}
	}
}


// is autoship running
if ( !function_exists( 'wc_as_running' ) ) {
	function wc_as_running() {
		if ( in_array( 'woocommerce-autoship/woocommerce-autoship.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			return true;
		} else {
			return false;

		}
	}
}

if ( ! function_exists( 'woocommerce_version_check' ) ) {

	function woocommerce_version_check( $version = '3.0' ) {
		if ( class_exists( 'WooCommerce' ) ) {
			global $woocommerce;
			if ( version_compare( $woocommerce->version, $version, ">=" ) ) {
				return true;
			}
		}
		return false;
	}
}