(function($) {

	// jQuery plugin definition
	$.fn.TextAreaExpander = function(minHeight, maxHeight) {

		var hCheck = !(navigator.userAgent.match(/msie/i) || navigator.userAgent.match(/opera/i));
		// resize a textarea
		function ResizeTextarea(e) {

			// event or initialize element?
			e = e.target || e;
			// find content length and box width
			var vlen = e.value.length, ewidth = e.offsetWidth;
			if (vlen != e.valLength || ewidth != e.boxWidth) {
                // Save the scroll top position.
                //var scrollTop = $(document).scrollTop();

				//if (hCheck && (vlen < e.valLength || ewidth != e.boxWidth)) e.style.height = e.expandMin + "px";
				var h = Math.max(e.expandMin, Math.min(e.scrollHeight, e.expandMax));
				e.style.overflow = (e.scrollHeight > h ? "auto" : "hidden");
				e.style.height = (h - parseInt($(e).css("padding-top")) - parseInt($(e).css("padding-bottom"))) + "px";

                // Scroll back to where we were.
                //$.scrollTo(scrollTop);

				e.valLength = vlen;
				e.boxWidth = ewidth;
			}

			return true;
		};

		// initialize
		this.each(function() {

			// is a textarea?
			if (this.nodeName.toLowerCase() != "textarea") return;

			// set height restrictions
			var p = this.className.match(/expand(\d+)\-*(\d+)*/i);
			this.expandMin = minHeight || (p ? parseInt('0'+p[1], 10) : 0);
			this.expandMax = maxHeight || (p ? parseInt('0'+p[2], 10) : 99999);

			// initial resize
			ResizeTextarea(this);

			// zero vertical padding and add events
			if (!this.Initialized) {
				this.Initialized = true;
				//$(this).css("padding-top", 0).css("padding-bottom", 0);
				$(this).bind("keyup", ResizeTextarea).bind("focus", ResizeTextarea);
			}
		});

		return this;
	};

})(jQuery);


(function($){

$.fn.autoGrowInput = function(o) {

    o = $.extend({
        maxWidth: 1000,
        minWidth: 0,
        comfortZone: 70
    }, o);

    this.filter('input:text').each(function(){

        var minWidth = o.minWidth || $(this).width(),
            val = '',
            input = $(this),
            testSubject = $('<div/>').css({
                position: 'absolute',
                top: -9999,
                left: -9999,
                width: 'auto',
                fontSize: input.css('fontSize'),
                fontFamily: input.css('fontFamily'),
                fontWeight: input.css('fontWeight'),
                letterSpacing: input.css('letterSpacing'),
                whiteSpace: 'nowrap'
            }),
            check = function() {
                val = input.val();

                // Enter new content into testSubject
                var escaped = val.replace(/&/g, '&amp;').replace(/\s/g,' ').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                testSubject.html(escaped);

                // Calculate new width + whether to change
                var testerWidth = testSubject.width(),
                    newWidth = (testerWidth + o.comfortZone) >= minWidth ? testerWidth + o.comfortZone : minWidth,
                    currentWidth = input.width(),
                    //isValidWidthChange = (newWidth < currentWidth && newWidth >= minWidth)
                    //                     || (newWidth > minWidth && newWidth < o.maxWidth);

                // Animate width
                //if (isValidWidthChange) {
                	newWidth = Math.max(minWidth, Math.min(newWidth, o.maxWidth));
                    input.width(newWidth);
                //}

            };

        testSubject.insertAfter(input);

        $(this).bind('keyup keydown blur update', check);

    });

    return this;

};

})(jQuery);