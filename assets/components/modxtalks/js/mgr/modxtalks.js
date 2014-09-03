Ext.apply(Ext.form.VTypes, {
    //  vtype validation function
    IPAddress: function (v) {
        return /^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/.test(v);
    },
    // vtype Text property: The error text to display when the validation function returns false
    IPAddressText: _('modxtalks.ip_creat'),
    // vtype Mask property: The keystroke filter mask
    IPAddressMask: /[\d\.]/i
});

var modxTalks = function (config) {
    config = config || {};
    modxTalks.superclass.constructor.call(this, config);
};

Ext.extend(modxTalks, Ext.Component, {
    page: {},
    window: {},
    grid: {},
    tree: {},
    panel: {},
    combo: {},
    config: {}
});

Ext.reg('modxtalks', modxTalks);

modxTalks = new modxTalks();
