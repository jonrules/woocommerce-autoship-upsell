jQuery(function ($) {
	$('.wc-autoship-upsell-cart-toggle').click(function () {
		var $toggle = $(this);
		$($toggle.data('target')).dialog({
			minWidth: parseInt(0.7*$(window).width())
		});
	});

	$('.wc-autoship-upsell-cart-submit').click(function () {
		var $submit = $(this);
		var $form = $submit.parent('.wc-autoship-upsell-cart-options');
		var data = $form.find('input,select').serialize();
		$.post(WC_Autoship_Upsell.cart_upsell_url, data, function (response) {
			//window.location = WC_Autoship_Upsell.cart_url;
		});
	});
})