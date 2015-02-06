// modxTalks JavaScript
// Translate a string, using definitions found in MT.language. addJSLanguage() must be called on
// a controller to make a definition available in MT.language.
function T(string) {
    return typeof MT.language[string] == 'undefined' ? string : MT.language[string];
}

// An array of "loading overlays". Loading overlays can be used to cover up a certain area when new content
// is loading.
var loadingOverlays = {};

// Create a loading overlay. id should be a unique identifier so the loading overlay can be hidden with the
// same id. The loading overlay will be sized and positions to cover the element specified by coverElementWithId.
function createLoadingOverlay(id, coverElementWithId) {
    if (!loadingOverlays[id]) loadingOverlays[id] = 0;
    loadingOverlays[id]++;

    // Create a new loading overlay element if one doesn't already exist.
    if (!jQuery('#mt_loadingOverlay-' + id).length) {
        jQuery('<div/>', {
            id: 'mt_loadingOverlay-' + id
        }).addClass('mt_loadingOverlay').appendTo(jQuery('body')).hide();
    }
    var elm = jQuery('#' + coverElementWithId);

    // Style and position it.
    jQuery('#mt_loadingOverlay-' + id).css({
        opacity: 0.6,
        position: 'absolute',
        top: elm.offset().top,
        left: elm.offset().left,
        width: elm.outerWidth(),
        height: elm.outerHeight()
    }).show();
}

// Hide a loading overlay.
function hideLoadingOverlay(id, fade) {
    loadingOverlays[id]--;
    if (loadingOverlays[id] <= 0) {
        jQuery('#mt_loadingOverlay-' + id)[fade ? 'fadeOut' : 'remove']();
    }
}

// modxTalks custom AJAX plugin. This automatically handles messages, disconnection, and modal message sheets.
jQuery.MTAjax = function (options) {

    // If this request has an ID, abort any other requests with the same ID.
    if (options.id) jQuery.MTAjax.abort(options.id);

    // Prepend the full path to this forum to the URL.
    options.url = MT.assetsPath + 'connectors/connector.php';

    // Set up the error handler. If we get an error, inform the user of the "disconnection".
    var handlerError = function (XMLHttpRequestObject, textStatus, errorThrown) {
        if (!errorThrown || errorThrown == 'abort') return;

        jQuery.MTAjax.disconnected = true;

        // Save this request's information so that it can be tried again if the user clicks "try again".
        if (!jQuery.MTAjax.disconnectedRequest) jQuery.MTAjax.disconnectedRequest = options;

        // Show a disconnection message.
        MTMessages.showMessage(T('message.ajaxDisconnected'), {
            className: 'mt_warning mt_dismissable',
            id: 'ajaxDisconnected'
        });
    };

    // Set up the success handler!
    var handlerSuccess = function (result, textStatus, XMLHttpRequestObject) {

        // If the ajax system is disconnected but this request was successful, reconnect.
        if (jQuery.MTAjax.disconnected) {
            jQuery.MTAjax.disconnected = false;
            MTMessages.hideMessage('ajaxDisconnected');
        }

        // If the result is empty, don't continue. ???
        // if (!result) return;

        // If there's a URL to redirect to, redirect there.
        if (typeof result.redirect != 'undefined') {
            jQuery(window).unbind('beforeunload.ajax');
            window.location = result.redirect;
            return;
        }

        // Did we get any messages? Show them via the messages system.
        if (result.messages) {
            for (var i in result.messages) {
                if (typeof result.messages[i] != 'object') continue;
                MTMessages.showMessage(result.messages[i]['message'], result.messages[i]);
            }
        }

        // Set the request's messages variable to false if there were no messages - makes it easy for handlers to test whether there were messages or not.
        if (typeof result.messages == 'undefined' || result.messages.length < 1) result.messages = false;

        // But hold on... if the server returned a modal message, then show that, and mark the request as NOT successful.
        if (result.modalMessage) {
            MTSheet.showSheet('messageSheet', result.view, function () {
                jQuery('#mt_messageSheet .mt_buttons a').click(function (e) {
                    MTSheet.hideSheet('messageSheet');
                    e.preventDefault();
                });
            });
        }

        // If there's a success handler for this request, call it.
        if (options.success) options.success.apply(window, arguments);
    };

    // Clone the request's options and make a few of our own changes.
    var newOptions = jQuery.extend(true, {
        data: {}
    }, options);
    newOptions.error = handlerError;
    newOptions.success = handlerSuccess;
    if (MT.userId) newOptions.data.userId = MT.userId;
    if (MT.token) newOptions.data.token = MT.token;
    if (MT.ctx) newOptions.data.ctx = MT.ctx;
    if (MT.mtconversation.id) newOptions.data.conversationId = MT.mtconversation.id;
    if (MT.conversation) newOptions.data.conversation = MT.conversation;
    if (MT.mtconversation.slug) newOptions.data.slug = MT.mtconversation.slug;

    var result = jQuery.ajax(newOptions);
    if (options.id) jQuery.MTAjax.requests[options.id] = result;

    return result;
};

jQuery.extend(jQuery.MTAjax, {
    // Resume normal activity after recovering from a disconnection: clear messages and repeat the request that failed.
    resumeAfterDisconnection: function () {
        MTMessages.hideMessage('ajaxDisconnected');
        jQuery.MTAjax(this.disconnectedRequest);
        this.disconnectedRequest = false;
    },

    requests: [],
    disconnected: false,
    disconnectedRequest: null,

    // Abort a request with the specified ID.
    abort: function (id) {
        if (jQuery.MTAjax.requests[id]) {
            jQuery.MTAjax.requests[id].abort();
        }
    }
});

// Set up global AJAX defaults and handlers.
jQuery(function () {
    jQuery('<div id="mt_loading">' + T('Loading...') + '</div>').appendTo('body').hide();

    jQuery(document)
        .bind('ajaxStart', function () {
            jQuery('#mt_loading').show();
            jQuery(window).bind('beforeunload.ajax', function () {
                return T('ajaxRequestPending');
            });
        })
        .bind('ajaxStop', function () {
            jQuery('#mt_loading').fadeOut('fast');
            jQuery(window).unbind('beforeunload.ajax');
        });

    jQuery.ajaxSetup({
        timeout: 10000
    });
});

