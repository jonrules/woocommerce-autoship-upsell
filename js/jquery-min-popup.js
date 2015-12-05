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
		resizePopup(popup);
		$popup.fadeIn(200);
	}

	function resizePopup(popup) {
		var $popup = $(popup);
		var windowWidth = $(window).width();
		var windowHeight = $(window).height();
		var width = parseInt(0.7*windowWidth);
		var height = parseInt(0.7*windowHeight);
		var left = parseInt((windowWidth - width)/2);
		var top = parseInt((windowHeight - height)/2);
		$popup.css({
			width: width,
			//height: height,
			left: left,
			top: top
		});
	}

	$.fn.minPopup = function () {
		this.each(function () {
			var $popup = $(this);
			if ($popup.hasClass('min-popup')) {
				closePopup(this);
			}
			$popup.unbind('resize').bind('resize', function() { resizePopup($popup); });
			openPopup($popup);
		});

		return this;
	};

}(jQuery));