/**
 * Timeago is a jQuery plugin that makes it easy to support automatically
 * updating fuzzy timestamps (e.g. "4 minutes ago" or "about 1 day ago").
 *
 * @name timeago
 * @version 1.0.1
 * @requires jQuery v1.2.3+
 * @author Ryan McGeary
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 *
 * For usage and examples, visit:
 * http://timeago.yarp.com/
 *
 * Copyright (c) 2008-2013, Ryan McGeary (ryan -[at]- mcgeary [*dot*] org)
 */
(function($) {
  $.timeago = function(timestamp) {
    if (timestamp instanceof Date) {
      return inWords(timestamp);
    }
    else if (typeof timestamp === "string") {
      return inWords($.timeago.parse(timestamp));
    }
    else if (typeof timestamp === "number") {
      return inWords(new Date(timestamp));
    }
    else {
      return inWords($.timeago.datetime(timestamp));
    }
  };
  var $t = $.timeago;

  $.extend($.timeago, {
    settings: {
      refreshMillis: 10000,
      allowFuture: false,
      strings: {
        prefixAgo: null,
        prefixFromNow: null,
        justNow: "just now",
        suffixAgo: "ago",
        suffixFromNow: "from now",
        seconds: function(v) { return v == 1 ? "%d second" : "%d seconds"; },
        minute: "about a minute",
        minutes: function(v) { return v == 1 ? "%d minute" : "%d minutes"; },
        hour: "about an hour",
        hours: function(v) { return v == 1 ? "%d hour" : "%d hours"; },
        day: "a day",
        days: function(v) { return v == 1 ? "%d day" : "%d days"; },
        month: "about a month",
        months: function(v) { return v == 1 ? "%d month" : "%d months"; },
        year: "about a year",
        years: function(v) { return v == 1 ? "%d year" : "%d years"; },
        wordSeparator: " ",
        numbers: []
      }
    },
    inWords: function(distanceMillis) {
      var words,
          $l = this.settings.strings,
          prefix = $l.prefixAgo,
          suffix = $l.suffixAgo,
          separator = $l.wordSeparator || "",
          seconds = Math.abs(distanceMillis) / 1000,
          minutes = Math.floor(seconds / 60),
          hours = Math.floor(minutes / 60),
          days = Math.floor(hours / 24),
          months = Math.floor(days / 30),
          years = Math.floor(days / 365),
          seconds = Math.floor(seconds);

      if (this.settings.allowFuture) {
        if (distanceMillis < 0) {
          prefix = $l.prefixFromNow;
          suffix = $l.suffixFromNow;
        }
      }

      function substitute(stringOrFunction, number) {
        var string = $.isFunction(stringOrFunction) ? stringOrFunction(number, distanceMillis) : stringOrFunction;
        var value = ($l.numbers && $l.numbers[number]) || number;
        return string.replace(/%d/i, value);
      }

      if (seconds < 10) { words = $l.justNow; suffix = ''; }
      else if (seconds < 60) { words = substitute($l.seconds, seconds); }
      else if (minutes < 45) { words = substitute($l.minutes, minutes); }
      else if (minutes < 60) { words = substitute($l.hour, 1); }
      else if (hours < 24) { words = substitute($l.hours, hours); }
      else if (days < 30) { words = substitute($l.days, days); }
      else if (days < 45) { words = substitute($l.month, 1); }
      else if (days < 365) { words = substitute($l.months, months); }
      else if (years < 1.5) { words = substitute($l.year, 1); }
      else { words = substitute($l.years, years); }

      if ($l.wordSeparator === undefined) { separator = ' '; }
      return $.trim([prefix, words, suffix].join(separator));
    },
    parse: function(iso8601) {
      var s = $.trim(iso8601);
      s = s.replace(/\.\d+/,""); // remove milliseconds
      s = s.replace(/-/,"/").replace(/-/,"/");
      s = s.replace(/T/," ").replace(/Z/," UTC");
      s = s.replace(/([\+\-]\d\d)\:?(\d\d)/," $1$2"); // -04:00 -> -0400
      return new Date(s);
    },
    datetime: function(elem) {
      var iso8601 = $t.isTime(elem) ? $(elem).attr("datetime") : $(elem).attr("title");
      return $t.parse(iso8601);
    },
    isTime: function(elem) {
      // jQuery's `is()` doesn't play well with HTML5 in IE
      return $(elem).get(0).tagName.toLowerCase() === "time"; // $(elem).is("time");
    }
  });

  $.fn.timeago = function() {
    var self = this;
    self.each(refresh);

    var $s = $t.settings;
    if ($s.refreshMillis > 0) {
      setInterval(function() { self.each(refresh); }, $s.refreshMillis);
    }
    return self;
  };

  function refresh() {
    var data = prepareData(this);
    if (!isNaN(data.datetime)) {
      $(this).text(inWords(data.datetime));
    }
    return this;
  }

  function prepareData(element) {
    element = $(element);
    if (!element.data("timeago")) {
      element.data("timeago", { datetime: $t.datetime(element) });
      var text = $.trim(element.text());
      if (text.length > 0 && !($t.isTime(element) && element.attr("title"))) {
        element.attr("title", text);
      }
    }
    return element.data("timeago");
  }

  function inWords(date) {
    return $t.inWords(distance(date));
  }

  function distance(date) {
    return (new Date().getTime() - date.getTime());
  }

  // fix for IE6 suckage
  document.createElement("abbr");
  document.createElement("time");
}(jQuery));