//***** MESSAGES SYSTEM

// The messages system makes it easy to show and hide messages in the lower left-hand corner of the page.
var MTMessages = {

    // An array of currently-showing messages.
    messages: {},
    index: 0,

    // Initialize the messages which are already in the container when the page loads.
    init: function () {
        var $message = jQuery('#mt_messages');
        // Gather all the messages and their information.
        var messages = [];
        $message.find('.mt_message').each(function () {
            messages.push([jQuery(this).html(), jQuery(this).attr('class')]);
        });

        // Clear the messages container, and redisplay the messages usings showMessage().
        $message.html('');
        if (messages.length) {
            for (var i in messages) {
                if (typeof messages[i] != 'object') continue;
                MTMessages.showMessage(messages[i][0], messages[i][1]);
            }
        }
    },

    // Show a message.
    showMessage: function (text, options) {
        if (typeof options == 'string') {
            options = {
                className: options
            };
        }
        if (!options) options = {};
        options.message = text;

        // If this message has an ID, hide any other message with the same ID.
        if (options.id) MTMessages.hideMessage(options.id, true);

        // Construct the message as a div, and prepend it to the messages container.
        var message = jQuery('<div class="mt_messageWrapper"><div class="mt_message"' + options.className + '">' + options.message + '</div></div>');
        jQuery('#mt_messages').prepend(message);
        // Hide it and fade it in.
        message.hide().fadeIn('fast');

        // Work out a unique ID for this message. If one wasn't provided, use a numeric index.
        var key = options.id || ++MTMessages.index;

        // Store the message information.
        MTMessages.messages[key] = [message, options];

        // If the message is dismissable, add an 'x' control to close it.
        if (!message.find('.mt_message').hasClass('undismissable')) {
            var close = jQuery('<a href="#" class="mt_control-delete mt_dismiss">&times;</a>').click(function (e) {
                e.preventDefault();
                MTMessages.hideMessage(key);
            });
            message.find('.mt_message').prepend(close);
        }

        // If the message is to auto-dismiss, set a timeout.
        message.bind('mouseenter', function () {
            message.data('hold', true);
        }).bind('mouseleave', function () {
            message.data('hold', false);
        });

        MTMessages.messages[key][2] = setTimeout(function () {
            if (!message.data('hold')) {
                MTMessages.hideMessage(key);
            } else {
                message.bind('mouseleave', function () {
                    MTMessages.hideMessage(key);
                });
            }
        }, 5 * 1000);
    },

    // Hide a message class.
    hideMessageClass: function (key, noAnimate) {
        if (!key) return;

        // Fade out the message, or just hide it if we don't want animation.
        jQuery('.' + key).fadeOut(noAnimate ? 0 : 'fast', function () {
            jQuery(this).parent().remove();
        });
    },

    // Hide a message.
    hideMessage: function (key, noAnimate) {

        // If this message doesn't exist, we can't do anything.
        if (!MTMessages.messages[key]) return;

        // Fade out the message, or just hide it if we don't want animation.
        MTMessages.messages[key][0].fadeOut(noAnimate ? 0 : 'fast', function () {
            jQuery(this).remove();
        });

        // Clear the message's hide timeout so we don't hide a future message with the same key.
        clearTimeout(MTMessages.messages[key][2]);

        // Remove the message from storage.
        delete MTMessages.messages[key];
    }
};

jQuery(function () {
    MTMessages.init();
});

// The popups system allows popups and popup menus to easily be created and shown/hidden.
var MTPopup = {

    // An array of currently-active popups.
    popups: {},

    // Show a popup.
    showPopup: function (id, button, options) {

        // If this popup is already being shown, hide it.
        if (MTPopup.popups[id]) {
            MTPopup.hidePopup(id);
            return false;
        }

        // Hide all other popups.
        MTPopup.hideAllPopups();

        // Get the popup element if it exists, or create it if it doesn't.
        var popup = jQuery('#' + id);
        if (!popup.length) {
            popup = jQuery('<div id="' + id + '"></div>').appendTo(button.parent()).hide();
        }
        popup.addClass('mt_popup');

        // Position the button's parent element (which should be a popupWrapper div) relatively so the popup within
        // it can be positioned accordingly.
        if (button.parent().css('position') != 'absolute') {
            button.parent().css('position', 'relative');
        }
        button.parent().addClass('active');

        // Make the popup button active.
        if (options.callbackOpen && typeof options.callbackOpen == 'function') {
            options.callbackOpen.call(popup);
        }

        // Show and position the popup.
        popup.css({
            position: 'absolute',
            top: button.outerHeight(true) - 1 - parseInt(button.css('marginBottom')) + (options.offset ? options.offset[1] || 0 : 0)
        })
            .css(options.alignment, 0)
            .show()
            .addClass(options.alignment)
            .data('options', options);
        this.popups[id] = popup;

        // Make sure the popup is within the document bounds.
        if (popup.offset().left < 0) popup.css(options.alignment, popup.offset().left);

        // Add a click handler to the body to hide the popup (as long as it's not a click on the popup button.)
        jQuery(document).unbind('mouseup.popup').bind('mouseup.popup', function (e) {
            if (jQuery(e.target).get(0) != button.get(0) && !button.has(e.target).length) {
                MTPopup.hidePopup(id);
            }
        });

        // However, we don't want this acting when the popup itself is clicked on.
        popup.bind('mouseup', function (e) {
            return false;
        });
    },

    // Hide a popup.
    hidePopup: function (id) {
        var popup = MTPopup.popups[id];
        if (popup) {
            popup.hide().removeClass('popup');
            popup.parent().removeClass('active');
            if (typeof popup.data('options').callbackClose == 'function') {
                popup.data('options').callbackClose.call(popup);
            }
            MTPopup.popups[id] = false;
        }
    },

    // Hide all popups.
    hideAllPopups: function () {
        for (var i in MTPopup.popups) {
            MTPopup.hidePopup(i);
        }
    }
};

