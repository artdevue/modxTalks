// Russian (Template)
(function() {
  function numpf(n, f, s, t) {
    var n10 = n % 10;
    if ( (n10 == 1) && ( (n == 1) || (n > 20) ) ) {
      return f;
    } else if ( (n10 > 1) && (n10 < 5) && ( (n > 20) || (n < 10) ) ) {
      return s;
    } else {
      return t;
    }
  }

  jQuery.timeago.settings.strings = {
    prefixAgo: null,
    prefixFromNow: "через",
    suffixAgo: "назад",
    suffixFromNow: null,
    seconds: function(value) { return numpf(value, "%d секунду", "%d секунды", "%d секунд"); },
    justNow: "Только что",
    minute: "1 минуту",
    minutes: function(value) { return numpf(value, "%d минуту", "%d минуты", "%d минут"); },
    hour: "Менее часа",
    hours: function(value) { return numpf(value, "%d час", "%d часа", "%d часов"); },
    day: "1 день",
    days: function(value) { return numpf(value, "%d день", "%d дня", "%d дней"); },
    month: "1 месяц",
    months: function(value) { return numpf(value, "%d месяц", "%d месяца", "%d месяцев"); },
    year: "1 год",
    years: function(value) { return numpf(value, "%d год", "%d года", "%d лет"); },
    yesterday: "Вчера",
    today: "Сегодня",
  };
})();