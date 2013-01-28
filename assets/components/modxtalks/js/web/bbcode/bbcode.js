var BBCode = {
    bold: function(id) {
        MTConversation.wrapText($("#"+id+" textarea"), "[b]", "[/b]");
    },
    video: function(id) {
        MTConversation.wrapText($("#"+id+" textarea"), "[video]", "[/video]");
    },
    quote: function(id) {
        MTConversation.wrapText($("#"+id+" textarea"), "[quote]", "[/quote]");
    },
    italic: function(id) {
        MTConversation.wrapText($("#"+id+" textarea"), "[i]", "[/i]");
    },
    strikethrough: function(id) {
        MTConversation.wrapText($("#"+id+" textarea"), "[s]", "[/s]");
    },
    header: function(id) {
        MTConversation.wrapText($("#"+id+" textarea"), "[h]", "[/h]");},
    link: function(id) {
        MTConversation.wrapText($("#"+id+" textarea"), "[url=http://example.com]", "[/url]", "http://example.com", "link text");
    },
    image: function(id) {
        MTConversation.wrapText($("#"+id+" textarea"), "[img]", "[/img]", "", "http://example.com/image.jpg");
    },
    fixed: function(id) {
        MTConversation.wrapText($("#"+id+" textarea"), "[code]", "[/code]");
    },

};