// To finish off popup functionality, this jQuery plugin will convert an element into a popup, returning
// a button element which can be added somewhere on the page.
jQuery.fn.popup = function (options) {

    options = options || {};

    // Get the element to use as the popup contents.
    var popup = jQuery(this).first();
    if (!popup.length) return;

    // Construct the popup wrapper and button.
    var wrapper = jQuery('<div class="mt_popupWrapper"></div>');
    var button = jQuery('<a href="#" class="mt_popupButton button" id="' + popup.attr('id') + '-button"><span class="mt_icon-settings">Controls</span> <span class="mt_icon-dropdown"></span></a>');
    wrapper.append(button).append(popup);

    // Remove whatever class is on the popup contents and make it into a popup menu.
    popup.removeClass().hide().addClass('mt_popupMenu');

    // Add a click handler to the popup button to show the popup.
    button.click(function (e) {
        jQuery.hideToolTip();
        MTPopup.showPopup('popup-' + popup.attr('id'), button, {
            alignment: options.alignment || 'left',
            callbackOpen: function () {
                jQuery(this).append(popup.show());
                popup.find('a').click(function () {
                    MTPopup.hidePopup('popup-' + popup.attr('id'));
                });
            }
        });
        e.preventDefault();
    });

    return wrapper;
};

//***** GLOBAL PAGE STUFF
jQuery(function () {
    // Initialize page history.
    jQuery.history.init();
});

//***** INTERVAL CALLBACK

