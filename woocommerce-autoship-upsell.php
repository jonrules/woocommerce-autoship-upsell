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
	//wp_enqueue_style( 'wc-autoship-upsell-jquery-ui', 'https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css' );
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
	if ( ! is_cart() || isset( $item['wc_autoship_frequency'] ) ) {
		return $name;
	}

	$product_id = $item['product_id'];
	$autoship_enabled = get_post_meta( $product_id, '_wc_autoship_enable_autoship', true );
	if ( $autoship_enabled != 'yes' ) {
		// No autoship
		return $name;
	}
	$var_product_id = ( ! empty( $item['variation_id'] ) ) ? $item['variation_id'] : $item['product_id'];
	$product = wc_get_product( $var_product_id );
	$price = $product->get_price();
	$autoship_price = (int) apply_filters( 'wc_autoship_price',
			get_post_meta( $var_product_id, '_wc_autoship_price', true ),
			$var_product_id,
			0,
			get_current_user_id(),
			0
	);
	$diff = $product->get_price() - $autoship_price;
	$upsell_title = '';
	if ($diff > 0) {

		$upsell_title = __( 'Save ' . wc_price( $diff ) . ' with Auto-Ship', 'wc-autoship-upsell' );
	} else {
		$upsell_title = __( 'Add to Auto-Ship', 'wc-autoship-upsell' );
	}
	$upsell_title = apply_filters( 'wc-autoship-upsell-title', $upsell_title, $item, $item_key );

	ob_start();
		?>
			<div class="wc-autoship-upsell-container">
				<a class="wc-autoship-upsell-cart-toggle" data-target="#wc-autoship-upsell-cart-options-<?php echo esc_attr( $item_key ); ?>"><span class="wc-autoship-upsell-icon">&plus;</span><?php echo $upsell_title; ?></a>
				<div id="wc-autoship-upsell-cart-options-<?php echo esc_attr( $item_key ); ?>" class="wc-autoship-upsell-cart-options" title="<?php echo esc_attr( strip_tags( $upsell_title ) ); ?>">
					<input type="hidden" name="wc_autoship_upsell_item_key" value="<?php echo esc_attr( $item_key ); ?>" />
					<input type="hidden" name="wc_autoship_upsell_remove_from_cart_url" value="<?php echo esc_attr( WC()->cart->get_remove_url( $item_key ) ) ?>" />
					<input type="hidden" name="wc_autoship_upsell_add_to_cart_url" value="<?php echo esc_attr( $product->add_to_cart_url() ) ?>" />
					<?php echo preg_replace(
							array( '/\bid="([^"]+)"/', '/\bfor="([^"]+)"/' ),
							array( 'id="$1-' . $item_key . '"', 'for="$1-' . $item_key . '"' ),
							WC_Autoship::render_template( 'product/autoship-options', array( 'product' => $product ) )
					); ?>
					<button type="button" class="wc-autoship-upsell-cart-submit button expand"><?php echo __( 'Update', 'wc-autoship-upsell' ); ?></button>
				</div>
			</div>
		<?php
	$upsell_content = ob_get_clean();
	return $name . $upsell_content;
}
add_filter( 'woocommerce_cart_item_name', 'wc_autoship_upsell_cart_item_name', 10, 3 );
// WooCommerce 2.2
add_filter( 'woocommerce_in_cart_product_title', 'wc_autoship_upsell_cart_item_name', 10, 3 );