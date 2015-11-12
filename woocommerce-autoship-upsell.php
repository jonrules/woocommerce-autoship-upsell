<?php

/*
Plugin Name: WC Autoship Upsell
Plugin URI: https://wooautoship.com
Description: Add autoship upsell options to the cart
Version: 1.0
Author: Patterns In the Cloud
Author URI: http://patternsinthecloud.com
License: Single-site
*/

define( 'WC_Autoship_Upsell_Version', '1.0' );

function wc_autoship_upsell_install() {

}
register_activation_hook( __FILE__, 'wc_autoship_upsell_install' );

function wc_autoship_upsell_deactivate() {

}
register_deactivation_hook( __FILE__, 'wc_autoship_upsell_deactivate' );

function wc_autoship_upsell_uninstall() {

}
register_uninstall_hook( __FILE__, 'wc_autoship_upsell_uninstall' );

function wc_autoship_upsell_scripts() {
	wp_enqueue_style( 'wc-autoship-upsell', plugin_dir_url( __FILE__ ) . 'css/style.css', array(), WC_Autoship_Upsell_Version );
	wp_enqueue_script( 'wc-autoship-upsell', plugin_dir_url( __FILE__ ) . 'js/scripts.js', array( 'jquery' ), WC_Autoship_Upsell_Version, true );
}
add_action( 'wp_enqueue_scripts', 'wc_autoship_upsell_scripts' );

function wc_autoship_upsell_cart_item_name( $name, $item, $key ) {
	return $name;
}
add_filter( 'woocommerce_cart_item_name', 'wc_autoship_upsell_cart_item_name', 10, 3 );

