<?php

/*
Plugin Name: WC Autoship Upsell
Plugin URI: https://wooautoship.com
Description: Add autoship upsell options to the cart
Version: 2.0.4
Author: Patterns In the Cloud
Author URI: http://patternsinthecloud.com
License: Single-site
*/

define( 'WC_Autoship_Upsell_Version', '2.0.4' );

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
	wp_enqueue_style( 'jquery-min-popup', plugin_dir_url( __FILE__ ) . 'css/jquery-min-popup.css', array(), WC_Autoship_Upsell_Version );
	wp_enqueue_script( 'jquery-min-popup', plugin_dir_url( __FILE__ ) . 'js/jquery-min-popup.js', array( 'jquery' ), WC_Autoship_Upsell_Version, true );
	wp_enqueue_style( 'wc-autoship-upsell', plugin_dir_url( __FILE__ ) . 'css/style.css', array(), WC_Autoship_Upsell_Version );
	wp_register_script( 'wc-autoship-upsell', plugin_dir_url( __FILE__ ) . 'js/scripts.js', array( 'jquery' ), WC_Autoship_Upsell_Version, true );
	wp_localize_script( 'wc-autoship-upsell', 'WC_Autoship_Upsell', array(
		'cart_upsell_url' => admin_url( '/admin-ajax.php?action=wc_autoship_upsell_cart' ),
		'cart_url' => wc_get_cart_url(),
		'get_cart_options_url' => admin_url( '/admin-ajax.php?action=get_cart_options' )
	) );
	wp_enqueue_script( 'wc-autoship-upsell' );
}
add_action( 'wp_enqueue_scripts', 'wc_autoship_upsell_scripts' );

function wc_autoship_upsell_settings( $settings ) {
	$settings[] = array(
		'name' => __( 'WC Auto-Ship Upsell Settings', 'wc-autoship-upsell' ),
		'type' => 'title',
		'desc' => __( 'Enter settings for WC Auto-Ship Upsell', 'wc-autoship-upsell' ),
		'id' => 'wc_autoship_upsell_settings'
	);
	$settings[] = array(
		'name' => __( 'License Key', 'wc-autoship-upsell' ),
		'desc' => __( 'Enter your software license key issued after purchase.', 'wc-autoship-upsell' ),
		'desc_tip' => true,
		'type' => 'text',
		'id' => 'wc_autoship_upsell_license_key'
	);
	$settings[] = array(
		'type' => 'sectionend',
		'id' => 'wc_autoship_upsell_section_end'
	);
	return $settings;
}
add_filter( 'wc_autoship_addons_settings', 'wc_autoship_upsell_settings', 10, 1 );

function wc_autoship_upsell_addon_license_keys( $addon_license_keys ) {
	if ( ! isset( $addon_license_keys['wc_autoship_upsell_license_key'] ) ) {
		$addon_license_keys['wc_autoship_upsell_license_key'] = array(
			'item_name' => 'WC Autoship Upsell',
			'license' => trim( get_option( 'wc_autoship_upsell_license_key' ) ),
			'version' => WC_Autoship_Upsell_Version,
			'plugin_file' => __FILE__
		);
	}
	return $addon_license_keys;
}
add_filter( 'wc_autoship_addon_license_keys', 'wc_autoship_upsell_addon_license_keys', 10, 1 );

function wc_autoship_upsell_cart_item_name( $name, $item, $item_key ) {
	if ( ! is_cart() ) {
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

	$product_id = 0;
	$variation_id = 0;
	if ( $product->get_type() == 'variation' ) {
		// Check deprecated property
		$product_id = method_exists( $product, 'get_parent_id' ) ? $product->get_parent_id() : $product->id;
		// Check deprecated property
		$variation_id = property_exists( $product, 'variation_id' ) ? $product->variation_id : $product->get_id();
	} else {
		// Check deprecated property
		$product_id = method_exists( $product, 'get_id' ) ? $product->get_id() : $product->id;
		// Check deprecated property
		$variation_id = 0;
	}

	$price = $product->get_price();
	$autoship_price = (float) apply_filters( 'wc_autoship_price',
			get_post_meta( $var_product_id, '_wc_autoship_price', true ),
			$var_product_id,
			0,
			get_current_user_id(),
			0
	);
	$diff = $product->get_price() - $autoship_price;
	$upsell_title = '';
	$upsell_class = '';
	if ( isset( $item['wc_autoship_frequency'] ) ) {
		$upsell_title = __( '<span class="wc-autoship-upsell-icon">&#9998;</span> Change Auto-Ship', 'wc-autoship-upsell' );
		$upsell_class = 'wc-autoship-upsell-change-autoship';
	} elseif ( $autoship_price > 0 && $diff > 0 ) {
		$upsell_title = __( '<span class="wc-autoship-upsell-icon">&plus;</span>Save ' . wc_price( $diff ) . ' with Auto-Ship', 'wc-autoship-upsell' );
		$upsell_class = 'wc-autoship-upsell-add-autoship-savings';
	} else {
		$upsell_title = __( '<span class="wc-autoship-upsell-icon">&plus;</span>Add to Auto-Ship', 'wc-autoship-upsell' );
		$upsell_class = 'wc-autoship-upsell-add-autoship';
	}
	$upsell_title = apply_filters( 'wc-autoship-upsell-title', $upsell_title, $item, $item_key );

		?>
			<div class="wc-autoship-upsell-container <?php echo $upsell_class; ?>">
				<button type="button" class="wc-autoship-upsell-cart-toggle"
					data-cart-item-key="<?php echo esc_attr( $item_key ); ?>"
					data-product-id="<?php echo esc_attr( $product_id ); ?>"
					data-variation-id="<?php echo esc_attr( $variation_id ); ?>"
					data-remove-from-cart-url="<?php echo esc_attr( WC()->cart->get_remove_url( $item_key ) ) ?>"
					data-add-to-cart-url="<?php echo esc_attr( $product->add_to_cart_url() ) ?>"><?php echo $upsell_title; ?></button>
			</div>
		<?php
	return $name;
}
add_filter( 'woocommerce_cart_item_name', 'wc_autoship_upsell_cart_item_name', 10, 3 );

function wc_autoship_upsell_after_cart() {
	?>
		<div id="wc-autoship-upsell-cart-popup">
			<div class="wc-autoship-upsell-cart-popup-loading-status"><?php echo __( 'Please wait...', 'wc-autoship-upsell' ); ?></div>
			<div class="wc-autoship-upsell-cart-popup-content">
				<div class="wc-autoship-upsell-cart-options"></div>
				<p>
					<button type="button" class="wc-autoship-upsell-cart-submit" data-loading-text="<?php echo __( 'Please wait...', 'wc-autoship-upsell' ); ?>"><?php echo __( 'Update Auto-Ship', 'wc-autoship-upsell' ); ?></button>
				</p>
			</div>
		</div>
	<?php
}
add_action( 'woocommerce_after_cart', 'wc_autoship_upsell_after_cart' );

function wc_autoship_upsell_ajax_get_cart_options() {
	$template_product_id = ! empty( $_REQUEST['variation_id'] ) ? $_REQUEST['variation_id'] : $_REQUEST['product_id'];
	$product = wc_get_product( $template_product_id );
	wc_autoship_print_cart_autoship_options( $product );
	die();
}
add_action( 'wp_ajax_get_cart_options', 'wc_autoship_upsell_ajax_get_cart_options' );
add_action( 'wp_ajax_nopriv_get_cart_options', 'wc_autoship_upsell_ajax_get_cart_options' );
