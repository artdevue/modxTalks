/**
 * jQuery Cookie plugin
 *
 * Copyright (c) 2010 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */
jQuery.cookie = function (key, value, options) {

    // key and value given, set cookie...
    if (arguments.length > 1 && (value === null || typeof value !== "object")) {
        options = jQuery.extend({}, options);

        if (value === null) {
            options.expires = -1;
        }

        if (typeof options.expires === 'number') {
            var days = options.expires, t = options.expires = new Date();
            t.setDate(t.getDate() + days);
        }

        return (document.cookie = [
            encodeURIComponent(key), '=',
            options.raw ? String(value) : encodeURIComponent(String(value)),
            options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
            options.path ? '; path=' + options.path : '',
            options.domain ? '; domain=' + options.domain : '',
            options.secure ? '; secure' : ''
        ].join(''));
    }

    // key and possibly options given, get cookie...
    options = value || {};
    var result, decode = options.raw ? function (s) { return s; } : decodeURIComponent;
    return (result = new RegExp('(?:^|; )' + encodeURIComponent(key) + '=([^;]*)').exec(document.cookie)) ? decode(result[1]) : null;
};

// This class allows you to set up a callback to be run at a certain interval (in seconds). If the window
// loses focus, the callback will run when the window regains focus or when the timer runs out (whichever
// comes last.)
function MTIntervalCallback(callback, interval)
{
	var ic = this;
	ic.hold = false;
	ic.timeout = null;
	ic.interval = interval;
	ic.callback = callback;

	// Set a timeout to call the callback, or if we're "holding", stop holding so that the callback will be
	// run when the window regains focus.
	ic.setTimeout = function() {
		clearTimeout(ic.timeout);
		if (ic.interval <= 0) return;
		ic.timeout = setTimeout(function() {
			if (!ic.hold) ic.runCallback();
			else ic.hold = false;
		}, ic.interval * 1000);
	};

	// Run the callback, resetting the timeout and the hold flag.
	ic.runCallback = function() {
		ic.callback();
		ic.setTimeout();
		ic.hold = false;
	};

	// Reset the interval (start the timer from the beginning.)
	ic.reset = function(interval) {
		if (interval > 0) ic.interval = interval;
		ic.setTimeout();
	}

	// When the window gains focus, if we're "holding", stop holding. Otherwise, run the callback.
	$(window).focus(function(e) {
		if (e.target != window) return;
		if (ic.hold) ic.hold = false;
		else ic.runCallback();
	})

	// When the window loses focus, start "holding".
	.blur(function(e) {
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
	editingPosts: 0, // Number of posts being edited.
	itemsComment: [], // Array of unread comments

	// Initialize:
	init: function() {
		// If we're viewing an existing conversation...
		if (MT.conversation) {
			// Get the details.
			this.id = MT.mtconversation.id;
			this.postCount = MT.mtconversation.countPosts;
			this.startFrom = MT.mtconversation.startFrom;
			this.slug = MT.conversation;

			// Set up the timeline scrubber.
			MTScrubber.header = $("#conversationBody .mthead");
			MTScrubber.body = $("#conversationBody");
			MTScrubber.scrubber = $("#conversationBody .scrubberContent");
			MTScrubber.items = $("#conversationPosts");
			MTScrubber.converReply = $("#mt_cf_conversationReply");
			MTScrubber.count = this.postCount;
			MTScrubber.perPage = MT.postsPerPage;
			MTScrubber.moreText = MT.language.moreText;
			MTScrubber.startFrom = this.startFrom;
			MTScrubber.lastRead = MT.mtconversation.lastRead;
			MTConversation.slug = MT.mtconversation.slug;
			
			// If there's a post ID in the URL hash (eg. p1234), highlight that post, and scroll to it.
			var wlh = window.location.hash;
			var hash = window.location.hash.replace("#", "");
			var idxcomm = wlh.substr(9).length > 0 ? $('*[data-idx="'+ wlh.substr(9) +'"]') : false;
			if(wlh.substr(0, 9) == '#comment-' && idxcomm.length) {
				MTConversation.highlightPost(idxcomm);
				setTimeout(function(){
					MTConversation.scrollTo(idxcomm.offset().top - 10);
				}, 100);
				$.history.load(window.location.pathname.substr(1), true); // window.location.pathname
				//$.history.load(MTConversation.slug.replace("$$$",wlh.substr(9)), true);
			} else {
				if(window.location.pathname.indexOf('-last-mt') > 0){
					lastIt = $('*[data-idx="'+ this.postCount +'"]');
					$('html, body').animate({
						scrollTop: lastIt.offset().top - MT.scrubberOffsetTop
					});
					MTConversation.highlightPost(lastIt);
					//MTScrubber.scrollToIndex(this.postCount);
				} else if (this.startFrom > 1) {
					MTScrubber.scrollToIndex(this.startFrom);
					MTConversation.highlightPost($('*[data-idx="'+ this.startFrom +'"]'));
				}
			}	

			// Set a callback that will load new post data.
			MTScrubber.loadItemsCallback = function(position,success, index) {
				//lim = typeof lim !== 'undefined' ? lim : MTScrubber.perPage;
				if (position == Infinity) {
					position = (MTScrubber.count - MTScrubber.perPage) < MTScrubber.perPage ? 1 : MTScrubber.count - MTScrubber.perPage + 1;//"999999"; // Kind of hackish? Meh...
					//index = null;
				}
				// If this "position" is an index in the timeline (eg. 201004), split it into year/month for the request.
				if (index && position != 0) {
					position = (""+position).substr(0, 4)+"-"+(""+position).substr(4, 2);
				}
				$.MTAjax({
					headers: {Action:'load'},
					type: "post",
					data: {start:position},
					success: function(data) {
						var addIt = new Object();
						if (data.success != false){
							$.each(data.results, function(i,item) {
								if(i == 0)
									addIt.startFrom = item.idx;
								if (item.delete_date) {
									addIt.view += new EJS({url: MT.assetsPath + MT.deletedCommentTpl}).render(item);
								} else {
									addIt.view += new EJS({url: MT.assetsPath + MT.commentTpl}).render(item);
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

			// Set a callback that will run whenever we scroll to a specific index. We need it to change the URL.
			MTScrubber.scrollToIndexCallback = function(index) {
				var position;
				if (index == Infinity) position = "last";
				else position = (""+index).substr(0, 4)+"-"+(""+index).substr(4, 2);
				if((position.charAt ( position.length - 1 )) == '-') position = position.slice(0, -1);
				//$.history.load(MTConversation.slug+"/"+position, true);
				$.history.load(MTConversation.slug.replace("$$$",position), true);
			}

			// Initialize the scrubber.
			MTScrubber.init();

			// When the "add a reply" button in the sidebar is clicked, we trigger a click on the "now" item in
			// the scrubber, and also on the reply textarea.
			$("#jumpToReply").click(function(e) {
				$(".scrubber-now a").click();
				setTimeout(function() {
					$("#reply textarea").click();
				}, 1);
				e.preventDefault();
			});

			// Start the automatic reload timeout.
			this.updateInterval = new MTIntervalCallback(this.update, MT.conversation.updateInterval); //MT.mtconversation.updateInterval

			// $('a.time').timeago();

			function widthscr () {
				$('.scrubberContent').width(MTScrubber.body.width());
			}

			if(MTScrubber.body.width() < 570) MTScrubber.body.addClass("scrubber-top");			

			if(MTScrubber.body.hasClass('scrubber-top')){
				MTConversation.scrubberTopDef = 1;
				widthscr();
				$(window).resize(function () {
					widthscr();
				});

			}

			$(window).resize(function () {
					if(MTScrubber.body.width() < 570 && MTConversation.scrubberTopDef == 0) {
						MTScrubber.body.addClass("scrubber-top");
					}else if (MTConversation.scrubberTopDef == 0) {
						MTScrubber.body.removeClass("scrubber-top");
					}
				});

			/*$('#comments_container').on('click','a[rel="comment"]',function(){
				var commentId = $(this).data('id');
				var comment = $('#comment-'+commentId);
				if (comment.lengh == 0) {};
			});*/

			 $('a[rel="comment"]').click(function(e) {
				var idcom = $(this).attr('data-id');
				var comment = $('*[data-idx="'+ idcom +'"]'); //'#comment-' + idcom;
				if (comment.lengh != 0) {
					MTConversation.scrollTo(comment.offset().top - 10);
					//MTScrubber.scrollTo($(comment).offset().top+100);
					//MTConversation.scrollElem(comment);
					MTConversation.highlightPost(comment);
					e.preventDefault();
				};
			});

			 $('.tt-mt').click(function(e) {
				var idcom = MTConversation.itemsComment[0];
				var comment = '#comment-' + idcom;
				MTConversation.itemsComment.shift();
				textComment = MTConversation.itemsComment.length;
				$('.big_count span').text(textComment);
				if(textComment == 0) $('.scrubber_total').addClass("noncom");
				MTConversation.scrollTo($(comment).offset().top - 10);
				MTConversation.highlightPost($("#comment-" + idcom));
				e.preventDefault();
			});

			 // Initialize the posts.
			this.initPosts();

		}
		// If we're starting a new conversation...
		else {}


		// If there's a reply box, initilize it.
		if ($("#reply").length) MTConversation.initReply();

		$('input.saveNamen').focus(function() {
			MTMessages.hideMessageClass("msg-name");
		});
		$('input.saveEmail').focus(function() {
			MTMessages.hideMessageClass("msg-email");
		});
	},

	// Scroll to a specific position, applying an animation and taking the fixed conversation header into account
	scrollTo: function(position) {
		MTScrubber.scrollTo(position);
	},

	//***** POSTS

	// Get new posts at the end of the conversation by comparing our post count with the server's.
	update: function() {
		var interval = MT.conversationUpdateIntervalStart;
		MTConversation.updateInterval.reset(interval);
		// Don't do this if we're searching, or if we haven't loaded the end of the conversation.
		// if (MTConversation.searchString || MTScrubber.loadedItems.indexOf(MTConversation.postCount - 1) == -1) return;
		if (MTScrubber.loadedItems.indexOf(MTConversation.postCount - 1) == -1) return;
		// Make the request for post data.
		$.MTAjax({
			headers: {Action:'load'},
			type: "POST",
			data: {
				start: parseInt(MTConversation.postCount)+1
			},
			success: function(data) {

				// If there are new posts, add them.
				if (MTConversation.postCount < data.total) {
					$('.total_mt').text(data.total);
					MTConversation.postCount = data.total;
					var addIt = new Object();
					$.each(data.results, function(i,item) {
						MTConversation.itemsComment.push(item.id);
							if(i == 0) addIt.startFrom = item.idx;
							if (item.delete_date) {
								addIt.view += new EJS({url: MT.assetsPath + MT.deletedCommentTpl}).render(item);
							} else {
								addIt.view += new EJS({url: MT.assetsPath + MT.commentTpl}).render(item);
							}
						});
					textComment = MTConversation.itemsComment.length;
					$('.big_count span').text(textComment);
					MTMessages.showMessage(textComment + ' '+ MT.language.newComment,'autoDismiss');
					if(textComment > 0) $('.scrubber_total').removeClass("noncom");
					// Create a dud "more" block and then add the new post to it.
					var moreItem = $("<div></div>").appendTo("#conversationPosts");
					MTScrubber.count = MTConversation.postCount;
					MTScrubber.addItems(addIt.startFrom, addIt.view, moreItem, true);
					// setTimeout(function () { $('a.time').timeago() }, 5000);
					//var interval = MT.conversationUpdateIntervalStart;
				}
				// Otherwise, multiply the update interval by our config setting.
				else var interval = Math.min(MT.conversationUpdateIntervalLimit, MTConversation.updateInterval.interval * MT.conversationUpdateIntervalMultiplier);

				MTConversation.updateInterval.reset(interval);

			},
			global: false
		});

	},

	// Initialize the posts.
	initPosts: function() {

		$("#conversationPosts .control-delete").live("click", function(e) {
			var postId = $(this).parents(".post").data("id");
			MTConversation.deletePost(postId);
			e.preventDefault();
		});

		$("#conversationPosts .control-restore").live("click", function(e) {
			var postId = $(this).parents(".post").data("id");
			MTConversation.restorePost(postId);
			e.preventDefault();
		});

		$("#conversationPosts .control-edit").live("click", function(e) {
			var postId = $(this).parents(".post").data("id");
			MTConversation.editPost(postId);
			e.preventDefault();
		});

		$(".mtComment .post:not(.edit) .control-quote").live("click", function(e) {
			postq = $(this).parents(".post");
			var postId = postq.data("id");
			var postIdx = $(this).parents(".mtComment").data("idx");
			//var member = '@' + $.trim(postq.find('.info h3').text());
			//var content = $.trim(postq.find('.postBody').text()).replace(/\s{2,}/g, ' ');
			MTConversation.quotePost(postId, undefined, undefined, undefined, postIdx);
			e.preventDefault();
		});

		// Add a click handler to any "post links" to scroll back up to the right post, if it's loaded.
		$("#conversationPosts .postBody a[rel=post]").live("click", function(e) {
			var id = $(this).data("id");

			$("#conversationPosts .post").each(function() {
				if ($(this).data("id") == id) {
					MTConversation.scrollTo($(this).offset().top - 10);
					MTConversation.highlightPost($("#p"+id));
					e.preventDefault();
					return false;
				}
			});
		});

	},

	//***** REPLY AREA

	replyShowing: false,

	// Initialize the reply section: disable/enable buttons, add click events, etc.
	initReply: function() {

		var textarea = $("#reply textarea");
		MTConversation.editingReply = false;

		//if (MT.mentions) new MTAutoCompletePopup($("#reply textarea"), "@");

		// Auto resize our reply textareas
		textarea.TextAreaExpander(200, 700);
		// Disable the "post reply" button if there's not a draft. Disable the save draft button regardless.
		if (!textarea.val()) $("#reply .postReply, #reply .discardDraft").disable();
		$("#reply .saveDraft").disable();

		// Add event handlers on the textarea to enable/disable buttons.
		textarea.keyup(function(e) {
			if (e.ctrlKey) return;
			$("#reply .postReply, #reply .saveDraft")[$(this).val() ? "enable" : "disable"]();
			MTConversation.editingReply = $(this).val() ? true : false;
		});

		// Add click events to the buttons.
		$("#reply .postReply").click(function(e){
			if (MT.conversation) MTConversation.addReply();
			//else MTConversation.startConversation();
			e.preventDefault();
		});

		$("#reply").click(function(e) {
			if (!MTConversation.replyShowing) {

				$(this).trigger("change");

				// Save the scroll position and then focus on the textarea.
				var scrollTop = $(document).scrollTop();
				$("#reply textarea").focus();
				$.scrollTo(scrollTop);

				// Scroll to the bottom of the reply area.
				//$.scrollTo("#reply", "slow");
				MTConversation.scrollTo($('#reply').offset().top - 10);
			}
			e.stopPropagation();
		});

		$("#reply").change(function(e) {
			if (!MTConversation.replyShowing) {
				MTConversation.replyShowing = true;
				$("#reply").removeClass("replyPlaceholder");

				// Put the cursor at the end of the textarea.
				var pos = textarea.val().length;
				textarea.selectRange(pos, pos);
			}
		});

		$(document).click(function(e) { MTConversation.hideReply(); });

	},

	// Highlight a post.
	highlightPost: function(post) {
		$(post).addClass("highlight");
		setTimeout(function() {
			$(post).removeClass("highlight");
		}, 5000);
	},

	// Hide consecutive avatars from the same member.
	redisplayAvatars: function() {
		// console.log('redisplayAvatars');
		// Loop through the avatars in the posts area and compare each one's src with the one before it.
		// If they're the same, hide it.
		var prevId = null;
		$("#conversationPosts > .mtComment").each(function() {
			var id = $(this).find("div.post").data("memberid");
			if (prevId == id) $(this).find("div.avatar").hide();
			else $(this).find("div.avatar").show();
			prevId = id;
		});
	},

	// Delete a post.
	deletePost: function(postId) {
		//$.hideToolTip();
		// Make the ajax request.
		$.MTAjax({
			headers: {Action:'delete'},
			type: "post",
			data: {id:postId},
			beforeSend: function() {
				createLoadingOverlay("comment-" + postId, "comment-" + postId);
			},
			complete: function() {
				hideLoadingOverlay("comment-" + postId, true);
			},
			success: function(data) {
				if (data.message === false) return;
				//$("#comment-"+postId).replaceWith(data.view);
				data.object.link = MTConversation.slug.replace("$$$",postId);
				data.object.link_restore = MTConversation.slug.replace("$$$",'restore-'+postId);
				data.object.timeMarker = ''
				$("#comment-"+postId).replaceWith(new EJS({url: MT.assetsPath + MT.deletedCommentTpl}).render(data.object));
				MTConversation.redisplayAvatars();
			}
		});
	},
	// Restore a post.
	restorePost: function(postId) {

		//$.hideToolTip();

		// Make the ajax request.
		$.MTAjax({
			headers: {Action:'restore'},
			type: "post",
			data: {id:postId},
			beforeSend: function() {
				createLoadingOverlay("comment-" + postId, "comment-" + postId);
			},
			complete: function() {
				hideLoadingOverlay("comment-" + postId, true);
			},
			success: function(data) {
				if (data.success === false) return;
				//$("#comment-"+postId).replaceWith(data.view);
				data.object.link = MTConversation.slug.replace("$$$",postId);
				data.object.link_restore = MTConversation.slug.replace("$$$",'delete-'+postId);
				data.object.timeMarker = '';
				$("#comment-"+postId).replaceWith(new EJS({url: MT.assetsPath + MT.commentTpl}).render(data.object));
				MTConversation.redisplayAvatars();
				$('pre').each(function(i, e) {hljs.highlightBlock(e)});
				// setTimeout(function () { $('a.time').timeago() }, 5000);
			}
		});
	},
	// Edit a post.
	editPost: function(postId) {

		//$.hideToolTip();
		var post = $("#comment-" + postId);

		// Make the ajax request.
		$.MTAjax({
			headers: {Action:'get'},
			type: "post",
			data: {id:postId},
			beforeSend: function() {
				createLoadingOverlay("comment-" + postId, "comment-" + postId);
			},
			complete: function() {
				hideLoadingOverlay("comment-" + postId, true);
			},
			success: function(data) {
				if (data.success === false) return;
				MTConversation.editingPosts++;
				var startHeight = $(".postContent", post).height();

				//data.object.link = MTConversation.slug.replace("$$$",postId);
				//data.object.timeMarker = ''

				//var formReply = $('#mt_cf_conversationReply').clone();
				//formReply.find("textarea").text(data.object.content);
				//formReply.find(".post").attr("id","comment-" + postId).removeClass('replyPlaceholder');
				//formReply.find(".editButtons").html('<input type="submit" name="save" value="Сохранить изменения" class="big submit button"><input type="submit" name="cancel" value="Отмена" class="big cancel button">');

				// Replace the post HTML with the new stuff we just got.
				post.replaceWith($(data.object.html).find(".post"));
				var newPost = $("#comment-" + postId);
				var textarea = $("textarea", newPost);

				// Save the old post HTML for later.
				newPost.data("oldPost", post);

				// Set up the text area.
				var len = textarea.val().length;
				textarea.TextAreaExpander(200, 700).focus().selectRange(len, len);
				new MTAutoCompletePopup(textarea, "@");

				// Add click handlers to the cancel/submit buttons.
				$(".cancel", newPost).click(function(e) {
					e.preventDefault();
					MTConversation.cancelEditPost(postId);
				});
				$(".submit", newPost).click(function(e) {
					e.preventDefault();
					MTConversation.saveEditPost(postId, textarea.val());
				});

				// Animate the post's height.
				var newHeight = $(".postContent", newPost).height();
				$(".postContent", newPost).height(startHeight).animate({height: newHeight}, "fast", function() {
					$(this).height("");
				});

				MTConversation.redisplayAvatars();

				// Scroll to the bottom of the edit area if necessary.
				var scrollTo = newPost.offset().top + newHeight - $(window).height() + 10;
				if ($(document).scrollTop() < scrollTo) $.scrollTo(scrollTo, "slow");

				// Regsiter the Ctrl+Enter and Escape shortcuts on the post's textarea.
				textarea.keydown(function(e) {
					if (e.ctrlKey && e.which == 13) {
						MTConversation.saveEditPost(postId, this.value);
						e.preventDefault();
					}
					if (e.which == 27) {
						MTConversation.cancelEditPost(postId);
						e.preventDefault();
					}
				});
				// setTimeout(function () { $('a.time').timeago() }, 5000);
			}
		});
	},

	// Save an edited post to the database.
	saveEditPost: function(postId, content) {

		// Disable the buttons.
		var post = $("#comment-" + postId);
		$(".button", post).disable();

		// Make the ajax request.
		$.MTAjax({
			headers: {Action:'edit'},
			type: "post",
			data: {content: content, id: postId},
			beforeSend: function() {
				createLoadingOverlay("comment-" + postId, "comment-" + postId);
			},
			complete: function() {
				hideLoadingOverlay("comment-" + postId, true);
				$(".button", post).enable();
			},
			success: function(data) {
				if (data.success === false) return;

				var startHeight = $(".postContent", post).height();

				// Replace the post HTML with the new post we just got.
				post.replaceWith(new EJS({url: MT.assetsPath + MT.commentTpl}).render(data.object));
				var newPost = $("#comment-" + postId);

				// Animate the post's height.
				var newHeight = $(".postContent", newPost).height();
				$(".postContent", newPost).height(startHeight).animate({height: newHeight}, "fast", function() {
					$(this).height("");
				});

				MTConversation.editingPosts--;
				MTConversation.redisplayAvatars();
				$('pre').each(function(i, e) {hljs.highlightBlock(e)});
				// setTimeout(function () { $('a.time').timeago() }, 5000);
			}
		});
	},

	// Cancel editing a post.
	cancelEditPost: function(postId) {
		MTConversation.editingPosts--;
		var post = $("#comment-" + postId);

		var scrollTop = $(document).scrollTop();

		// Change the post control and body HTML back to what it was before.
		var startHeight = $(".postContent", post).height();
		post.replaceWith(post.data("oldPost"));
		var newPost = $("#comment-" + postId);

		// Animate the post's height.
		var newHeight = $(".postContent", newPost).height();
		$(".postContent", newPost).height(startHeight).animate({height: newHeight}, "fast", function() {
			$(this).height("");
		});

		$.scrollTo(scrollTop);
	},

	// scroll to element
	scrollElem: function(elm) {
		$.scrollTo(elm);
		/*$('html, body').animate({
					scrollTop: $(elm).offset().top
				});*/
	},

	// Quote a post.
	quotePost: function(postId,member,content, multi, idx) {
		var selection = ""+$.getSelection();
		$.MTAjax({
			headers: {Action:'quote'},
			type: "post",
			data: {id:postId},
			success: function(data) {
				if(data.success === false && data.total == 0) {
					MTMessages.showMessage(data.message, 'msg-error');
					return;
				}
				var top = $(document).scrollTop();
				MTConversation.quote("reply", selection ? selection : data.object.content, data.object.user, data.object.id, null, true, idx);
				// If we're "multi" quoting (i.e. shift is being held down), keep our scroll position static.
				// Otherwise, scroll down to the reply area.
				if (!multi) {
					$("#jumpToReply").click();
				} else {
					$("#reply").change();
					$.scrollTo(top);
				}
			},
			global: true
		});
	},

	// Condense the reply box back into a placeholder.
	hideReply: function() {
		if (!MTConversation.replyShowing || $("#reply textarea").val()) return;
		// Save the scroll top and height.
		var scrollTop = $(document).scrollTop();
		var oldHeight = $("#reply .postContent").height();
		MTConversation.replyShowing = false;
		$("#reply").addClass("replyPlaceholder");
		var newHeight = $("#reply .postContent").height();
		$("#reply .postContent").height(oldHeight).animate({height: newHeight}, "fast", function() {
			$(this).height("");
		});
	},
	// Add a reply.
	addReply: function() {
		var content = $("#reply textarea").val();
		var saveemail = $("#reply .saveEmail").val();
		var savename = $("#reply .saveName").val();

		// Disable the reply/draft buttons.
		$("#reply .postReply, #reply .saveDraft").disable();

		// Make the ajax request.
		$.MTAjax({
			type: "post",
			headers: {Action:'add'},
			data: {content: content, name: savename, email: saveemail, conversation: MT.conversation},
			success: function(data) {

				if(data.success != true) {
					$("#reply .postReply, #reply .saveDraft").enable();
					if(data.message && data.message.length > 0){
						MTMessages.showMessage(data.message, 'msg-error');
						if(data.premoderated === true) {
							$("#reply textarea").val("");
							MTConversation.togglePreview("reply", false);
							MTConversation.hideReply();
						}
					}
					if(data.data && data.data.length > 0) {
						$.each(data.data, function(i,item) {
							MTMessages.showMessage(item.msg, 'msg-'+item.id);
						});
					}
					return;
				}

				// Hide messages which may have been previously triggered.
				MTMessages.hideMessage("waitToReply");
				MTMessages.hideMessage("emptyPost");

				$("#conversationHeader .labels .label-draft").remove();
				$("#reply textarea").val("");
				MTConversation.togglePreview("reply", false);
				MTConversation.hideReply();

				MTConversation.postCount++;

				// Create a dud "more" block and then add the new post to it.
				var moreItem = $("<dib></div>").appendTo("#conversationPosts");
				MTScrubber.count = MTConversation.postCount;
				// data.object.link = MT.link + '#comment-' + data.object.id;
				idComm = data.object.id;
				newComment = new EJS({url: MT.assetsPath + MT.commentTpl}).render(data.object);
				MTScrubber.addItems(MTConversation.postCount, newComment, moreItem, true);
				MTConversation.redisplayAvatars();
				MTConversation.highlightPost('#comment-' + idComm);
				$('.total_mt').text(MTConversation.postCount);
				$('pre').each(function(i, e) {hljs.highlightBlock(e)});

				// Reset the post-checking timeout.
				MTConversation.updateInterval.reset(MT.conversationUpdateIntervalStart);
				$('#reply-previewCheckbox').attr('checked', false);

			},
			beforeSend: function() {
				createLoadingOverlay("reply", "reply");
			},
			complete: function() {
				hideLoadingOverlay("reply", false);
			}
		});
	},

	//***** POST FORMATTING

	// Add a quote to a textarea.
	quote: function(id, quote, name, postId, insert, hrzn, idx) {
		var argument = postId || name ? (idx ? idx + " user=" : " ") + (name ? name : "Name") : " ";
		var startTag = "[quote " + (argument ? "id=" + argument : "") + "]" + (quote ? quote+" " : " ");
		var endTag = "[/quote]";

		// If we're inserting the quote, add it to the end of the textarea.
		if (insert) MTConversation.insertText($("#" + id + " textarea"), startTag + endTag + "\n");

		// Otherwise, wrap currently selected text with the quote.
		else MTConversation.wrapText($("#" + id + " textarea"), startTag, endTag);
	},


	// Add text to the reply area at the very end, and move the cursor to the very end.
	insertText: function(textarea, text) {
		textarea = $(textarea);
		textarea.focus();
		textarea.val(textarea.val() + text);
		textarea.focus();

		// Trigger the textarea's keyup to emulate typing.
		textarea.trigger("keyup");
	},

		// Add text to the reply area, with the options of wrapping it around a selection and selecting a part of it when it's inserted.
	wrapText: function(textarea, tagStart, tagEnd, selectArgument, defaultArgumentValue) {

		textarea = $(textarea);

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
	togglePreview: function(id, preview) {

		// If the preview box is checked...
		if (preview) {

			// Hide the formatting buttons.
			$("#" + id + " .formattingButtons").hide();
			$("#" + id + "-preview").html("");

			// Get the formatted post and show it.
			$.MTAjax({
				type: "post",
				headers: {Action:'preview'},
				data: {content: $("#" + id + " textarea").val(), name: $("#reply .saveName").val(), email: $("#reply .saveEmail").val(), conversation: MT.conversation, ctx: MT.ctx},
					success: function(data) {

						if(data.success != true) {
							$("#reply .postReply, #reply .saveDraft").enable();
							$.each(data.data, function(i,item) {
								MTMessages.showMessage(item.msg, 'msg-'+item.id);
							});
							return;
						}

					// Keep the minimum height.
					$("#" + id + "-preview").css("min-height", $("#" + id + "-textarea").innerHeight());

					// Hide the textarea, and show the preview.
					$("#" + id + " textarea").hide();
					$("#" + id + "-bg").hide();
					$("#" + id + "-preview").show()
					$("#" + id + "-preview").html(data.message.content);
					$('pre').each(function(i, e) {hljs.highlightBlock(e)});
					// setTimeout(function () { $('a.time').timeago() }, 5000);
				}
			});
		}

		// The preview box isn't checked...
		else {
			// Show the formatting buttons and the textarea; hide the preview area.
			$("#" + id + " .formattingButtons").show();
			$("#" + id + " textarea").show();
			$("#" + id + "-bg").show();
			$("#" + id + "-preview").hide();
			// setTimeout(function () { $('a.time').timeago() }, 5000);
		}

	}

};
$(function() {
	$("body").prepend('<div id="messages"></div>'); //
	$("#reply.post").addClass("replyPlaceholder");
	MTConversation.init();
});

$(document).ready(function() {
  $('a.time').timeago();
});