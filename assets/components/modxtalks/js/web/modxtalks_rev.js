// modxTalks JavaScript
// Translate a string, using definitions found in MT.language. addJSLanguage() must be called on
// a controller to make a definition available in MT.language.
function T(string) {
    return typeof MT.language[string] == "undefined" ? string : MT.language[string];
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
    if (!jQuery("#mt_loadingOverlay-" + id).length) jQuery("<div/>", {
        id: "mt_loadingOverlay-" + id
    }).addClass("mt_loadingOverlay").appendTo(jQuery("body")).hide();
    var elm = jQuery("#" + coverElementWithId);

    // Style and position it.
    jQuery("#mt_loadingOverlay-" + id).css({
        opacity: 0.6,
        position: "absolute",
        top: elm.offset().top,
        left: elm.offset().left,
        width: elm.outerWidth(),
        height: elm.outerHeight()
    }).show();
}

// Hide a loading overlay.
function hideLoadingOverlay(id, fade) {
    loadingOverlays[id]--;
    if (loadingOverlays[id] <= 0) jQuery("#mt_loadingOverlay-" + id)[fade ? "fadeOut" : "remove"]();
}

//***** AJAX Functionality

// modxTalks custom AJAX plugin. This automatically handles messages, disconnection, and modal message sheets.
jQuery.MTAjax = function (options) {

    // If this request has an ID, abort any other requests with the same ID.
    if (options.id) jQuery.MTAjax.abort(options.id);

    // Prepend the full path to this forum to the URL.
    options.url = MT.assetsPath + 'connectors/connector.php';

    // Set up the error handler. If we get an error, inform the user of the "disconnection".
    var handlerError = function (XMLHttpRequestObject, textStatus, errorThrown) {
        if (!errorThrown || errorThrown == "abort") return;

        jQuery.MTAjax.disconnected = true;

        // Save this request's information so that it can be tried again if the user clicks "try again".
        if (!jQuery.MTAjax.disconnectedRequest) jQuery.MTAjax.disconnectedRequest = options;

        // Show a disconnection message.
        MTMessages.showMessage(T("message.ajaxDisconnected"), {
            className: "mt_warning mt_dismissable",
            id: "ajaxDisconnected"
        });
    };

    // Set up the success handler!
    var handlerSuccess = function (result, textStatus, XMLHttpRequestObject) {

        // If the ajax system is disconnected but this request was successful, reconnect.
        if (jQuery.MTAjax.disconnected) {
            jQuery.MTAjax.disconnected = false;
            MTMessages.hideMessage("ajaxDisconnected");
        }

        // If the result is empty, don't continue. ???
        // if (!result) return;

        // If there's a URL to redirect to, redirect there.
        if (typeof result.redirect != "undefined") {
            jQuery(window).unbind("beforeunload.ajax");
            window.location = result.redirect;
            return;
        }

        // Did we get any messages? Show them via the messages system.
        if (result.messages) {
            for (var i in result.messages) {
                if (typeof result.messages[i] != "object") continue;
                MTMessages.showMessage(result.messages[i]["message"], result.messages[i]);
            }
        }

        // Set the request's messages variable to false if there were no messages - makes it easy for handlers to test whether there were messages or not.
        if (typeof result.messages == "undefined" || result.messages.length < 1) result.messages = false;

        // But hold on... if the server returned a modal message, then show that, and mark the request as NOT successful.
        if (result.modalMessage) {
            MTSheet.showSheet("messageSheet", result.view, function () {
                jQuery("#mt_messageSheet .mt_buttons a").click(function (e) {
                    MTSheet.hideSheet("messageSheet");
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
        MTMessages.hideMessage("ajaxDisconnected");
        jQuery.MTAjax(this.disconnectedRequest);
        this.disconnectedRequest = false;
    },

    requests: [],
    disconnected: false,
    disconnectedRequest: null,

    // Abort a request with the specified ID.
    abort: function (id) {
        if (jQuery.MTAjax.requests[id]) jQuery.MTAjax.requests[id].abort();
    }
});


// Set up global AJAX defaults and handlers.
jQuery(function () {

    // Append a "loading" div to the page.
    jQuery("<div id='mt_loading'>" + T("Loading...") + "</div>").appendTo("body").hide();

    // Set up global ajax event handlers to show/hide the loading box and configure an onbeforeunload event.
    jQuery(document).bind("ajaxStart", function () {
        jQuery("#mt_loading").show();
        jQuery(window).bind("beforeunload.ajax", function () {
            return T("ajaxRequestPending");
        });
    })
        .bind("ajaxStop", function () {
        jQuery("#mt_loading").fadeOut("fast");
        jQuery(window).unbind("beforeunload.ajax");
    });

    // Set the default AJAX request settings.
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

        // Gather all the messages and their information.
        var messages = [];
        jQuery("#mt_messages .mt_message").each(function () {
            messages.push([jQuery(this).html(), jQuery(this).attr("class")]);
        });

        // Clear the messages container, and redisplay the messages usings showMessage().
        jQuery("#mt_messages").html("");
        if (messages.length) {
            for (var i in messages) {
                if (typeof messages[i] != "object") continue;
                MTMessages.showMessage(messages[i][0], messages[i][1]);
            }
        }
    },

    // Show a message.
    showMessage: function (text, options) {

        // If the options is a string, use it as a class name.
        if (typeof options == "string") options = {
            className: options
        };
        if (!options) options = {};
        options.message = text;

        // If this message has an ID, hide any other message with the same ID.
        if (options.id) MTMessages.hideMessage(options.id, true);

        // Construct the message as a div, and prepend it to the messages container.
        var message = jQuery("<div class='mt_messageWrapper'><div class='mt_message " + options.className + "'>" + options.message + "</div></div>");
        jQuery("#mt_messages").prepend(message);
        // Hide it and fade it in.
        message.hide().fadeIn("fast");

        // Work out a unique ID for this message. If one wasn't provided, use a numeric index.
        var key = options.id || ++MTMessages.index;

        // Store the message information.
        MTMessages.messages[key] = [message, options];

        // If the message is dismissable, add an 'x' control to close it.
        if (!message.find(".mt_message").hasClass("undismissable")) {
            var close = jQuery("<a href='#' class='mt_control-delete mt_dismiss'>X</a>").click(function (e) {
                e.preventDefault();
                MTMessages.hideMessage(key);
            });
            message.find(".mt_message").prepend(close);
        }

        // If the message is to auto-dismiss, set a timeout.
        //if (message.find(".message").hasClass("autoDismiss")) {
        message.bind("mouseenter", function () {
            message.data("hold", true);
        }).bind("mouseleave", function () {
            message.data("hold", false);
        });
        MTMessages.messages[key][2] = setTimeout(function () {
            if (!message.data("hold")) MTMessages.hideMessage(key);
            else message.bind("mouseleave", function () {
                MTMessages.hideMessage(key);
            });
        }, 5 * 1000);
        //}

    },

    // Hide a message class.
    hideMessageClass: function (key, noAnimate) {
        if (!key) return;

        // Fade out the message, or just hide it if we don't want animation.
        jQuery('.' + key).fadeOut(noAnimate ? 0 : "fast", function () {
            jQuery(this).parent().remove();
        });

    },

    // Hide a message.
    hideMessage: function (key, noAnimate) {

        // If this message doesn't exist, we can't do anything.
        if (!MTMessages.messages[key]) return;

        // Fade out the message, or just hide it if we don't want animation.
        MTMessages.messages[key][0].fadeOut(noAnimate ? 0 : "fast", function () {
            jQuery(this).remove();
        });

        // Clear the message's hide timeout so we don't hide a future message with the same key.
        clearTimeout(MTMessages.messages[key][2]);

        // Remove the message from storage.
        delete MTMessages.messages[key];

    }

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
    }

    // When the window gains focus, if we're "holding", stop holding. Otherwise, run the callback.
    jQuery(window).focus(function (e) {
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
    title: "",
    channel: "",
    slug: "",
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
        if (MT.conversation) {
            // Get the details.
            this.id = MT.mtconversation.id;
            this.postCount = MT.mtconversation.countPosts;
            this.startFrom = MT.mtconversation.startFrom;
            this.slug = MT.conversation;

            // Set up the timeline scrubber.
            MTScrubber.header = jQuery("#mt_conversationBody .mt_mthead");
            MTScrubber.body = jQuery("#mt_conversationBody");
            MTScrubber.scrubber = jQuery("#mt_conversationBody .mt_scrubberContent");
            MTScrubber.items = jQuery("#mt_conversationPosts");
            MTScrubber.converReply = jQuery("#mt_cf_conversationReply");
            MTScrubber.count = this.postCount;
            MTScrubber.perPage = MT.postsPerPage;
            MTScrubber.moreText = MT.language.moreText;
            MTScrubber.startFrom = this.startFrom;
            MTScrubber.lastRead = MT.mtconversation.lastRead;
            MTConversation.slug = MT.mtconversation.slug;
            MTScrubber.converReply.hide();

            // Initialize the scrubber.
            MTScrubber.init();

            // If there's a post ID in the URL hash (eg. p1234), highlight that post, and scroll to it.
            var wlh = window.location.hash;
            var hash = window.location.hash.replace("#", "");
            var idxcomm = wlh.substr(9).length > 0 ? jQuery('*[data-idx="' + wlh.substr(9) + '"]') : false;
            if (wlh.substr(0, 9) == '#comment-' && idxcomm.length) {
                MTConversation.highlightPost(idxcomm);
                setTimeout(function () {
                    MTConversation.scrollTo(idxcomm.offset().top - 10);
                }, 100);
                jQuery.history.load(window.location.pathname.substr(1), true); // window.location.pathname
                //jQuery.history.load(MTConversation.slug.replace("$$$",wlh.substr(9)), true);
            } else {
                if (window.location.pathname.indexOf('-last-mt') > 0 && this.postCount > 0) {
                    lastIt = jQuery('*[data-idx="' + this.postCount + '"]');
                    jQuery('html, body').animate({
                        scrollTop: lastIt.offset().top - MT.scrubberOffsetTop
                    });
                    MTConversation.highlightPost(lastIt);
                    //MTScrubber.scrollToIndex(this.postCount);
                } else if (this.startFrom > 1) {
                    if (!MT.revers) {
                        MTScrubber.scrollToIndex(this.startFrom);
                    }
                    MTConversation.highlightPost(jQuery('*[data-idx="' + this.startFrom + '"]'));
                }
            }

            // Process the text selection and display button quote
            var $txt,
                node;
            jQuery(MTScrubber.items).on('mouseup', '.mt_postBody', function(e) {
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
                else { return; }
                if ($txt != '') {
                    jQuery('#mt_MTpopUpBox').css({'display':'block', 'left':e.pageX-60+'px', 'top':e.pageY+5+'px'});
                }
            });

            jQuery(document).bind("mousedown", function () {
                jQuery('#mt_MTpopUpBox').css({
                    'display': 'none'
                });
            });

            jQuery(document).bind("mousedown", function(){
                jQuery('#MTpopUpBox').css({'display':'none'});
            });

            jQuery('#mt_MTpopUpBox').bind("mousedown", function(){
                postq = jQuery(this).parents(".mt_post");
                var postId = postq.data("id");
                var postIdx = jQuery(this).parents(".mt_mtComment").data("idx");
                parNod = jQuery(node.parentNode).parents(".mt_post");
                var idNod = parNod.data("id") || null;
                var idxNod = parNod.parents(".mt_mtComment").data("idx") || null;
                var nameNode = parNod.find('.mt_info h3').text() || null;
                if(nameNode) nameNode = '"' + nameNode + '"';
                MTConversation.quote("mt_replay", $txt, nameNode, idNod, null, true, idxNod);
                jQuery("#mt_jumpToReply").click();
                MTConversation.scrollTo(MTScrubber.converReply.offset().top - 10);
                //MTConversation.scrollElem(MTScrubber.converReply.offset().top - 20);
            });

            // The conclusion of the comment link to copy
            jQuery(MTScrubber.items).on('click','.mt_info a',function(e) {
                jQuery('.mt_get_link_textarea').remove();
                var clEl = jQuery(this);
                clEl.parents('.mt_postHeader').after('<textarea class="mt_get_link_textarea e_mt_get_link_textarea" style="height: 26px; margin: 0px 10px; width: '
                    +(clEl.parents('.mt_postContent').width() - 40)+'px;">'
                    + location.protocol + '//' + location.host + clEl.attr('href')+'</textarea>');
                jQuery('.mt_get_link_textarea').select();
                e.preventDefault();
            });

            jQuery('body').on('click', function(e) {
                if (jQuery(e.target).closest('.mt_time').length == 0 && jQuery(e.target).closest('.mt_get_link_textarea').length == 0) {
                    jQuery('.mt_get_link_textarea').remove();
                }
            });

            // block IP && Email user
            jQuery(MTScrubber.items).on('click','.mt_user_info span',function() {
                thisSpan = jQuery(this);
                var clickSpan = thisSpan.hasClass('mt_user_ip') ? 'ip' : 'email';;
                var clickText = thisSpan.text();
                var clickId = thisSpan.parents('.mt_post').data('id');
                var retVal = confirm(T("message.confirm_" + clickSpan) + clickText);
                   if( retVal == true ){
                      jQuery.MTAjax({
                        headers: {
                            Action: 'ban_' + clickSpan
                        },
                        type: 'POST',
                        data: {
                            id: clickId
                        },
                        success: function (data) {
                            MTMessages.showMessage(data.message, 'autoDismiss');
                            if (data.success != false) {
                                thisSpan.addClass('block');
                            }
                        }
                      });
                      return true;
                   }else{
                      return false;
                   }
            });

            MTConversation.figures();

            jQuery('.mt_post .mt_likehov').hover(function () {
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
                var iditem = item.data("id");
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
                        itemBox.append('<div id="mt_box_user_info"><span class="mt_loaduser">' + T("Loading...") + '</span></div>');
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

            jQuery(document).click(function (e) {
                MTConversation.hideReply();
            });

            // If there's a reply box, initilize it.
            if (jQuery("#mt_replay").length) MTConversation.initReply();

            // Start the automatic reload timeout.
            this.updateInterval = new MTIntervalCallback(this.update, MT.conversation.updateInterval);

            // When the "add a reply" button in the sidebar is clicked, we trigger a click on the "now" item in
            // the scrubber, and also on the reply textarea.
            jQuery('#mt_jumpToReply').click(function (e) {
                MTConversation.replyShowing = false;
                jQuery('.mt_scrubber-now a').click();
                setTimeout(function () {
                    jQuery('#mt_replay textarea').click();
                }, 1);
                MTScrubber.converReply.show();
                e.preventDefault();
            });

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

            jQuery('a[rel="comment"]').click(function (e) {
                e.preventDefault();
                // Comment Idx
                var idcom = jQuery(this).attr('data-id'),
                    comment = jQuery('*[data-idx="' + idcom + '"]'); //'#comment-' + idcom;
                // If comment exists
                if (comment) {
                    // Scroll to first new comment
                    //MTConversation.scrollElem(comment);
                    MTConversation.scrollTo(comment.offset().top - 10);
                    // Highlight comment
                    MTConversation.highlightPost(comment);
                    e.preventDefault();
                };
            });

            jQuery('.mt_button.mt_bc').click(function (e) {
                // First comment Id
                var idcom = MTConversation.itemsComment.slice(-1)[0],
                    comment = jQuery('#comment-' + idcom);
                if (!idcom) { return false; }
                // Remove first comment from comments array
                MTConversation.itemsComment.pop();
                // Count of new comments without first
                var textComment = MTConversation.itemsComment.length;
                // Increment total comments count
                jQuery('.mt_big_count').text(textComment);
                if (textComment == 0) {
                    jQuery('.mt_button.mt_bc').css('cursor','default');
                    jQuery('.mt_big_count').hide('slow');
                }
                // Scroll to first new comment
                MTScrubber.scrollTo(comment.offset().top - 10);
                // Highlight comment
                MTConversation.highlightPost(comment);
                e.preventDefault();
            });

            // Initialize the posts.
            this.initPosts();


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
            }
        }
    },
    //***** REPLY AREA

    replyShowing: false,

    // Initialize the reply section: disable/enable buttons, add click events, etc.
    initReply: function () {

        var textarea = jQuery("#mt_replay textarea");
        MTConversation.editingReply = false;

        //if (MT.mentions) new MTAutoCompletePopup(jQuery("#reply textarea"), "@");

        // Auto resize our reply textareas
        textarea.TextAreaExpander(200, 700);
        // Disable the "post reply" button if there's not a draft. Disable the save draft button regardless.
        if (!textarea.val()) jQuery("#mt_replay .mt_postReply, #mt_replay .mt_discardDraft").disable();
        jQuery("#mt_replay .mt_saveDraft").disable();

        // Add event handlers on the textarea to enable/disable buttons.
        textarea.keyup(function (e) {
            if (e.ctrlKey) return;
            jQuery("#mt_replay .mt_postReply, #mt_replay .mt_saveDraft")[jQuery(this).val() ? "enable" : "disable"]();
            MTConversation.editingReply = jQuery(this).val() ? true : false;
        });

        // Add click events to the buttons.
        jQuery("#mt_replay .mt_postReply").click(function (e) {
            if (MT.conversation) MTConversation.addReply();
            //else MTConversation.startConversation();
            e.preventDefault();
        });

        jQuery("#mt_replay").click(function (e) {
            if (!MTConversation.replyShowing) {

                jQuery(this).trigger("change");

                // Save the scroll position and then focus on the textarea.
                var scrollTop = jQuery(document).scrollTop();
                jQuery("#mt_replay textarea").focus();
                jQuery.scrollTo(scrollTop);

                // Scroll to the bottom of the reply area.
                //jQuery.scrollTo("#reply", "slow");
                if (!MT.revers) {
                    MTConversation.scrollTo(jQuery('#mt_replay').offset().top - 10);
                }
            }
            e.stopPropagation();
        });

        jQuery("#mt_replay").change(function (e) {
            if (!MTConversation.replyShowing) {
                MTConversation.replyShowing = true;
                jQuery("#mt_replay").removeClass("mt_replayPlaceholder");

                // Put the cursor at the end of the textarea.
                var pos = textarea.val().length;
                textarea.selectRange(pos, pos);
            }
        });

        jQuery(document).click(function (e) {
            MTConversation.hideReply();
        });

    },
    // Condense the reply box back into a placeholder.
    hideReply: function () {
        if (!MTConversation.replyShowing || jQuery("#mt_replay textarea").val()) return;
        // Save the scroll top and height.
        var scrollTop = jQuery(document).scrollTop();
        var oldHeight = jQuery("#mt_replay .mt_postContent").height();
        MTConversation.replyShowing = false;
        MTScrubber.converReply.hide('slow');
        jQuery("#mt_replay").addClass("mt_replayPlaceholder");
        var newHeight = jQuery("#mt_replay .mt_postContent").height();
        jQuery("#mt_replay .mt_postContent").height(oldHeight).animate({
            height: newHeight
        }, "fast", function () {
            jQuery(this).height("");
        });
    },
    // Add a reply.
    addReply: function () {
        var content = jQuery("#mt_replay textarea").val();
        var saveemail = jQuery("#mt_replay .mt_saveEmail").val();
        var savename = jQuery("#mt_replay .mt_saveName").val();

        // Disable the reply/draft buttons.
        jQuery("#mt_replay .mt_postReply, #mt_replay .mt_saveDraft").disable();

        // Make the ajax request.
        jQuery.MTAjax({
            type: "post",
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
                            jQuery("#mt_replay textarea").val("");
                            MTConversation.togglePreview("mt_replay", false);
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
                MTMessages.hideMessage("mt_waitToReply");
                MTMessages.hideMessage("mt_emptyPost");

                jQuery("#mt_conversationHeader .mt_labels .mt_label-draft").remove();
                jQuery("#mt_replay textarea").val("");
                MTConversation.togglePreview("mt_replay", false);
                MTConversation.hideReply();

                MTConversation.postCount++;

                // Create a dud "more" block and then add the new post to it.
                var moreItem = jQuery("<dib></div>").appendTo("#mt_conversationPosts");
                MTScrubber.count = MTConversation.postCount;
                // data.object.link = MT.link + '#comment-' + data.object.id;
                idComm = data.object.id;
                newComment = new EJS({
                    url: MT.assetsPath + MT.commentTpl
                }).render(data.object);
                //MTScrubber.addItems(MTConversation.postCount, newComment, moreItem, true);
                MTScrubber.items.prepend(newComment);
                MTConversation.redisplayAvatars();
                MTConversation.highlightPost('#mt_comment-' + idComm);
                jQuery('.mt_total_mt').text(MTConversation.postCount);
                if(jQuery('.mt_count_comments').length) jQuery('.mt_count_comments').text(MTConversation.postCount);
                jQuery('pre').each(function (i, e) {
                    hljs.highlightBlock(e)
                });

                // Reset the post-checking timeout.
                MTConversation.updateInterval.reset(MT.conversationUpdateIntervalStart);
                jQuery('#reply-previewCheckbox').attr('checked', false);

            },
            beforeSend: function () {
                createLoadingOverlay("reply", "mt_replay");
            },
            complete: function () {
                hideLoadingOverlay("reply", false);
                jQuery('a.mt_time').timeago();
            }
        });
    },

    //***** POST FORMATTING

    // Add a quote to a textarea.
    quote: function (id, quote, name, postId, insert, hrzn, idx) {
        var argument = postId || name ? (idx ? idx + " user=" : " ") + (name ? name : "Name") : " ";
        var startTag = "[quote" + (argument && argument != " " ? " id=" + argument : "") + "]" + (quote ? quote + " " : " ");
        var endTag = "[/quote]";

        // If we're inserting the quote, add it to the end of the textarea.
        if (insert) MTConversation.insertText(jQuery("#" + id + " textarea"), startTag + endTag + "\n");

        // Otherwise, wrap currently selected text with the quote.
        else MTConversation.wrapText(jQuery("#" + id + " textarea"), startTag, endTag);
    },


    // Add text to the reply area at the very end, and move the cursor to the very end.
    insertText: function (textarea, text) {
        textarea = jQuery(textarea);
        textarea.focus();
        textarea.val(textarea.val() + text);
        textarea.focus();

        // Trigger the textarea's keyup to emulate typing.
        textarea.trigger("keyup");
    },

    // Add text to the reply area, with the options of wrapping it around a selection and selecting a part of it when it's inserted.
    wrapText: function (textarea, tagStart, tagEnd, selectArgument, defaultArgumentValue) {

        textarea = jQuery(textarea);

        // Save the scroll position of the textarea.
        var scrollTop = textarea.scrollTop();

        // Work out what text is currently selected.
        var selectionInfo = textarea.getSelection();
        if (textarea.val().substring(selectionInfo.start, selectionInfo.start + 1).match(/ /)) selectionInfo.start++;
        if (textarea.val().substring(selectionInfo.end - 1, selectionInfo.end).match(/ /)) selectionInfo.end--;
        var selection = textarea.val().substring(selectionInfo.start, selectionInfo.end);

        // Work out the text to insert over the selection.
        selection = selection ? selection : (defaultArgumentValue ? defaultArgumentValue : "");
        var text = tagStart + selection + (typeof tagEnd != "undefined" ? tagEnd : tagStart);

        // Replace the textarea's value.
        textarea.val(textarea.val().substr(0, selectionInfo.start) + text + textarea.val().substr(selectionInfo.end));

        // Scroll back down and refocus on the textarea.
        //textarea.scrollTo(scrollTop);
        textarea.focus();

        // If a selectArgument was passed, work out where it is and select it. Otherwise, select the text that was selected
        // before this function was called.
        if (selectArgument) {
            var newStart = selectionInfo.start + tagStart.indexOf(selectArgument);
            var newEnd = newStart + selectArgument.length;
        } else {
            var newStart = selectionInfo.start + tagStart.length;
            var newEnd = newStart + selection.length;
        }
        //textarea.selectRange(newStart, newEnd);

        // Trigger the textarea's keyup to emulate typing.
        textarea.trigger("keyup");
    },

    // Toggle preview on an editing area.
    togglePreview: function (id, preview) {

        // If the preview box is checked...
        if (preview) {

            // Hide the formatting buttons.
            jQuery("#" + id + " .mt_formattingButtons").hide();
            jQuery("#" + id + "-preview").html("");

            // Get the formatted post and show it.
            jQuery.MTAjax({
                type: "post",
                headers: {
                    Action: 'preview'
                },
                data: {
                    content: jQuery("#" + id + " textarea").val(),
                    name: jQuery("#mt_replay .mt_saveName").val(),
                    email: jQuery("#mt_replay .mt_saveEmail").val(),
                    conversation: MT.conversation,
                    ctx: MT.ctx
                },
                success: function (data) {

                    if (data.success != true) {
                        jQuery("#mt_replay .mt_postReply, #mt_replay .mt_saveDraft").enable();
                        jQuery('#reply-previewCheckbox').attr('checked', false);
                        jQuery('.mt_formattingButtons').show('slow');
                        jQuery.each(data.data, function (i, item) {
                            MTMessages.showMessage(item.msg, 'mt_msg-' + item.id);
                        });
                        return;
                    }

                    // Keep the minimum height.
                    jQuery("#" + id + "-preview").css("min-height", jQuery("#" + id + "-textarea").innerHeight());

                    // Hide the textarea, and show the preview.
                    jQuery("#" + id + " textarea").hide();
                    jQuery("#" + id + "-bg").hide();
                    jQuery("#" + id + "-preview").show()
                    jQuery("#" + id + "-preview").html(data.message.content);
                    jQuery('pre').each(function (i, e) {
                        hljs.highlightBlock(e)
                    });
                    // setTimeout(function () { jQuery('a.time').timeago() }, 5000);
                }
            });
        }

        // The preview box isn't checked...
        else {
            // Show the formatting buttons and the textarea; hide the preview area.
            jQuery("#" + id + " .mt_formattingButtons").show();
            jQuery("#" + id + " textarea").show();
            jQuery("#" + id + "-bg").show();
            jQuery("#" + id + "-preview").hide();
            // setTimeout(function () { jQuery('a.time').timeago() }, 5000);
        }

    },
    // Highlight a post.
    highlightPost: function (post) {
        jQuery(post).addClass("mt_highlight");
        setTimeout(function () {
            jQuery(post).removeClass("mt_highlight");
        }, 5000);
    },
    // Hide consecutive avatars from the same member.
    redisplayAvatars: function () {
        // Loop through the avatars in the posts area and compare each one's src with the one before it.
        // If they're the same, hide it.
        var prevId = null;
        jQuery("#mt_conversationPosts > .mt_mtComment").each(function () {
            var id = jQuery(this).find("div.mt_post").data("memberid");
            if (prevId == id) jQuery(this).find("div.mt_avatar").hide();
            else jQuery(this).find("div.mt_avatar").show();
            prevId = id;
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
        if (parseInt(jQuery('.mt_mtComment').first().data('idx')) != MTConversation.postCount) return;
        // Make the request for post data.
        jQuery.MTAjax({
            headers: {
                Action: 'load'
            },
            type: 'POST',
            data: {
                start: parseInt(MTConversation.postCount) + parseInt(MTScrubber.perPage)
            },
            success: function (data) {
                // If there are new posts, add them.
                if (MTConversation.postCount < data.total) {
                    jQuery('.mt_total_mt').text(data.total);
                    if(jQuery('.mt_count_comments').length) jQuery('.mt_count_comments').text(data.total);
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
                    jQuery('.mt_big_count').show().text(textComment);
                    jQuery('.mt_button.mt_bc').css('cursor','pointer');
                    MTMessages.showMessage(textComment + ' ' + MT.language.newComment, 'autoDismiss');
                    // Create a dud "more" block and then add the new post to it.
                    //var moreItem = jQuery("<div></div>").appendTo("#mt_conversationPosts");
                    MTScrubber.count = MTConversation.postCount;
                    //MTScrubber.addItems(addIt.startFrom, addIt.view, moreItem, true);
                    MTScrubber.items.prepend(addIt.view);

                    MTConversation.figures();
                    MTConversation.updateInterval.reset(interval);
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
        jQuery(MTScrubber.items).on("click", ".mt_control-delete", function (e) {
            var postId = jQuery(this).parents(".mt_post").data("id");
            MTConversation.deletePost(postId);
            e.preventDefault();
        });

        jQuery(MTScrubber.items).on("click", ".mt_control-restore", function (e) {
            var postId = jQuery(this).parents(".mt_post").data("id");
            MTConversation.restorePost(postId);
            e.preventDefault();
        });

        jQuery(MTScrubber.items).on("click", ".mt_control-edit", function (e) {
            var postId = jQuery(this).parents(".mt_post").data("id");
            MTConversation.editPost(postId);
            e.preventDefault();
        });

        jQuery(MTScrubber.items).on("click", ".mt_post:not(.mt_edit) .mt_control-quote", function (e) {
            postq = jQuery(this).parents(".mt_post");
            var postId = postq.data("id");
            var postIdx = jQuery(this).parents(".mt_mtComment").data("idx");
            //var member = '@' + jQuery.trim(postq.find('.info h3').text());
            //var content = jQuery.trim(postq.find('.postBody').text()).replace(/\s{2,}/g, ' ');
            MTConversation.quotePost(postId, undefined, undefined, undefined, postIdx);
            e.preventDefault();
        });

        // Add a click handler to any "post links" to scroll back up to the right post, if it's loaded.
        jQuery(MTScrubber.items).on("click", ".mt_postBody a[rel=mt_post]", function (e) {
            var id = jQuery(this).data("id");

            jQuery("#mt_conversationPosts .mt_post").each(function () {
                if (jQuery(this).data("id") == id) {
                    MTConversation.scrollTo(jQuery(this).offset().top - 10);
                    MTConversation.highlightPost(jQuery("#mt_p" + id));
                    e.preventDefault();
                    return false;
                }
            });
        });

    },
    // Delete a post.
    deletePost: function (postId) {
        //jQuery.hideToolTip();
        // Make the ajax request.
        jQuery.MTAjax({
            headers: {
                Action: 'delete'
            },
            type: "post",
            data: {
                id: postId
            },
            beforeSend: function () {
                createLoadingOverlay("comment-" + postId, "comment-" + postId);
            },
            complete: function () {
                hideLoadingOverlay("comment-" + postId, true);
            },
            success: function (data) {
                if (data.message === false) return;
                //jQuery("#comment-"+postId).replaceWith(data.view);
                data.object.link_restore = MTConversation.slug.replace("$$$", 'mt_restore-' + postId);
                data.object.timeMarker = ''
                jQuery("#comment-" + postId).replaceWith(new EJS({
                    url: MT.assetsPath + MT.deletedCommentTpl
                }).render(data.object));
                MTConversation.redisplayAvatars();
                jQuery('.mt_loadingOverlay').remove();
            }
        });
    },
    // Restore a post.
    restorePost: function (postId) {

        //jQuery.hideToolTip();

        // Make the ajax request.
        jQuery.MTAjax({
            headers: {
                Action: 'restore'
            },
            type: "post",
            data: {
                id: postId
            },
            beforeSend: function () {
                createLoadingOverlay("mt_comment-" + postId, "mt_comment-" + postId);
            },
            complete: function () {
                hideLoadingOverlay("comment-" + postId, true);
            },
            success: function (data) {
                if (data.success === false) return;
                //jQuery("#comment-"+postId).replaceWith(data.view);
                data.object.link_restore = MTConversation.slug.replace("$$$", 'mt_delete-' + postId);
                data.object.timeMarker = '';
                jQuery("#mt_comment-" + postId).replaceWith(new EJS({
                    url: MT.assetsPath + MT.commentTpl
                }).render(data.object));
                MTConversation.redisplayAvatars();
                jQuery('pre').each(function (i, e) {
                    hljs.highlightBlock(e)
                });
                // setTimeout(function () { jQuery('a.time').timeago() }, 5000);
                jQuery('.mt_loadingOverlay').remove();
            }
        });
    },
    // Edit a post.
    editPost: function (postId) {

        //jQuery.hideToolTip();
        var post = jQuery("#comment-" + postId);

        // Make the ajax request.
        jQuery.MTAjax({
            headers: {
                Action: 'get'
            },
            type: "post",
            data: {
                id: postId
            },
            beforeSend: function () {
                createLoadingOverlay("comment-" + postId, "comment-" + postId);
            },
            complete: function () {
                hideLoadingOverlay("comment-" + postId, true);
            },
            success: function (data) {
                if (data.success === false) return;
                MTConversation.editingPosts++;
                var startHeight = jQuery(".mt_postContent", post).height();
                // Replace the post HTML with the new stuff we just got.
                post.replaceWith(jQuery(data.object.html).find(".mt_post"));
                var newPost = jQuery("#mt_comment-" + postId);
                var textarea = jQuery("textarea", newPost);

                // Save the old post HTML for later.
                newPost.data("mt_oldPost", post);

                // Set up the text area.
                var len = textarea.val().length;
                textarea.TextAreaExpander(200, 700).focus().selectRange(len, len);

                // Add click handlers to the cancel/submit buttons.
                jQuery(".mt_cancel", newPost).click(function (e) {
                    e.preventDefault();
                    MTConversation.cancelEditPost(postId);
                });
                jQuery(".mt_submit", newPost).click(function (e) {
                    e.preventDefault();
                    MTConversation.saveEditPost(postId, textarea.val());
                });

                // Animate the post's height.
                var newHeight = jQuery(".mt_postContent", newPost).height();
                jQuery(".mt_postContent", newPost).height(startHeight).animate({
                    height: newHeight
                }, "fast", function () {
                    jQuery(this).height("");
                });

                MTConversation.redisplayAvatars();

                // Scroll to the bottom of the edit area if necessary.
                var scrollTo = newPost.offset().top + newHeight - (window.innerHeight || docElemProp) + 10;
                if (jQuery(document).scrollTop() < scrollTo) jQuery.scrollTo(scrollTo, "slow");

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
                // setTimeout(function () { jQuery('a.time').timeago() }, 5000);
            }
        });
    },

    // Save an edited post to the database.
    saveEditPost: function (postId, content) {

        // Disable the buttons.
        var post = jQuery("#mt_comment-" + postId);
        jQuery(".mt_button", post).disable();

        // Make the ajax request.
        jQuery.MTAjax({
            headers: {
                Action: 'edit'
            },
            type: "post",
            data: {
                content: content,
                id: postId
            },
            beforeSend: function () {
                createLoadingOverlay("comment-" + postId, "mt_comment-" + postId);
            },
            complete: function () {
                hideLoadingOverlay("comment-" + postId, true);
                jQuery(".button", post).enable();
            },
            success: function (data) {
                if (data.success === false) return;

                var startHeight = jQuery(".mt_postContent", post).height();

                // Replace the post HTML with the new post we just got.
                post.replaceWith(new EJS({
                    url: MT.assetsPath + MT.commentTpl
                }).render(data.object));
                var newPost = jQuery("#comment-" + postId);

                // Animate the post's height.
                var newHeight = jQuery(".mt_postContent", newPost).height();
                jQuery(".mt_postContent", newPost).height(startHeight).animate({
                    height: newHeight
                }, "fast", function () {
                    jQuery(this).height("");
                });

                MTConversation.editingPosts--;
                MTConversation.redisplayAvatars();
                jQuery('pre').each(function (i, e) {
                    hljs.highlightBlock(e)
                });
                // setTimeout(function () { jQuery('a.time').timeago() }, 5000);
            }
        });
    },

    // Cancel editing a post.
    cancelEditPost: function (postId) {
        MTConversation.editingPosts--;
        var post = jQuery("#mt_comment-" + postId);

        var scrollTop = jQuery(document).scrollTop();

        // Change the post control and body HTML back to what it was before.
        var startHeight = jQuery(".mt_postContent", post).height();
        post.replaceWith(post.data("mt_oldPost"));
        var newPost = jQuery("#mt_comment-" + postId);

        // Animate the post's height.
        var newHeight = jQuery(".mt_postContent", newPost).height();
        jQuery(".postContent", newPost).height(startHeight).animate({
            height: newHeight
        }, "fast", function () {
            jQuery(this).height("");
        });

        jQuery.scrollTo(scrollTop);
    },

    // scroll to element
    scrollElem: function (elm) {
        jQuery.scrollTo(elm.offset().top - jQuery('.mt_scrubberContent').height() - 10);
        /*jQuery('html, body').animate({
                    scrollTop: jQuery(elm).offset().top
                });*/
    },

    // Quote a post.
    quotePost: function (postId, member, content, multi, idx) {
        var selection = "" + jQuery.getSelection();
        jQuery.MTAjax({
            headers: {
                Action: 'quote'
            },
            type: "post",
            data: {
                id: postId
            },
            success: function (data) {
                if (data.success === false && data.total == 0) {
                    MTMessages.showMessage(data.message, 'mt_msg-error');
                    return;
                }
                var top = jQuery(document).scrollTop();
                MTConversation.quote("mt_replay", selection ? selection : data.object.content, data.object.user, data.object.id, null, true, idx);
                // If we're "multi" quoting (i.e. shift is being held down), keep our scroll position static.
                // Otherwise, scroll down to the reply area.
                MTScrubber.converReply.show();
                jQuery("#mt_replay").change();
                MTScrubber.scrollTo(MTScrubber.converReply.offset().top - 10);
            },
            global: true
        });
    },

}

jQuery(document).ready(function () {
    jQuery("body").prepend('<div id="mt_messages"></div>'); //
    jQuery("#mt_replay.mt_post").addClass("mt_replayPlaceholder");
    MTConversation.init();
    MTMessages.init();
    jQuery('a.mt_time').timeago();

    var url = MT.assetsPath + 'connectors/connector.php';
    MTScrubber.items.on('click', '.mt_like-btn', function (e) {
        var btn = jQuery(this);
        var id = btn.parents('.mt_post').data('id');
        jQuery.ajax({
            url: url,
            headers: {
                Action: 'vote'
            },
            type: 'POST',
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
        })
        e.preventDefault();
    })

});

// Scrubber JavaScript
// A scrubber is a list of "sections" that allow you to quickly navigate through a large collection of items.
// By default, a scrubber is used in the conversation view (the timeline scrubber) and on the member list (as
// a letter scrubber.)
var MTScrubber = {

    // These variables refer to various elements of the page.
    header: null,
    body: null,
    scrubber: null,
    items: null,

    // Callback functions.
    loadItemsCallback: null,
    scrollToIndexCallback: null,

    // Information about the content of the scrubber.
    count: 0,
    startFrom: 0,
    perPage: 0,
    moreText: "Load more",

    // An array of positions within the scrubber that have been loaded.
    loadedItems: [],

    // Initialize the scrubber.
    init: function () {
        // Go through the currently displaying item range and add the positions to the loadedItems array.
        var count = Math.min(this.startFrom + this.perPage, this.count + 1);
        for (var i = this.startFrom; i < count; i++)
            this.loadedItems.push(i);

        // Make the header and the scrubber's position fixed when we scroll down the page.
        // Get the normal top position of the header and of the scrubber. If the scrollTop is greater than
        // this, we know we'll need to make it fixed.
        var headerTop = this.header.offset().top;
        var headerWidth = this.header.width();
        //var scrubberTop = this.scrubber.length && (this.scrubber.offset().top - this.header.outerHeight() - 10);
        var scrubberTop = this.scrubber.length && (MTScrubber.body.offset().top - 10);

        jQuery(window).scroll(function () {
            var y = jQuery(this).scrollTop();

            // If we're past the normal top position of the header, make it fixed.
            if (y + MT.scrubberOffsetTop >= MTScrubber.body.offset().top && !MT.disableFixedPositions) {
                MTScrubber.body.css({
                    paddingTop: MTScrubber.header.outerHeight()
                });
                MTScrubber.header.addClass("floating").css({
                    position: "fixed",
                    top: MT.scrubberOffsetTop,
                    width: headerWidth,
                    zIndex: 110
                });
                MTScrubber.items.addClass("scrubtop");
            }
            // Otherwise, put it back to normal.
            else {
                MTScrubber.body.css({
                    paddingTop: 0
                });
                MTScrubber.header.removeClass("floating").css({
                    position: "",
                    top: "",
                    width: ""
                });
                MTScrubber.items.removeClass("scrubtop");
                headerWidth = MTScrubber.header.width();
            }

            // If we're past the normal top position of the scrubber, make it fixed.
            if (y + MT.scrubberOffsetTop >= MTScrubber.body.offset().top && !MT.disableFixedPositions) {
                if (jQuery('#mt_conversationBody').hasClass('mt_scrubber-top')) {
                    topofs = MT.scrubberOffsetTop;
                } else {
                    topofs = MT.scrubberOffsetTop + 10; //MTScrubber.header.outerHeight() + 10;
                }
                MTScrubber.scrubber.addClass("floating").css({
                    position: "fixed",
                    top: topofs,
                    zIndex: 100
                });
                //MTScrubber.items.addClass("mt_scrubtop");
            }
            // Otherwise, put it back to normal.
            else {
                MTScrubber.scrubber.removeClass("mt_floating").css({
                    position: "",
                    top: ""
                });
                //MTScrubber.items.removeClass("mt_scrubtop");
            }

            // Now we need to work out where we are in the content and highlight the appropriate
            // index in the scrubber. Go through each of the items on the page...
            jQuery(".mt_mtComment", MTScrubber.items).each(function () {
                var item = jQuery(this);

                // If we've scrolled past this item, continue in the loop.
                if (y > item.offset().top + item.outerHeight() - MTScrubber.header.outerHeight()) return true;
                else {

                    // This must be the first item within our viewport. Get the index of it and highlight
                    // it that index in the scrubber, then break out of the loop.
                    jQuery(".mt_scrubber li").removeClass("selected");
                    var index = item.data("index");
                    jQuery(".mt_scrubber-" + index, MTScrubber.scrubber).addClass("selected").parents("li").addClass("selected");
                    return false;

                }
            });

            // Work out if the "next page" block is visible in the viewport. If it it, automatically load
            // new items, starting from the last item position that we have loaded already.
            var newer = jQuery(".mt_scrubberNext", MTScrubber.body);
            if (newer.length && y + (window.innerHeight || docElemProp) > newer.offset().top && !newer.hasClass("mt_loading") && !MT.disableFixedPositions) {
                newer.find("a").click();
                /*position  = parseInt(jQuery('.mt_mtComment').last().data('idx')) - 1;
                var moreItem = jQuery('.mt_scrubberNext');
                MTScrubber.loadItemsCallback(position, function (addIt) {
                // If we are loading items that are above where we are, save the scroll position relative
                // to the first post after the "more" block.

               MTScrubber.addItems(addIt.startFrom, addIt.view, moreItem);

                // Restore the scroll position.
                //if (backwards) jQuery.scrollTo(firstItem.offset().top - scrollOffset);

            });*/
            }
        })

        jQuery(MTScrubber.body).on("click", ".mt_scrubberMore a", function (e) {
            e.preventDefault();
            jQuery(this).parent().addClass("mt_loading");
            var moreItem = jQuery(this).parent();
            var backwards, // Whether or not to load items that are at the start or the end of this "more" block.
            position; // The position to load items from.
            // If this is the "previous page" block...
            if (moreItem.is(".mt_scrubberPrevious")) {
                backwards = true;
                position = parseInt(jQuery('.mt_mtComment').first().data('idx')) + MTScrubber.perPage;
                if (position >= MTScrubber.count) position = MTScrubber.count;
            }
            // If this is the "next page" block...
            else if (moreItem.is(".mt_scrubberNext")) {
                backwards = false;
                position  = parseInt(jQuery('.mt_mtComment').last().data('idx')) - 1;
            }
            // If this is a "load more" block...
            else {
                backwards = moreItem.offset().top - jQuery(document).scrollTop() < 250;
                position = backwards ? jQuery(this).parent().data("positionEnd") - MTScrubber.perPage + 1 : jQuery(this).parent().data("positionStart");
            }
            MTScrubber.loadItemsCallback(position, function (addIt) {
                // If we are loading items that are above where we are, save the scroll position relative
                // to the first post after the "more" block.
                if (backwards) {
                    var firstItem = moreItem.next();
                    var scrollOffset = firstItem.offset().top - jQuery(document).scrollTop();
                }

                MTScrubber.addItems(addIt.startFrom, addIt.view, moreItem);

                // Restore the scroll position.
                //if (backwards) jQuery.scrollTo(firstItem.offset().top - scrollOffset);

            });

        });

    },
    // Scroll to a specific position, applying an animation and taking the fixed header into account.
    scrollTo: function (position) {
        mtscrh = jQuery('.mt_scrubberContent').outerHeight();
        tops = jQuery('.mt_mthead').hasClass('floating') ? 0 : mtscrh;
        //console.log(mtscrh,tops);
        jQuery.scrollTo(position - MT.scrubberOffsetTop - mtscrh - tops, "slow");
    },

    // Scroll to the item on or before an index combination.
    scrollToIndex: function (index, ur) {

        var post = null,
            found = false,
            item;

        // Go through each of the items and find one on or before the supplied index to scroll to.
        jQuery(".mt_mtComment", MTScrubber.items).each(function () {
            item = jQuery(this);
            // If this item matches the index we want to scroll to, then we've found it!
            if (item.data("index") == index) {
                found = true;
                return false;
            }

            // If this item is after the index we want to scroll to, break out of the loop.
            if (item.data("index") > index) return false;
        });
        // Scroll to it.
        if (item) MTScrubber.scrollTo(jQuery(item).offset().top);

        if (typeof MTScrubber.scrollToIndexCallback == "function" && !ur) MTScrubber.scrollToIndexCallback(index);

        return found;
    },
    // Add a collection of items, returned by an AJAX request, to the page.
    addItems: function (startFrom, items, moreItem, animate) {
        startFrom = parseInt(startFrom);
        moreItem.removeClass("mt_loading");

        // Get all of the <li>s in the item HTML provided.
        var view = jQuery(items);
        view = view.filter(".mt_mtComment");

        // Now we're going to loop through the range of items (startFrom -> startFrom + itemsPerPage) and make
        // a nice array of item objects, making sure we only add items that we don't already have. This means that
        // if we already have items 1-10 and 15-25, and we load items 11-20, this array will only contain 11-14.
        var items = [],
            newStartFrom = startFrom;
        for (var i = 0; i < MTScrubber.perPage; i++) {
            if (startFrom - MTScrubber.perPage + i - 1 >= MTScrubber.count) break;
            if (MTScrubber.loadedItems.indexOf(startFrom + i) != -1) {
                if (items.length) break;
                newStartFrom = startFrom + i + 1;
                continue;
            }
            items.push(view[i]);
        }
        startFrom = newStartFrom;

        // Now that we have an array of items, convert it to a jQuery collection.
        items = jQuery(items);
        // Add the items to the page before/after/replacing the "more" block, depending on the type of "more" block.
        if (moreItem.is(".mt_scrubberPrevious")) moreItem.after(items);
        else if (moreItem.is(".mt_scrubberNext")) moreItem.before(items);
        else if (items.length) moreItem.replaceWith(items);

        // Create a "more" block item which we can use below.
        var scrubberMore = jQuery("<div class='mt_scrubberMore'><a href='#'>" + MTScrubber.moreText + "</a></div>");
        // If we don't have the item immediately before the first item we just loaded (ie. there's a gap),
        // we need to put a "more" block there.
        var lastitem = parseInt(jQuery('.mt_mtComment').last().data('idx'));
        if (lastitem > 1 && items.first().prev().is("div:not(.mt_scrubberMore)")) {
            //jQuery(".mt_scrubberPrevious").append(MTScrubber.items);
            //scrubberMore = scrubberMore.clone();
            //items.first().before(scrubberMore);
            // Work out the range of items that this "more" block covers. We know where it ends, so loop backwards
            // from there and find the start.
            for (var i = startFrom - 1; i > 0; i--) {
                if (MTScrubber.loadedItems.indexOf(i) != -1) break;
            }
        }


        //if (animate) items.hide().fadeIn("slow");

        // Update the loadedItems index with the new item positions we have loaded.
        for (var i = startFrom; i < startFrom + items.length; i++) {
            if (MTScrubber.loadedItems.indexOf(i) == -1) MTScrubber.loadedItems.push(i);
        }

        // If we have the very first item in the collection, remove the "older" block.
        var mtsp = jQuery(".mt_scrubberPrevious");
        console.log(parseInt(jQuery('.mt_mtComment').first().data('idx')), parseInt(MTScrubber.count));
        if (parseInt(jQuery('.mt_mtComment').first().data('idx')) == parseInt(MTScrubber.count)) mtsp.remove();

        setTimeout(function () {
        console.log(mtsp.prev().data('idx'), mtsp.next().data('idx') + 1);
        if(parseInt(mtsp.prev().data('idx')) == parseInt(mtsp.next().data('idx')) + 1) mtsp.remove();
        }, 5000);

        // If we have the very last item in the collection, remove the "newer" block.
        if (lastitem <= 1) jQuery(".mt_scrubberNext").remove();

        jQuery('pre').each(function (i, e) {
            hljs.highlightBlock(e)
        });
        setTimeout(function () {
            jQuery('a.mt_time').timeago()
        }, 5000);

        MTConversation.figures();
    }
}
