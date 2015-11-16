jQuery(function ($) {
	$('.wc-autoship-upsell-cart-toggle').click(function () {
		var $toggle = $(this);
		$($toggle.data('popup')).minPopup();
	});

	function get_frequency_val($form) {
		if ( $form.find('input[name="wc_autoship_frequency"]:checked').length > 0 ) {
			return $form.find('input[name="wc_autoship_frequency"]:checked').val();
		}
		return $form.find('[name="wc_autoship_frequency"]').val();
	}

	$('.wc-autoship-upsell-cart-submit').click(function () {
		var $submit = $(this);
		$submit.attr('disabled', 'disabled');
		var submitText = $submit.text();
		var loadingText = $submit.data('loading-text');
		$submit.text(loadingText);
		var $form = $submit.parents('.wc-autoship-upsell-cart-options');
		var itemKey = $form.find('input[name="wc_autoship_upsell_item_key"]').val();
		var frequency = get_frequency_val($form);
		var quantity = $('input[name="cart[' + itemKey + '][qty]"]').val();
		var remove_from_cart_url = $form.find('input[name="wc_autoship_upsell_remove_from_cart_url"]').val();
		var add_to_cart_url = $form.find('input[name="wc_autoship_upsell_add_to_cart_url"]').val();
		add_to_cart_url += '&quantity=' + encodeURIComponent(quantity) + '&wc_autoship_frequency=' + encodeURIComponent(frequency);
		$.get(remove_from_cart_url, function () {
			$.get(add_to_cart_url, function () {
				window.location = WC_Autoship_Upsell.cart_url;
			}).fail(function () {
				alert('Error');
				$submit.removeAttr('disabled');
				$submit.text(submitText);
			});
		}).fail(function () {
			alert('Error');
			$submit.removeAttr('disabled');
			$submit.text(submitText);
		});
	});

});