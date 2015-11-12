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
	wp_enqueue_script( 'jquery-ui-dialog' );

	wp_enqueue_style( 'wc-autoship-upsell', plugin_dir_url( __FILE__ ) . 'css/style.css', array(), WC_Autoship_Upsell_Version );
	wp_register_script( 'wc-autoship-upsell', plugin_dir_url( __FILE__ ) . 'js/scripts.js', array( 'jquery' ), WC_Autoship_Upsell_Version, true );
	wp_localize_script( 'wc-autoship-upsell', 'WC_Autoship_Upsell', array(
		'cart_upsell_url' => admin_url( 'admin-ajax.php?action=wc_autoship_upsell_cart' ),
		'cart_url' => WC()->cart->get_cart_url()
	) );
	wp_enqueue_script( 'wc-autoship-upsell' );
}
add_action( 'wp_enqueue_scripts', 'wc_autoship_upsell_scripts' );

function wc_autoship_upsell_cart_item_name( $name, $item, $item_key ) {
	if ( isset( $item['wc_autoship_frequency'] ) ) {
		return $name;
	}

	$product_id = $item['product_id'];
	$var_product_id = ( ! empty( $item['variation_id'] ) ) ? $item['variation_id'] : $item['product_id'];
	$product = wc_get_product( $var_product_id );
	$price = $product->get_price();
	$autoship_price = apply_filters( 'wc_autoship_price',
			get_post_meta( $var_product_id, '_wc_autoship_price', true ),
			$var_product_id,
			0,
			get_current_user_id(),
			0
	);
	$autoship_min_frequency = get_post_meta( $product_id, '_wc_autoship_min_frequency', true );
	$autoship_max_frequency = get_post_meta( $product_id, '_wc_autoship_max_frequency', true );
	$autoship_default_frequency = get_post_meta( $product_id, '_wc_autoship_default_frequency', true );

	ob_start();
		?>
			<button type="button" class="wc-autoship-upsell-cart-toggle button small expand" data-target="#wc-autoship-upsell-cart-options-<?php echo esc_attr( $item_key ); ?>"><?php echo __( 'Add to Autoship', 'wc-autoship-upsell' ); ?></button>
			<div id="wc-autoship-upsell-cart-options-<?php echo esc_attr( $item_key ); ?>" class="wc-autoship-upsell-cart-options" title="<?php echo __( 'Add to Autoship', 'wc-autoship-upsell' ); ?>">
				<input type="hidden" name="wc_autoship_upsell_item_key" value="<?php echo esc_attr( $item_key ); ?>" />
				<?php WC_Autoship::include_template( 'product/autoship-options', array( 'product' => $product ) ); ?>
				<button type="button" class="wc-autoship-upsell-cart-submit button expand"><?php echo __( 'Update', 'wc-autoship-upsell' ); ?></button>
			</div>
		<?php
	$upsell_content = ob_get_clean();
	return $name . $upsell_content;
}
add_filter( 'woocommerce_cart_item_name', 'wc_autoship_upsell_cart_item_name', 10, 3 );

function wc_autoship_upsell_cart_ajax() {
	if ( isset( $_POST['wc_autoship_upsell_item_key'] ) && isset( $_POST['wc_autoship_frequency'] ) ) {
		$item = WC()->cart->get_cart_item( $_POST['wc_autoship_upsell_item_key'] );
		WC()->cart->cart_contents[ $_POST['wc_autoship_upsell_item_key'] ]['wc_autoship_frequency'] = $_POST['wc_autoship_frequency'];
		WC()->cart->maybe_set_cart_cookies();
		$cart = WC()->cart;
		header( "HTTP/1.1 200 OK" );
		die();
	}
	header( "HTTP/1.1 400 Bad Request" );
	die();
}
add_action( 'wp_ajax_wc_autoship_upsell_cart', 'wc_autoship_upsell_cart_ajax' );
add_action( 'wp_ajax_nopriv_wc_autoship_upsell_cart', 'wc_autoship_upsell_cart_ajax' );