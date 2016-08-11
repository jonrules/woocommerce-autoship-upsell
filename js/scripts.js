jQuery(function ($) {

	function get_frequency_val($form) {
		if ( $form.find('input[name="wc_autoship_frequency"]:checked').length > 0 ) {
			return $form.find('input[name="wc_autoship_frequency"]:checked').val();
		}
		return $form.find('[name="wc_autoship_frequency"]').val();
	}

	$('.wc-autoship-upsell-cart-toggle').click(function () {
		var $toggle = $(this);
		var $popup = $('#wc-autoship-upsell-cart-popup');
		var $options = $popup.find('.wc-autoship-upsell-cart-options');
		var $loadingStatus = $popup.find('.wc-autoship-upsell-cart-popup-loading-status');
		var $content = $popup.find('.wc-autoship-upsell-cart-popup-content');

		$loadingStatus.show();
		$content.hide();
		$popup.minPopup();
		var get_cart_options_url = WC_Autoship_Upsell.get_cart_options_url
			+ '&product_id=' + encodeURIComponent($toggle.data('product-id'))
			+ '&variation_id=' + encodeURIComponent($toggle.data('variation-id'));
		$.get( get_cart_options_url, function (response) {
			$options.html(response);
			$loadingStatus.hide();
			$content.show();
			$popup.trigger('resize');

			$content.find('.wc-autoship-upsell-cart-submit').unbind('click').bind('click', function () {
				var $submit = $(this);
				$submit.attr('disabled', 'disabled');
				var submitText = $submit.text();
				var loadingText = $submit.data('loading-text');
				$submit.text(loadingText);
				var itemKey = $toggle.data('cart-item-key');
				var frequency = get_frequency_val($options);
				var quantity = $('input[name="cart[' + itemKey + '][qty]"]').val();
				var remove_from_cart_url = $toggle.data('remove-from-cart-url');
				var add_to_cart_url = $toggle.data('add-to-cart-url');
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

	});

});