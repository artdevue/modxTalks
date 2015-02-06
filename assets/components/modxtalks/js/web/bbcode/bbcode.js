var BBCode = {
    bold: function (btn) {
        MTConversation.wrapText(BBCode.textarea(btn), "[b]", "[/b]");
    },
    video: function (btn) {
        MTConversation.wrapText(BBCode.textarea(btn), "[video]", "[/video]");
    },
    quote: function (btn) {
        MTConversation.wrapText(BBCode.textarea(btn), "[quote]", "[/quote]");
    },
    italic: function (btn) {
        MTConversation.wrapText(BBCode.textarea(btn), "[i]", "[/i]");
    },
    strike: function (btn) {
        MTConversation.wrapText(BBCode.textarea(btn), "[s]", "[/s]");
    },
    header: function (btn) {
        MTConversation.wrapText(BBCode.textarea(btn), "[h]", "[/h]");
    },
    link: function (btn) {
        MTConversation.wrapText(BBCode.textarea(btn), "[url=http://example.com]", "[/url]", "http://example.com", "link text");
    },
    image: function (btn) {
        MTConversation.wrapText(BBCode.textarea(btn), "[img]", "[/img]", "", "http://example.com/image.jpg");
    },
    code: function (btn) {
        MTConversation.wrapText(BBCode.textarea(btn), "[code]", "[/code]");
    },
    textarea: function (btn) {
        return $(btn).closest('.mt_thing').find('textarea');
    }
};
