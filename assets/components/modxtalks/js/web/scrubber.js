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
        var i = 0,
            count = Math.min(this.startFrom + this.perPage, this.count + 1);

        for (i = this.startFrom; i < count; i++) {
            this.loadedItems.push(i);
        }

        var headerTop = this.header.offset().top;
        var headerWidth = this.header.width();
        var scrubberTop = this.scrubber.length && (MTScrubber.body.offset().top - 10);

        // Whenever the user scrolls within the window...
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
            } else {
                MTScrubber.body.css({
                    paddingTop: 0
                });
                MTScrubber.header.removeClass("floating").css({
                    position: '',
                    top: '',
                    width: ''
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
                MTScrubber.items.addClass("mt_scrubtop");
            } else {
                MTScrubber.scrubber.removeClass("mt_floating").css({
                    position: '',
                    top: ''
                });
                MTScrubber.items.removeClass("mt_scrubtop");
            }

            // Now we need to work out where we are in the content and highlight the appropriate
            // index in the scrubber. Go through each of the items on the page...
            jQuery(".mt_mtComment", MTScrubber.items).each(function () {
                var item = jQuery(this);

                // If we've scrolled past this item, continue in the loop.
                if (y > item.offset().top + item.outerHeight() - MTScrubber.header.outerHeight()) {
                    return true;
                } else {
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
            }
        });
        // Alright, so, all the scrolling event stuff is done! Now we need to make the "next/previous page" and
        // "load more" blocks clickable.
        jQuery(MTScrubber.body).on("click", ".mt_scrubberMore a", function (e) {
            e.preventDefault();
            jQuery(this).parent().addClass("mt_loading");
            var moreItem = jQuery(this).parent();

            var backwards,
                position;
            // If this is the "previous page" block...
            if (moreItem.is(".mt_scrubberPrevious")) {
                backwards = true;
                position = Math.min.apply(Math, MTScrubber.loadedItems) - MTScrubber.perPage;
                if (position <= 0) position = 1;
            } else if (moreItem.is(".mt_scrubberNext")) {
                backwards = false;
                position = Math.max.apply(Math, MTScrubber.loadedItems) + 1;
            } else {
                backwards = moreItem.offset().top - jQuery(document).scrollTop() < 250;
                position = backwards ? jQuery(this).parent().data("positionEnd") - MTScrubber.perPage + 1 : jQuery(this).parent().data("positionStart");
            }

            MTScrubber.loadItemsCallback(position, function (addIt) {
                if (backwards) {
                    var firstItem = moreItem.next();
                    var scrollOffset = firstItem.offset().top - jQuery(document).scrollTop();
                }

                MTScrubber.addItems(addIt.startFrom, addIt.view, moreItem);

                if (backwards) jQuery.scrollTo(firstItem.offset().top - scrollOffset);
            });
        });

        jQuery(".mt_scrubber a", MTScrubber.body).click(function (e) {
            e.preventDefault();

            // Get the index of that this element represents.
            var index = jQuery(this).parent().data("index");
            if (index == "last") index = Infinity;
            else if (index == "first") index = 1;

            var found = MTScrubber.scrollToIndex(index);

            if (!found) {
                // 1. Work out where this index will be in the context of the items we currently have
                // rendered. We need to find the "more" block that the index will be in, and the item
                // before that "more" block if there is one.
                var moreItem = null,
                    prevPost = null;
                jQuery("div", MTScrubber.items).each(function () {
                    if (jQuery(this).is(".mt_scrubberMore")) moreItem = jQuery(this);
                    else {
                        var item = jQuery(this).first();

                        // If this item is past the index we're looking for, break out of the loop.
                        if (item.data("index") > index) return false;

                        moreItem = null;
                        prevPost = jQuery(this);
                    }
                });
                // 2. If a "more" block wasn't found, and no previous items were found, then scroll right up to the top.
                if (!moreItem && !prevPost) MTScrubber.scrollTo(0);

                // 3. If a "more" block wasn't found, and a previous item was found, scroll to the previous item.
                else if (!moreItem && prevPost && index != Infinity) MTScrubber.scrollTo(prevPost.offset().top);

                // 4. If a "more" block WAS found, scroll to it, and load the items.
                else if (moreItem) {
                    MTScrubber.scrollTo(moreItem.offset().top);
                    moreItem.addClass("mt_loading");
                    MTScrubber.loadItemsCallback(index, function (addIt) {

                        // If we're scrolling down to the very bottom, save the scroll position relative to the
                        // bottom of the items area.
                        if (index == Infinity) var scrollOffset = MTScrubber.items.offset().top + MTScrubber.items.outerHeight() - jQuery(document).scrollTop();
                        MTScrubber.addItems(addIt.startFrom, addIt.view, moreItem);
                        // Restore the scroll position, or scroll to the index which we should now have items for.
                        if (index == Infinity) {
                            MTScrubber.scrollToIndex(MTScrubber.postCount, true);
                            //jQuery.scrollTo(MTScrubber.items.offset().top + MTScrubber.items.outerHeight() - scrollOffset);
                        } else {
                            MTScrubber.scrollToIndex(index);
                        }

                    }, true);
                }

            }
        });
    },

    // Scroll to a specific position, applying an animation and taking the fixed header into account.
    scrollTo: function (position) {
        jQuery.scrollTo(position - MT.scrubberOffsetTop - MTScrubber.header.outerHeight() + 1, "slow");
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
        var view = jQuery(items),
            i = 0,
            newStartFrom = startFrom;

        view = view.filter(".mt_mtComment");
        items = [];

        for (i = 0; i < MTScrubber.perPage; i++) {
            if (startFrom + i - 1 >= MTScrubber.count) break;
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
        if (MTScrubber.loadedItems.indexOf(startFrom - 1) == -1 && items.first().prev().is("div:not(.mt_scrubberMore)")) {
            scrubberMore = scrubberMore.clone();
            items.first().before(scrubberMore);
            // Work out the range of items that this "more" block covers. We know where it ends, so loop backwards
            // from there and find the start.
            for (i = startFrom - 1; i > 0; i--) {
                if (MTScrubber.loadedItems.indexOf(i) != -1) break;
            }
            scrubberMore.data("positionStart", i + 1);
            scrubberMore.data("positionEnd", startFrom - 1);
        }

        // If we don't have the item immediately AFTER the LAST item that we just loaded (ie. there's a gap), we
        // need to put a "more" block there.
        if (MTScrubber.loadedItems.indexOf(startFrom + items.length) == -1 && items.last().next().is("div:not(.mt_scrubberMore)")) {
            scrubberMore = scrubberMore.clone();
            items.last().after(scrubberMore);
            // Work out the range of items that this "more" block covers. We know where it starts, so loop forwards
            // from there and find the end.
            for (i = startFrom + items.length; i < MTScrubber.count; i++) {
                if (MTScrubber.loadedItems.indexOf(i) != -1) break;
            }
            scrubberMore.data("positionStart", startFrom + items.length);
            scrubberMore.data("positionEnd", i - 1);
        }

        //if (animate) items.hide().fadeIn("slow");

        // Update the loadedItems index with the new item positions we have loaded.
        for (i = startFrom; i < startFrom + items.length; i++) {
            if (MTScrubber.loadedItems.indexOf(i) == -1) MTScrubber.loadedItems.push(i);
        }

        // If we have the very first item in the collection, remove the "older" block.
        if (Math.min.apply(Math, MTScrubber.loadedItems) <= 1) jQuery(".mt_scrubberPrevious").remove();

        // If we have the very last item in the collection, remove the "newer" block.
        if (Math.max.apply(Math, MTScrubber.loadedItems) >= MTScrubber.count) jQuery(".mt_scrubberNext").remove();

        jQuery('pre').each(function (i, e) {
            hljs.highlightBlock(e)
        });
        setTimeout(function () {
            jQuery('a.mt_time').timeago()
        }, 5000);

        MTConversation.figures();
    }
};
