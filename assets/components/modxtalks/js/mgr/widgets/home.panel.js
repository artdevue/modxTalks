var tokenDelimiter = ':';
var acTab = hashCheck();
function hashCheck() {
    var token = window.location.hash.substr(1);
    if(token){
        var parts = token.split(tokenDelimiter);
        var tabPanel = Ext.getCmp(parts[0]);
        var tabId = parts[1];
        return tabId;
    } else {
        return 0;
    }
}

window.onhashchange = function () {
    Ext.getCmp('homeTab').setActiveTab(hashCheck());
};

modxTalks.panel.Home = function(config) {
    config = config || {};
     Ext.apply(config, {
        cls: 'container'
        ,renderTo: Ext.getBody()
        ,unstyled: true
        ,defaults: { collapsible: false ,autoHeight: true }
        ,id: 'panelHome'
        ,buttons: this.getButtons(config)
        ,items: [{
            html: '<h2>'+_('modxtalks')+' <span class="logo-desc"> // '+_('modxtalks.menu_desc')+'</span></h2>'
            ,border: false
            ,cls: 'modx-page-header head-logo'
        },{
            xtype: 'modx-vtabs'
            ,activeTab: 0
            ,autoWidth: true
            ,resizable: true
            ,monitorResize:true
            ,deferredRender: false
            ,cls: 'x-panel-bwrap'
            ,bodyStyle: 'padding: 10px'
            ,id: 'homeTab'
            ,enableTabScroll : true
            ,activeTab: acTab
                ,defaults: {
                bodyCssClass: 'vertical-tabs tabs-modxtalks'
                ,autoScroll: true
                ,autoHeight: true
                ,autoWidth: true
                ,layout: 'form'
            },
            items: [{
                title: _('modxtalks.conversations')
                ,id: 'home'
                ,defaults: { autoHeight: true }
                ,items: [{
                    html: '<p>'+ _('modxtalks.menu_desc') + ' ' + _('modxtalks.management_desc') +'</p>'
                    ,border: true
                    ,bodyCssClass: 'panel-desc'
                },{
                    xtype: 'modxtalks-grid-posts'
                    ,preventRender: true
                }]
            },{
                title: _('modxtalks.not_confirmed')
                ,cls: 'not-confirmed'
                ,defaults: { autoHeight: true }
                ,id: 'not-confirmed'
                ,items: [{
                    html: '<p>'+ _('modxtalks.management_unconfirmed_desc') +'</p>'
                    ,border: true
                    ,bodyCssClass: 'panel-desc'
                },{
                    xtype: 'modxtalks-panel-unconfirmed'
                    ,preventRender: true
                }]
            },{
                title: _('modxtalks.blocking_ip')
                ,cls: 'loc-manager'
                ,defaults: { autoHeight: true }
                ,id: 'ip-blocking'
                ,items: [{
                    html: '<p>'+ _('modxtalks.ipblock_desc') +'</p>'
                    ,border: true
                    ,bodyCssClass: 'panel-desc'
                },{
                    xtype: 'modxtalks-grid-ipblock'
                    ,preventRender: true
                }]
            },{
                title: _('modxtalks.blocking_email')
                ,cls: 'email-manager'
                ,defaults: { autoHeight: true }
                ,id: 'email-blocking'
                ,items: [{
                    html: '<p>'+ _('modxtalks.emailblock_desc') +'</p>'
                    ,border: true
                    ,bodyCssClass: 'panel-desc'
                },{
                    xtype: 'modxtalks-grid-emailblock'
                    ,preventRender: true
                }]
            }],
        listeners: {
            'tabchange': function(tabPanel, tab){
                    Ext.History.add('modxTalks' + tokenDelimiter + tab.id);
            }
        }
        }]
    });
    modxTalks.panel.Home.superclass.constructor.call(this, config);
};

Ext.extend(modxTalks.panel.Home, MODx.Panel, {
    getButtons: function(cfg) {
        var btns = [];
        btns.push({
            text: _('help_ex'),
            handler: this.loadHelpPane,
            id: 'modx-abtn-help'
        });
        return btns;
    },
    loadHelpPane: function(b) {
        var url = 'http://modxtalks.artdevue.com/' + MODx.config.manager_language;
        if (!url) return false;
        MODx.helpWindow = new Ext.Window({
            title: _('help'),
            width: 850,
            height: 500,
            resizable: true,
            maximizable: true,
            modal: false,
            layout: 'fit',
            html: '<iframe src="' + url + '" width="100%" height="100%" frameborder="0"></iframe>'
        });
        MODx.helpWindow.show(b);
        return true;
    }
});

Ext.reg('modxtalks-panel-home', modxTalks.panel.Home);
