<?php

/*
Plugin Name: WC Autoship Upsell
Plugin URI: https://wooautoship.com
Description: Add autoship upsell options to the cart
Version: 0.9.9
Author: Patterns In the Cloud
Author URI: http://patternsinthecloud.com
License: Single-site
*/

define( 'WC_Autoship_Upsell_Version', '0.9.9' );

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
		'cart_upsell_url' => admin_url( 'admin-ajax.php?action=wc_autoship_upsell_cart' ),
		'cart_url' => WC()->cart->get_cart_url()
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
add_filter( 'wc_autoship_settings_addons', 'wc_autoship_upsell_settings', 10, 1 );

function wc_autoship_upsell_addon_license_keys( $addon_license_keys ) {
	if ( ! isset( $addon_license_keys['wc_autoship_upsell_license_key'] ) ) {
		$addon_license_keys['wc_autoship_upsell_license_key'] = array(
			'item_name' => 'WC Auto-Ship Upsell',
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

	ob_start();
		?>
			<div class="wc-autoship-upsell-container <?php echo $upsell_class; ?>">
				<button type="button" class="wc-autoship-upsell-cart-toggle" data-popup="#wc-autoship-upsell-cart-options-<?php echo esc_attr( $item_key ); ?>"><?php echo $upsell_title; ?></button>
				<div id="wc-autoship-upsell-cart-options-<?php echo esc_attr( $item_key ); ?>" class="wc-autoship-upsell-cart-options">
					<input type="hidden" name="wc_autoship_upsell_item_key" value="<?php echo esc_attr( $item_key ); ?>" />
					<input type="hidden" name="wc_autoship_upsell_remove_from_cart_url" value="<?php echo esc_attr( WC()->cart->get_remove_url( $item_key ) ) ?>" />
					<input type="hidden" name="wc_autoship_upsell_add_to_cart_url" value="<?php echo esc_attr( $product->add_to_cart_url() ) ?>" />
					<?php
						$theme_template = get_stylesheet_directory() . '/woocommerce-autoship/templates/upsell/autoship-options.php';
						if ( file_exists( $theme_template ) ) {
							include $theme_template;
						} else {
							echo preg_replace(
								array( '/\bid="([^"]+)"/', '/\bfor="([^"]+)"/' ),
								array( 'id="$1-' . $item_key . '"', 'for="$1-' . $item_key . '"' ),
								WC_Autoship::render_template( 'product/autoship-options', array( 'product' => $product ) )
							);
						}
					?>
					<p>
						<button type="button" class="wc-autoship-upsell-cart-submit" data-loading-text="<?php echo __( 'Please wait...', 'wc-autoship-upsell' ); ?>"><?php echo __( 'Update Auto-Ship', 'wc-autoship-upsell' ); ?></button>
					</p>
				</div>
			</div>
		<?php
	$upsell_content = ob_get_clean();
	return $name . $upsell_content;
}
add_filter( 'woocommerce_cart_item_name', 'wc_autoship_upsell_cart_item_name', 10, 3 );