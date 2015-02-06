function MTIntervalCallbackComments(callback, interval) {
    var ic = this;
    ic.hold = false;
    ic.timeout = null;
    ic.interval = interval;
    ic.callback = callback;

    // Set a timeout to call the callback, or if we're "holding", stop holding so that the callback will be
    // run when the window regains focus.
    ic.setTimeout = function () {
        clearTimeout(ic.timeout);
        if (ic.interval <= 0) return;
        ic.timeout = setTimeout(function () {
            if (!ic.hold) ic.runCallback();
            else ic.hold = false;
        }, ic.interval * 1000);
    };

    // Run the callback, resetting the timeout and the hold flag.
    ic.runCallback = function () {
        ic.callback();
        ic.setTimeout();
        ic.hold = false;
    };

    // Reset the interval (start the timer from the beginning.)
    ic.reset = function (interval) {
        if (interval > 0) ic.interval = interval;
        ic.setTimeout();
    };

    // When the window gains focus, if we're "holding", stop holding. Otherwise, run the callback.
    $(window).focus(function (e) {
        if (e.target != window) return;
        if (ic.hold) ic.hold = false;
        else ic.runCallback();
    })

        // When the window loses focus, start "holding".
        .blur(function (e) {
            if (e.target != window) return;
            ic.hold = true;
        });

    // Set the initial timeout.
    ic.setTimeout();
}

var MTLatestComment = {

    updateInterval: null,
    ir: 1,

    // Initialize:
    init: function () {
        // Start the automatic reload timeout.
        this.updateInterval = new MTIntervalCallbackComments(this.update, MTL.updateInterval);
    },
    // Highlight a post.
    highlightComment: function (comment) {
        jQuery(comment).addClass("mt_highlight");
        setTimeout(function () {
            jQuery(comment).removeClass("mt_highlight");
        }, 5000);
    },
    // Get new posts at the end of the conversation by comparing our post count with the server's.
    update: function () {
        var interval = MTL.updateInterval;
        MTLatestComment.updateInterval.reset(interval);
        // Make the request for post data.
        var mtCL = jQuery('#mt_mtCommentLatest');
        var datTime = mtCL.children().eq(0);
        if (!datTime.length) return;
        jQuery.ajax({
            headers: {
                Action: 'latest'
            },
            url: MTL.connectorUrl,
            type: 'POST',
            data: {
                time: datTime.data('timeLatest')
            },
            success: function (data) {
                // If there are new posts, add them.
                if (data.total > 0) {
                    $.each(data.results, function (key, item) {
                        var elem = jQuery('[data-cid-latest="' + key + '"]');
                        if (elem.length === 1) {
                            elem.remove();
                        } else if (jQuery('.mt_latcomment_item').length < MTL.limit) {
                            jQuery('.mt_latcomment_item:last').remove();
                        }
                        mtCL.prepend(item);
                        MTLatestComment.highlightComment(jQuery('*[data-cid-latest="' + key + '"]'));
                        if (typeof jQuery.timeago === 'function') jQuery(".mt_time").timeago();
                    });
                }
            }
        });

    }
};

jQuery(document).ready(function () {
    if (typeof jQuery.timeago === 'function') {
        jQuery.timeago.settings.allowFuture = true;
        jQuery(".time").timeago();
    }
    MTLatestComment.init();
});
