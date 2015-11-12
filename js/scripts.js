jQuery(function ($) {
	$('.wc-autoship-upsell-cart-toggle').click(function () {
		var $toggle = $(this);
		$($toggle.data('target')).dialog({
			minWidth: parseInt(0.7*$(window).width())
		});
	});

	function get_frequency_val($form) {
		if ( $form.find('input[name="wc_autoship_frequency"]:checked').length > 0 ) {
			return $form.find('input[name="wc_autoship_frequency"]:checked').val();
		}
		$form.find('[name="wc_autoship_frequency"]').val();
	}

	$('.wc-autoship-upsell-cart-submit').click(function () {
		var $submit = $(this);
		$submit.attr('disabled', 'disabled');
		var $form = $submit.parent('.wc-autoship-upsell-cart-options');
		var itemKey = $form.find('input[name="wc_autoship_upsell_item_key"]').val();
		var frequency = get_frequency_val($form);
		var quantity = $('input[name="cart[' + itemKey + '][qty]"]').val();
		var data = {
			item_key: itemKey,
			frequency: frequency,
			quantity: quantity
		};
		$.post(WC_Autoship_Upsell.cart_upsell_url, data, function (response) {
			window.location = WC_Autoship_Upsell.cart_url;
		}).fail(function () {
			alert('Error');
			$submit.removeAttr('disabled');
		});
	});
})