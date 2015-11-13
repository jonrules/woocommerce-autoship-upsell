(function ($) {

	function closePopup(popup) {
		var $popup = $(popup);
		$popup.fadeOut(200, function () {
			$popup.removeClass('min-popup');
			$popup.find('.min-popup-close-button').remove();
		});
	}

	function openPopup(popup) {
		var $popup = $(popup);
		$popup.addClass('min-popup');
		var $closeButton = $('<a href="#" class="min-popup-close-button">&times;</a>');
		$closeButton.click(function () {
			closePopup(popup);
			return false;
		});
		$popup.append($closeButton);

		var windowWidth = $(window).width();
		var windowHeight = $(window).height();
		var width = $popup.outerWidth(true); //parseInt(0.7*windowWidth);
		var height = $popup.outerHeight(true); //parseInt(0.7*windowHeight);
		var left = parseInt((windowWidth - width)/2);
		var top = parseInt((windowHeight - height)/2);
		$popup.css({
			width: width,
			height: height,
			left: left,
			top: top
		});
		$popup.fadeIn(200);
	}

	$.fn.minPopup = function () {
		this.each(function () {
			if ($(this).hasClass('min-popup')) {
				closePopup(this);
			}
			openPopup(this);
		});

		return this;
	};

}(jQuery));