// This class allows you to set up a callback to be run at a certain interval (in seconds). If the window
// loses focus, the callback will run when the window regains focus or when the timer runs out (whichever
// comes last.)
function MTIntervalCallback(callback, interval) {
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
    jQuery(window)
        .focus(function (e) {
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

// Conversation JavaScript
var MTConversation = {

    // Conversation details.
    id: 0,
    title: '',
    channel: '',
    slug: '',
    startFrom: 0,
    postCount: 0,
    scrubberTopDef: 0,

    updateInterval: null,
    editingReply: false, // Are we typing a reply?
    userunfolike: false,
    editingPosts: 0, // Number of posts being edited.
    itemsComment: [], // Array of unread comments

    // Initialize:
    init: function () {
        // If we're viewing an existing conversation...
        if (MT.conversation) {
            // Get the details.
            this.id = MT.mtconversation.id;
            this.postCount = MT.mtconversation.countPosts;
            this.startFrom = MT.mtconversation.startFrom;
            this.slug = MT.conversation;

            // Set up the timeline scrubber.
            MTScrubber.body = jQuery('#mt_conversationBody');
            MTScrubber.header = MTScrubber.body.find('.mt_mthead');
            MTScrubber.scrubber = MTScrubber.body.find('.mt_scrubberContent');
            MTScrubber.items = jQuery('#mt_conversationPosts');
            MTScrubber.converReply = jQuery('#mt_cf_conversationReply');
            MTScrubber.count = this.postCount;
            MTScrubber.perPage = MT.postsPerPage;
            MTScrubber.moreText = MT.language.moreText;
            MTScrubber.startFrom = this.startFrom;
            MTScrubber.lastRead = MT.mtconversation.lastRead;
            MTConversation.slug = MT.mtconversation.slug;

            // If there's a post ID in the URL hash (eg. p1234), highlight that post, and scroll to it.
            var wlh = window.location.hash;
            var hash = window.location.hash.replace('#', '');
            var idxcomm = wlh.substr(9).length > 0
                ? jQuery('*[data-idx="' + wlh.substr(9) + '"]')
                : false;

            if (wlh.substr(0, 9) == '#comment-' && idxcomm.length) {
                MTConversation.highlightPost(idxcomm);
                setTimeout(function () {
                    MTConversation.scrollTo(idxcomm.offset().top - 10);
                }, 100);
                jQuery.history.load(window.location.pathname.substr(1), true);
            } else {
                if (window.location.pathname.indexOf('-last-mt') > 0 && this.postCount > 0) {
                    lastIt = jQuery('*[data-idx="' + this.postCount + '"]');
                    jQuery('html, body').animate({
                        scrollTop: lastIt.offset().top - MT.scrubberOffsetTop
                    });
                    MTConversation.highlightPost(lastIt);
                    //MTScrubber.scrollToIndex(this.postCount);
                } else if (this.startFrom > 1) {
                    MTScrubber.scrollToIndex(this.startFrom);
                    MTConversation.highlightPost(jQuery('*[data-idx="' + this.startFrom + '"]'));
                }
            }

            // Process the text selection and display button quote
            var $txt,
                node;
            jQuery(MTScrubber.items).on('mouseup', '.mt_postBody', function (e) {
                if (window.getSelection) {
                    $txt = window.getSelection();
                    node = $txt.anchorNode;
                }
                else if (document.getSelection) {
                    $txt = document.getSelection();
                    node = $txt.anchorNode;
                }
                else if (document.selection) {
                    $txt = document.selection.createRange().text;
                    var range = $txt.getRangeAt ? $txt.getRangeAt(0) : $txt.createRange();
                    node = range.commonAncestorContainer ? range.commonAncestorContainer : range.parentElement ? range.parentElement() : range.item(0);
                }
                else {
                    return;
                }
                if ($txt != '') {
                    jQuery('#mt_MTpopUpBox').css({
                        display: 'block',
                        left: e.pageX - 60 + 'px',
                        top: e.pageY + 5 + 'px'
                    });
                }
            });

            jQuery(document).bind('mousedown', function () {
                jQuery('#mt_MTpopUpBox').css({
                    'display': 'none'
                });
            });

            jQuery(document).bind('mousedown', function () {
                jQuery('#MTpopUpBox').css({'display': 'none'});
            });

            jQuery('#mt_MTpopUpBox').bind('mousedown', function () {
                postq = jQuery(this).parents('.mt_post');
                var postId = postq.data('id');
                var postIdx = jQuery(this).parents('.mt_mtComment').data('idx');
                parNod = jQuery(node.parentNode).parents('.mt_post');
                var idNod = parNod.data('id') || null;
                var idxNod = parNod.parents('.mt_mtComment').data('idx') || null;
                var nameNode = parNod.find('.mt_info h3').text() || null;
                if (nameNode) nameNode = '"' + nameNode + '"';
                MTConversation.quote('mt_replay', $txt, nameNode, idNod, null, true, idxNod);
                jQuery('#mt_jumpToReply').click();
                MTConversation.scrollTo(MTScrubber.converReply.offset().top - 10);
            });

            // conclusion of users who voted for the comment
            MTConversation.figures();

            jQuery('.mt_post').find('.mt_likehov').hover(function () {
                    jQuery(this).addClass('hover');
                },
                function () {
                    jQuery(this).removeClass('hover');
                });

            MTScrubber.items.on('click', '.mt_likehov', function () {
                jQuery('#mt_box_user_info').remove();
                MTConversation.userunfolike = false;
                var itemBox = jQuery(this).parents('.mt_like_block');
                item = jQuery(this).parents('.mt_post');
                var iditem = item.data('id');
                var itUser = '';
                jQuery.MTAjax({
                    headers: {
                        Action: 'votes_info'
                    },
                    type: 'POST',
                    data: {
                        id: iditem
                    },
                    beforeSend: function () {
                        itemBox.append('<div id="mt_box_user_info"><span class="mt_loaduser">' + T('Loading...') + '</span></div>');
                    },
                    complete: function () {
                        itemBox.children('#mt_box_user_info').html(itUser);
                        MTConversation.userunfolike = true;
                    },
                    success: function (data) {
                        if (data.success != false) {
                            jQuery.each(data.object.users, function (i, item) {
                                itUser += '<span class="mt_user_comment_like"><img src="' + item.avatar + '" alt="' + item.name + '" class="mt_avatar_user_comment_like">' + item.name + '</span>';
                            });
                        }
                    }
                });
                jQuery(document).click(function (e) {
                    if (MTConversation.userunfolike == false) return;
                    jQuery('#mt_box_user_info').remove();
                    MTConversation.userunfolike = false;
                });
            });

            // Set a callback that will load new post data.
            MTScrubber.loadItemsCallback = function (position, success, index) {
                if (position == Infinity) {
                    position = (MTScrubber.count - MTScrubber.perPage) < MTScrubber.perPage ? 1 : MTScrubber.count - MTScrubber.perPage + 1; //"999999"; // Kind of hackish? Meh...
                }
                // If this "position" is an index in the timeline (eg. 201004), split it into year/month for the request.
                if (index && position != 0) {
                    positionNew = ('' + position).substr(0, 4) + '-' + ('' + position).substr(4, 2);
                    if (positionNew.charAt(positionNew.length - 1) != '-') position = positionNew;
                }
                jQuery.MTAjax({
                    headers: {
                        Action: 'load'
                    },
                    type: 'POST',
                    data: {
                        start: position
                    },
                    success: function (data) {
                        var addIt = {};
                        addIt.view = '';
                        if (data.success != false) {
                            jQuery.each(data.results, function (i, item) {
                                if (i == 0) {
                                    addIt.startFrom = item.idx;
                                }
                                if (item.delete_date) {
                                    addIt.view += new EJS({
                                        url: MT.assetsPath + MT.deletedCommentTpl
                                    }).render(item);
                                } else {
                                    addIt.view += new EJS({
                                        url: MT.assetsPath + MT.commentTpl
                                    }).render(item);
                                }
                            });
                            MTScrubber.count = data.total;
                            MTConversation.redisplayAvatars();
                        }
                        success(addIt);
                    },
                    global: false
                });
            };

            // Set a callback that will run whenever we scroll to a specific index. We need it to change the URL.
            MTScrubber.scrollToIndexCallback = function (index) {
                var position;
                if (index == Infinity) position = 'last';
                else position = ('' + index).substr(0, 4) + '-' + ('' + index).substr(4, 2);
                if ((position.charAt(position.length - 1)) == '-') position = position.slice(0, -1);
                jQuery.history.load(MTConversation.slug.substr(1).replace('$$$', position), true);
            };

            // Initialize the scrubber.
            MTScrubber.init();

            // When the "add a reply" button in the sidebar is clicked, we trigger a click on the "now" item in
            // the scrubber, and also on the reply textarea.
            jQuery('#mt_jumpToReply').click(function (e) {
                jQuery('.mt_scrubber-now a').click();
                setTimeout(function () {
                    jQuery('#mt_replay').find('textarea').click();
                }, 1);
                e.preventDefault();
            });

            // Start the automatic reload timeout.
            this.updateInterval = new MTIntervalCallback(this.update, MT.conversation.updateInterval); //MT.mtconversation.updateInterval

            function widthscr() {
                jQuery('.mt_scrubberContent').width(MTScrubber.body.width());
            }

            if (MTScrubber.body.width() < 570) MTScrubber.body.addClass('mt_scrubber-top');

            if (MTScrubber.body.hasClass('mt_scrubber-top')) {
                MTConversation.scrubberTopDef = 1;
                widthscr();
                jQuery(window).resize(function () {
                    widthscr();
                });

            }

            jQuery(window).resize(function () {
                if (MTScrubber.body.width() < 570 && MTConversation.scrubberTopDef == 0) {
                    MTScrubber.body.addClass('mt_scrubber-top');
                } else if (MTConversation.scrubberTopDef == 0) {
                    MTScrubber.body.removeClass('mt_scrubber-top');
                }
            });

            jQuery('a[rel="comment"]').click(function (e) {
                // Comment Idx
                var idcom = jQuery(this).attr('data-id'),
                    comment = jQuery('*[data-idx="' + idcom + '"]'); //'#comment-' + idcom;
                // If comment exists
                if (comment) {
                    // Scroll to first new comment
                    MTConversation.scrollTo(comment.offset().top - 10);
                    // Highlight comment
                    MTConversation.highlightPost(comment);
                    e.preventDefault();
                }
            });

            jQuery('.mt_tt-mt').click(function (e) {
                // First comment Id
                var idcom = MTConversation.itemsComment[0],
                    comment = jQuery('#comment-' + idcom);
                if (!idcom) {
                    return false;
                }
                // Remove first comment from comments array
                MTConversation.itemsComment.shift();
                // Count of new comments without first
                var textComment = MTConversation.itemsComment.length;
                // Increment total comments count
                jQuery('.mt_big_count span').text(textComment);
                if (textComment == 0) {
                    jQuery('.mt_scrubber_total').addClass('mt_noncom');
                }
                // Scroll to first new comment
                MTConversation.scrollTo(comment.offset().top - 10);
                // Highlight comment
                MTConversation.highlightPost(comment);
                e.preventDefault();
            });

            // Initialize the posts.
            this.initPosts();

        } else {
        }

        // If there's a reply box, initilize it.
        if (jQuery('#mt_replay').length) MTConversation.initReply();

        jQuery('input.mt_saveNamen').focus(function () {
            MTMessages.hideMessageClass('mt_msg-name');
        });
        jQuery('input.mt_saveEmail').focus(function () {
            MTMessages.hideMessageClass('mt_msg-email');
        });
    },

    // Scroll to a specific position, applying an animation and taking the fixed conversation header into account
    scrollTo: function (position) {
        MTScrubber.scrollTo(position);
    },

    figures: function () {
        // know if the figures in the text
        jQuery('.mt_post .mt_likes').each(function (i, elem) {
            textEl = jQuery(elem).text();
            if (/([0-9])+/g.test(textEl)) {
                jQuery(this).addClass('mt_likehov');
            }
        });
    },

    // Get new posts at the end of the conversation by comparing our post count with the server's.
    update: function () {
        var interval = MT.conversationUpdateIntervalStart;
        MTConversation.updateInterval.reset(interval);
        // Don't do this if we're searching, or if we haven't loaded the end of the conversation.
        // if (MTConversation.searchString || MTScrubber.loadedItems.indexOf(MTConversation.postCount - 1) == -1) return;
        if (MTScrubber.loadedItems.indexOf(MTConversation.postCount - 1) == -1) return;
        // Make the request for post data.
        jQuery.MTAjax({
            headers: { Action: 'load' },
            type: 'post',
            data: { start: parseInt(MTConversation.postCount) + 1 },
            success: function (data) {
                // If there are new posts, add them.
                if (MTConversation.postCount < data.total) {
                    jQuery('.mt_total_mt').text(data.total);
                    var comments_count =jQuery('.mt_count_comments');
                    if (comments_count.length) {
                        comments_count.text(data.total);
                    }
                    MTConversation.postCount = data.total;
                    var addIt = {};
                    addIt.view = '';
                    jQuery.each(data.results, function (i, item) {
                        MTConversation.itemsComment.push(item.id);
                        if (i == 0) addIt.startFrom = item.idx;
                        if (item.delete_date) {
                            addIt.view += new EJS({
                                url: MT.assetsPath + MT.deletedCommentTpl
                            }).render(item);
                        } else {
                            addIt.view += new EJS({
                                url: MT.assetsPath + MT.commentTpl
                            }).render(item);
                        }
                    });

                    var textComment = MTConversation.itemsComment.length;
                    jQuery('.mt_big_count span').text(textComment);
                    MTMessages.showMessage(textComment + ' ' + MT.language.newComment, 'autoDismiss');
                    if (textComment > 0) jQuery('.mt_scrubber_total').removeClass("mt_noncom");
                    // Create a dud "more" block and then add the new post to it.
                    var moreItem = jQuery('<div></div>').appendTo('#mt_conversationPosts');
                    MTScrubber.count = MTConversation.postCount;
                    MTScrubber.addItems(addIt.startFrom, addIt.view, moreItem, true);

                    MTConversation.figures();
                }
                // Otherwise, multiply the update interval by our config setting.
                else {
                    var interval = Math.min(MT.conversationUpdateIntervalLimit, MTConversation.updateInterval.interval * MT.conversationUpdateIntervalMultiplier);
                }
                MTConversation.updateInterval.reset(interval);
            },
            global: false
        });

    },

    // Initialize the posts.
    initPosts: function () {
        jQuery(MTScrubber.items).on('click', '.mt_control-restore', function (e) {
            var postId = jQuery(this).parents('.mt_post').data('id');
            MTConversation.restorePost(postId);
            e.preventDefault();
        });

        // Add a click handler to any "post links" to scroll back up to the right post, if it's loaded.
        jQuery(MTScrubber.items).on('click', '.mt_postBody a[rel="mt_post"]', function (e) {
            var id = jQuery(this).data('id');

            jQuery('#mt_conversationPosts').find('.mt_post').each(function () {
                if (jQuery(this).data('id') == id) {
                    MTConversation.scrollTo(jQuery(this).offset().top - 10);
                    MTConversation.highlightPost(jQuery('#mt_p' + id));
                    e.preventDefault();
                    return false;
                }
            });
        });
    },

    replyShowing: false,

    // Initialize the reply section: disable/enable buttons, add click events, etc.
    initReply: function () {
        var $replay = jQuery('#mt_replay');
        var $textarea = $replay.find('textarea');
        MTConversation.editingReply = false;

        // Auto resize our reply textareas
        $textarea.TextAreaExpander(200, 700);

        if (!$textarea.val()) {
            $replay.find('.mt_postReply').disable();
            $replay.find('.mt_discardDraft').disable();
        }

        $replay.find('.mt_saveDraft').disable();

        // Add event handlers on the textarea to enable/disable buttons.
        $textarea.keyup(function (e) {
            if (e.ctrlKey) return;
            $replay.find('.mt_postReply')[$textarea.val() ? 'enable' : 'disable']();
            $replay.find('.mt_saveDraft')[$textarea.val() ? 'enable' : 'disable']();
            MTConversation.editingReply = $textarea.val() ? true : false;
        });

        // Add click events to the buttons.
        $replay.find('.mt_postReply').click(function (e) {
            if (MT.conversation) MTConversation.addReply();
            e.preventDefault();
        });

        $replay.click(function (e) {
            if (!MTConversation.replyShowing) {
                jQuery(this).trigger('change');

                // Save the scroll position and then focus on the textarea.
                var scrollTop = jQuery(document).scrollTop();
                $textarea.focus();
                jQuery.scrollTo(scrollTop);

                MTConversation.scrollTo($replay.offset().top - 10);
            }
            e.stopPropagation();
        });

        $replay.change(function () {
            if (!MTConversation.replyShowing) {
                MTConversation.replyShowing = true;
                $replay.removeClass('mt_replayPlaceholder');

                // Put the cursor at the end of the textarea.
                var pos = $textarea.val().trim().length;
                $textarea.selectRange(pos, pos);
            }
        });

        jQuery(document).click(function () {
            MTConversation.hideReply();
        });
    },

    // Highlight a post.
    highlightPost: function (post) {
        jQuery(post).addClass('mt_highlight');
        setTimeout(function () {
            jQuery(post).removeClass('mt_highlight');
        }, 5000);
    },

    // Hide consecutive avatars from the same member.
    redisplayAvatars: function () {
        var prevId = null;
        jQuery('#mt_conversationPosts').siblings('.mt_mtComment').each(function () {
            var id = jQuery(this).find('div.mt_post').data('memberid');
            if (prevId == id) {
                jQuery(this).find('div.mt_avatar').hide();
            } else {
                jQuery(this).find('div.mt_avatar').show();
            }
            prevId = id;
        });
    },

    // Delete a comment
    commentDelete: function (btn) {
        if (!confirm(MT.language['message.confirmDelete'])) return;

        var $btn = jQuery(btn),
            postId = $btn.parents('.mt_post').data('id'),
            post = jQuery('#comment-' + postId);

        jQuery.MTAjax({
            headers: {
                Action: 'delete'
            },
            type: 'post',
            data: {
                id: postId
            },
            beforeSend: function () {
                createLoadingOverlay('comment-' + postId, 'comment-' + postId);
            },
            complete: function () {
                hideLoadingOverlay('comment-' + postId, true);
            },
            success: function (data) {
                if (MT.fullDeleteComment === true) {
                    MTMessages.showMessage(data.message);
                    setTimeout(function () {
                        window.location.reload();
                    }, 500);
                    return false;
                }

                if (data.message === false) return;

                data.object.link_restore = MTConversation.slug.replace('$$$', 'mt_restore-' + postId);
                data.object.timeMarker = '';
                post.replaceWith(new EJS({
                    url: MT.assetsPath + MT.deletedCommentTpl
                }).render(data.object));
                MTConversation.redisplayAvatars();
            }
        });
    },
    // Restore a comment
    commentRestore: function (id, btn) {
        if (!confirm(MT.language['message.confirmRestore'])) return;

        // Make the ajax request.
        jQuery.MTAjax({
            headers: { Action: 'restore' },
            type: 'post',
            data: { id: id },
            beforeSend: function () {
                createLoadingOverlay('mt_comment-' + id, 'mt_comment-' + id);
            },
            complete: function () {
                hideLoadingOverlay('comment-' + id, true);
            },
            success: function (data) {
                if (data.success === false) return;
                data.object.link_restore = MTConversation.slug.replace('$$$', 'mt_delete-' + id);
                data.object.timeMarker = '';
                jQuery('#mt_comment-' + id).replaceWith(new EJS({
                    url: MT.assetsPath + MT.commentTpl
                }).render(data.object));
                MTConversation.redisplayAvatars();
                jQuery('pre').each(function (i, e) {
                    hljs.highlightBlock(e)
                });
            }
        });
    },

    // Edit a post.
    commentEdit: function (btn) {
        var $btn = jQuery(btn),
            postId = $btn.parents('.mt_post').data('id'),
            post = jQuery('#comment-' + postId);

        // Make the ajax request.
        jQuery.MTAjax({
            headers: { Action: 'get' },
            type: 'post',
            data: { id: postId },
            beforeSend: function () {
                createLoadingOverlay('comment-' + postId, 'comment-' + postId);
            },
            complete: function () {
                hideLoadingOverlay('comment-' + postId, true);
            },
            success: function (data) {
                if (data.success === false) return;
                MTConversation.editingPosts++;
                var startHeight = jQuery('.mt_postContent', post).height();
                // Replace the post HTML with the new stuff we just got.
                post.replaceWith(jQuery(data.object.html).find('.mt_post'));
                var newPost = jQuery('#mt_comment-' + postId);
                var textarea = jQuery('textarea', newPost);

                // Save the old post HTML for later.
                newPost.data('mt_oldPost', post);

                // Set up the text area.
                var len = textarea.val().length;
                textarea.TextAreaExpander(200, 700).focus().selectRange(len, len);

                // Add click handlers to the cancel/submit buttons.
                jQuery('.mt_cancel', newPost).click(function (e) {
                    e.preventDefault();
                    MTConversation.cancelEditPost(postId);
                });
                jQuery('.mt_submit', newPost).click(function (e) {
                    e.preventDefault();
                    MTConversation.saveEditPost(postId, textarea.val());
                });

                // Animate the post's height.
                var newHeight = jQuery('.mt_postContent', newPost).height();
                jQuery('.mt_postContent', newPost).height(startHeight).animate({
                    height: newHeight
                }, 'fast', function () {
                    jQuery(this).height('');
                });

                MTConversation.redisplayAvatars();

                // Scroll to the bottom of the edit area if necessary.
                var scrollTo = newPost.offset().top + newHeight - (window.innerHeight || docElemProp) + 10;
                if (jQuery(document).scrollTop() < scrollTo) {
                    jQuery.scrollTo(scrollTo, 'slow');
                }

                // Regsiter the Ctrl+Enter and Escape shortcuts on the post's textarea.
                textarea.keydown(function (e) {
                    if (e.ctrlKey && e.which == 13) {
                        MTConversation.saveEditPost(postId, this.value);
                        e.preventDefault();
                    }

                    if (e.which == 27) {
                        MTConversation.cancelEditPost(postId);
                        e.preventDefault();
                    }
                });
            }
        });
    },

    // Save an edited post to the database.
    saveEditPost: function (postId, content) {
        // Disable the buttons.
        var post = jQuery('#mt_comment-' + postId);
        jQuery('.mt_button', post).disable();

        // Make the ajax request.
        jQuery.MTAjax({
            headers: {
                Action: 'edit'
            },
            type: 'post',
            data: {
                content: content,
                id: postId
            },
            beforeSend: function () {
                createLoadingOverlay('comment-' + postId, 'mt_comment-' + postId);
            },
            complete: function () {
                hideLoadingOverlay('comment-' + postId, true);
                jQuery('.button', post).enable();
            },
            success: function (data) {
                if (data.success === false) return;

                var startHeight = jQuery('.mt_postContent', post).height();

                // Replace the post HTML with the new post we just got.
                post.replaceWith(new EJS({
                    url: MT.assetsPath + MT.commentTpl
                }).render(data.object));
                var newPost = jQuery('#comment-' + postId);

                // Animate the post's height.
                var newHeight = jQuery('.mt_postContent', newPost).height();
                jQuery('.mt_postContent', newPost).height(startHeight).animate({
                    height: newHeight
                }, 'fast', function () {
                    jQuery(this).height('');
                });

                MTConversation.editingPosts--;
                MTConversation.redisplayAvatars();
                jQuery('pre').each(function (i, e) {
                    hljs.highlightBlock(e)
                });
            }
        });
    },

    // Cancel editing a post.
    cancelEditPost: function (postId) {
        MTConversation.editingPosts--;
        var post = jQuery('#mt_comment-' + postId);

        var scrollTop = jQuery(document).scrollTop();

        // Change the post control and body HTML back to what it was before.
        var startHeight = jQuery('.mt_postContent', post).height();
        post.replaceWith(post.data('mt_oldPost'));
        var newPost = jQuery('#mt_comment-' + postId);

        // Animate the post's height.
        var newHeight = jQuery('.mt_postContent', newPost).height();
        jQuery('.postContent', newPost).height(startHeight).animate({
            height: newHeight
        }, 'fast', function () {
            jQuery(this).height('');
        });

        jQuery.scrollTo(scrollTop);
    },

    // scroll to element
    scrollElem: function (elm) {
        jQuery.scrollTo(elm);
    },

    commentQuote: function (btn) {
        var $btn = jQuery(btn),
            $comment = $btn.closest('.mt_post');

        MTConversation.quotePost($comment.data('id'), $comment.parent().data('idx'), true)
    },

    // Quote a post.
    quotePost: function (id, idx, multi) {
        var selection = '' + jQuery.getSelection();
        jQuery.MTAjax({
            headers: {
                Action: 'quote'
            },
            type: 'post',
            data: {
                id: id
            },
            success: function (data) {
                if (data.success === false && data.total == 0) {
                    MTMessages.showMessage(data.message, 'mt_msg-error');
                    return;
                }

                var top = jQuery(document).scrollTop();
                MTConversation.quote('mt_replay', selection ? selection : data.object.content, data.object.user, data.object.id, null, true, idx);
                // If we're "multi" quoting (i.e. shift is being held down), keep our scroll position static.
                // Otherwise, scroll down to the reply area.
                if (!multi) {
                    jQuery('#mt_jumpToReply').click();
                } else {
                    jQuery('#mt_replay').find('textarea').change();
                    jQuery.scrollTo('#mt_replay');
                }
            },
            global: true
        });
    },

    // Condense the reply box back into a placeholder.
    hideReply: function () {
        var replay = jQuery('#mt_replay');
        if (!MTConversation.replyShowing || replay.find('textarea').val()) return;
        // Save the scroll top and height.
        var scrollTop = jQuery(document).scrollTop();
        var oldHeight = replay.find('.mt_postContent').height();
        MTConversation.replyShowing = false;
        replay.addClass('mt_replayPlaceholder');
        var newHeight = replay.find('.mt_postContent').height();
        replay.find('.mt_postContent').height(oldHeight).animate({
            height: newHeight
        }, 'fast', function () {
            jQuery(this).height('');
        });
    },
    // Add a reply.
    addReply: function () {
        var replay = jQuery('#mt_replay');
        var content = replay.find('textarea').val();
        var saveemail = replay.find('.mt_saveEmail').val();
        var savename = replay.find('.mt_saveName').val();

        // Disable the reply/draft buttons.
        jQuery("#mt_replay .mt_postReply, #mt_replay .mt_saveDraft").disable();

        // Make the ajax request.
        jQuery.MTAjax({
            type: 'post',
            headers: {
                Action: 'add'
            },
            data: {
                content: content,
                name: savename,
                email: saveemail,
                conversation: MT.conversation
            },
            success: function (data) {

                if (data.success != true) {
                    jQuery("#mt_replay .mt_postReply, #mt_replay .mt_saveDraft").enable();
                    if (data.message && data.message.length > 0) {
                        MTMessages.showMessage(data.message, 'mt_msg-error');
                        if (data.premoderated === true) {
                            jQuery('#mt_replay').find('textarea').val('');
                            MTConversation.togglePreview('mt_replay', false);
                            MTConversation.hideReply();
                        }
                    }
                    if (data.data && data.data.length > 0) {
                        jQuery.each(data.data, function (i, item) {
                            MTMessages.showMessage(item.msg, 'mt_msg-' + item.id);
                        });
                    }
                    return;
                }

                // Hide messages which may have been previously triggered.
                MTMessages.hideMessage('mt_waitToReply');
                MTMessages.hideMessage('mt_emptyPost');

                jQuery("#mt_conversationHeader .mt_labels .mt_label-draft").remove();
                jQuery('#mt_replay').find('textarea').val('');
                MTConversation.togglePreview('mt_replay', false);
                MTConversation.hideReply();

                MTConversation.postCount++;

                // Create a dud "more" block and then add the new post to it.
                var moreItem = jQuery('<div/>').appendTo('#mt_conversationPosts');
                MTScrubber.count = MTConversation.postCount;
                // data.object.link = MT.link + '#comment-' + data.object.id;
                idComm = data.object.id;
                newComment = new EJS({
                    url: MT.assetsPath + MT.commentTpl
                }).render(data.object);
                MTScrubber.addItems(MTConversation.postCount, newComment, moreItem, true);
                MTConversation.redisplayAvatars();
                MTConversation.highlightPost('#mt_comment-' + idComm);
                jQuery('.mt_total_mt').text(MTConversation.postCount);
                var comments_count = jQuery('.mt_count_comments');
                if (comments_count.length) {
                    comments_count.text(MTConversation.postCount);
                }
                jQuery('pre').each(function (i, e) {
                    hljs.highlightBlock(e)
                });

                // Reset the post-checking timeout.
                MTConversation.updateInterval.reset(MT.conversationUpdateIntervalStart);
                jQuery('#reply-previewCheckbox').attr('checked', false);

            },
            beforeSend: function () {
                createLoadingOverlay('reply', 'mt_replay');
            },
            complete: function () {
                hideLoadingOverlay('reply', false);
            }
        });
    },

    // block IP && Email user
    ban: function (type, value, btn) {
        if (!confirm(T('message.confirm_' + type) + value)) return;

        jQuery.MTAjax({
            headers: { Action: 'ban_' + type },
            type: 'post',
            data: { value: value },
            success: function (data) {
                MTMessages.showMessage(data.message, 'autoDismiss');
                if (data.success != false) {
                    btn.classList.add('block');
                }
            }
        });
    },

    //***** POST FORMATTING

    // Add a quote to a textarea.
    quote: function (id, quote, name, postId, insert, hrzn, idx) {
        var argument = postId || name ? (idx ? idx + ' user=' : ' ') + (name ? name : 'Name') : ' ';
        var startTag = '[quote' + (argument && argument != ' ' ? ' id=' + argument : '') + ']' + (quote ? quote + ' ' : ' ');
        var endTag = '[/quote]';

        // If we're inserting the quote, add it to the end of the textarea.
        if (insert) {
            MTConversation.insertText(jQuery('#' + id).find('textarea'), startTag + endTag + "\n");
        } else {
            MTConversation.wrapText(jQuery('#' + id).find('textarea'), startTag, endTag);
        }
    },

    // Add text to the reply area at the very end, and move the cursor to the very end.
    insertText: function (textarea, text) {
        textarea = jQuery(textarea);
        textarea.focus();
        textarea.val(textarea.val() + text);
        textarea.focus();

        // Trigger the textarea's keyup to emulate typing.
        textarea.trigger('keyup');
    },

    // Add text to the reply area, with the options of wrapping it around a selection and selecting a part of it when it's inserted.
    wrapText: function (textarea, tagStart, tagEnd, selectArgument, defaultArgumentValue) {
        var newStart, newEnd;
        textarea = jQuery(textarea);

        // Save the scroll position of the textarea.
        var scrollTop = textarea.scrollTop();

        // Work out what text is currently selected.
        var selectionInfo = textarea.getSelection();
        if (textarea.val().substring(selectionInfo.start, selectionInfo.start + 1).match(/ /)) {
            selectionInfo.start++;
        }
        if (textarea.val().substring(selectionInfo.end - 1, selectionInfo.end).match(/ /)) {
            selectionInfo.end--;
        }

        var selection = textarea.val().substring(selectionInfo.start, selectionInfo.end);

        // Work out the text to insert over the selection.
        selection = selection ? selection : (defaultArgumentValue ? defaultArgumentValue : '');
        var text = tagStart + selection + (typeof tagEnd != "undefined" ? tagEnd : tagStart);

        // Replace the textarea's value.
        textarea.val(textarea.val().substr(0, selectionInfo.start) + text + textarea.val().substr(selectionInfo.end));

        // Scroll back down and refocus on the textarea.
        //textarea.scrollTo(scrollTop);
        textarea.focus();

        // If a selectArgument was passed, work out where it is and select it. Otherwise, select the text that was selected
        // before this function was called.
        if (selectArgument) {
            newStart = selectionInfo.start + tagStart.indexOf(selectArgument);
            newEnd = newStart + selectArgument.length;
        } else {
            newStart = selectionInfo.start + tagStart.length;
            newEnd = newStart + selection.length;
        }

        // Trigger the textarea's keyup to emulate typing.
        textarea.trigger("keyup");
    },

    // Toggle preview on an editing area.
    togglePreview: function (id, preview) {
        var $block = jQuery('#' + id)
            $content = $block.find('textarea').first(),
            $preview = jQuery('#' + id + '-preview').first(),
            $buttons = $block.find('.mt_formattingButtons').first();

        if (!$content[0]) $content = $block.find('textarea').first();
        if (!$preview[0]) $preview = jQuery('#mt_' + id + '-preview').first();
        if (!$buttons[0]) $buttons = $block.find('.mt_formattingButtons').first();

        // If the preview box is checked...
        if (preview) {
            // Hide the formatting buttons.
            $buttons.hide();
            $preview.html('');

            // Get the formatted post and show it.
            jQuery.MTAjax({
                type: 'post',
                headers: {
                    Action: 'preview'
                },
                data: {
                    content: $content.val(),
                    name: jQuery('#mt_replay .mt_saveName').val(),
                    email: jQuery('#mt_replay .mt_saveEmail').val(),
                    conversation: MT.conversation,
                    ctx: MT.ctx
                },
                success: function (data) {
                    if (data.success != true) {
                        jQuery('#mt_replay .mt_postReply, #mt_replay .mt_saveDraft').enable();
                        jQuery('#reply-previewCheckbox').attr('checked', false);
                        $buttons.show();
                        jQuery.each(data.data, function (i, item) {
                            MTMessages.showMessage(item.msg, 'mt_msg-' + item.id);
                        });
                        return;
                    }

                    // Keep the minimum height.
                    $preview.css('min-height', jQuery('#' + id + '-textarea').innerHeight());

                    // Hide the textarea, and show the preview.
                    $content.hide();
                    jQuery('#' + id + '-bg').hide();
                    $preview.show();
                    $preview.html(data.message.content);
                    jQuery('pre').each(function (i, e) {
                        hljs.highlightBlock(e)
                    });
                }
            });
        }
        // The preview box isn't checked...
        else {
            // Show the formatting buttons and the textarea; hide the preview area.
            $buttons.show();
            $content.show();
            jQuery('#' + id + '-bg').show();
            $preview.hide();
        }
    }
};

jQuery(function () {
    jQuery('body').prepend('<div id="mt_messages"></div>');
    jQuery('#mt_replay.mt_post').addClass('mt_replayPlaceholder');
    MTConversation.init();
});

jQuery(document).ready(function () {
    jQuery('a.mt_time').timeago();

    var url = MT.assetsPath + 'connectors/connector.php';
    MTScrubber.items.on('click', '.mt_like-btn', function (e) {
        var btn = jQuery(this);
        var id = btn.parents('.mt_post').data('id');
        jQuery.ajax({
            url: url,
            headers: { Action: 'vote' },
            type: 'post',
            data: {
                id: id,
                conversation: MT.conversation,
                ctx: MT.ctx
            },
            success: function (data) {
                if (data.success == true) {
                    btn.text(data.object.btn).next().text(data.object.html);
                    MTMessages.showMessage(data.message, 'autoDismiss');
                }
            }
        });
        e.preventDefault();
    })
});
