Ext.onReady(function() {
    document.getElementById("modx-resource-header").className += " headmodxtalks";    

    var tabpanel = Ext.getCmp('modx-resource-tabs');
    var res = tabpanel.ownerCt.config.record;
    //var fulUrlRes = MODx.config.site_url + res.uri;
    if (Ext.isEmpty(res.pagetitle)) {
                Ext.getCmp('modx-resource-header').getEl().update('<h2>' + _('document_new') + _('modxtalks.with_comments') + '</h2>');
            }
    var rec = res.properties;    
    if (!rec) var rec = {modxtalks:{}};
    var total = 0;
    Ext.Ajax.request({
        url: modxTalks.config.connector_url,
        params: {
            action: 'mgr/comments/getList',
            conversationName: res.class_key + '-' + res.id,
        },
        success: function(a) {
            var data = Ext.decode(a.responseText);
            document.getElementById('totaltalks').textContent = data.total;
            document.getElementById('deletedtalks').textContent = data.deleted;
            document.getElementById('notpublishedtalks').textContent = data.notpublished;
        }
    });

    /*var modxTalksStore = new Ext.data.JsonStore({
        url: modxTalks.config.connector_url,
        baseParams: { action: 'mgr/comments/getList', 'conversationName': res.class_key + '-' + res.id },
        autoLoad: true,
        // root:'reader',
        listeners: {
            load: {
                fn: function(record) {
                    console.log(record);
                    total = a.totalLength;
                    document.getElementById('totaltalks').textContent = total;
                },scope:this
            }
        }
    });*/
    // console.log(modxTalksStore.reader);
    tabpanel.add({
        title: _('modxtalks.comments_menu'),
        forceLayout: true,
        id:'modxtalks-settings',
        cls: 'modxtalkstab',
        closable: false,
        items: [{
            html: '<div class="modtalks_desc"><div class="desc_comment"><p>'+ _('modxtalks.desc_titl') +'</p></div>'
                + '<div class="infortalks"><div class="informer"><a onclick="javascript: window.open(\''+ (MODx.config.site_url + res.uri) +'\',\'_blank\');return false;" href="#settings:modxtalks-settings"><span id="totaltalks" class="title totaltalks">'+total+'</span><span class="text">'+ _('modxtalks.comments_total') +'</span></div>'
                + '<div class="informer"><a href="#settings:modxtalks-settings"><span id="deletedtalks" class="title delet">0</span><span class="text">'+ _('modxtalks.delete') +'</span></div>'
                + '<div class="informer"><a href="'+MODx.config.manager_url+'index.php?a='+MODx.action['modxtalks:index']+'#modxTalks:not-confirmed"><span id="notpublishedtalks" class="title">0</span><span class="text">'+ _('modxtalks.not_confirmed') +'</span></div></div></div>',
            id: 'modxtalks-desc',
            border: false,
            bodyCssClass: 'panel-desc',
            bodyStyle: 'margin-bottom: 10px'
        },{
            layout: 'column'
            ,border: false
            ,defaults: {
                layout: 'form',
                labelAlign: 'top',
                anchor: '100%',
                border: false,
                cls:'main-wrapper',
                labelSeparator: '',
            },
            items: [{
                columnWidth: .333,
                items: [{
                    xtype: 'combo',
                    name: 'dataValue',
                    hiddenName: 'modxtalks[commentsPerPage]',
                    fieldLabel: _('modxtalks.comments_per_page'),
                    description: _('modxtalks.comments_per_page_desc') +' ' + _('modxtalks.default_desc'),
                    id: 'commentsPerPage',
                    fields: ['dataValue', 'dataName'],
                    store: [['',_('modxtalks.default')],['10','10 ' + _('modxtalks.comments')],['15','15 ' + _('modxtalks.comments')],
                    ['20','20 ' + _('modxtalks.comments')],['25','25 ' + _('modxtalks.comments')],['30','30 ' + _('modxtalks.comments')],
                    ['35','35 ' + _('modxtalks.comments')],['40','40 ' + _('modxtalks.comments')],['45','45 ' + _('modxtalks.comments')],
                    ['50','50 ' + _('modxtalks.comments')],['100','100 ' + _('modxtalks.comments')],['200','200 ' + _('modxtalks.comments')]],
                    mode: 'local',
                    triggerAction: 'all',
                    value: rec.modxtalks.commentsPerPage || '',
                    selectOnFocus: true,
                    cls:'commentsPerPage',
                    anchor: '100%'
                },{
                    xtype: 'textfield',
                    name: 'modxtalks[moderator]',
                    fieldLabel: _('modxtalks.moderators'),
                    description: _('modxtalks.moderators_desc') + ' ' + _('modxtalks.default_desc'),
                    value: rec.modxtalks.moderator || '',
                    emptyText: _('modxtalks.default'),
                    cls:'moderators',
                    anchor: '100%',
                }]
                },{
                columnWidth: .333,
                items: [{
                    xtype: 'textfield',
                    name: 'modxtalks[commentTpl]',
                    fieldLabel: _('modxtalks.template_comment'),
                    description: _('modxtalks.template_comment_desc') + ' ' + _('modxtalks.default_desc'),
                    value: rec.modxtalks.commentTpl || '',
                    emptyText: _('modxtalks.default'),
                    cls:'commentTpl',
                    anchor: '100%'
                },{
                    xtype: 'textfield',
                    name: 'modxtalks[deletedCommentTpl]',
                    fieldLabel: _('modxtalks.template_deleting_comments'),
                    description: _('modxtalks.template_deleting_comments_desc') + ' ' + _('modxtalks.default_desc'),
                    value: rec.modxtalks.deletedCommentTpl || '',
                    emptyText: _('modxtalks.default'),
                    cls:'commentTpl',
                    anchor: '100%'
                }]
                },{
                columnWidth: .333,
                items: [{
                    xtype: 'textfield',
                    name: 'modxtalks[commentEditFormTpl]',
                    fieldLabel: _('modxtalks.template_editing_comments'),
                    description: _('modxtalks.template_editing_comments_desc') + ' ' + _('modxtalks.default_desc'),
                    value: rec.modxtalks.commentEditFormTpl || '',
                    emptyText: _('modxtalks.default'),
                    cls:'commentTpl',
                    anchor: '100%'
                },{
                    xtype: 'textfield',
                    name: 'modxtalks[commentAuthTpl]',
                    fieldLabel: _('modxtalks.template_authorization_comments'),
                    description: _('modxtalks.template_authorization_comments_desc') + ' ' + _('modxtalks.default_desc'),
                    value: rec.modxtalks.commentAuthTpl || '',
                    emptyText: _('modxtalks.default'),
                    cls:'commentTpl',
                    anchor: '100%'
                }]
            }]
        }]
    })
    


    modxTalks.loadHelpPaneMT = function(b) {
        var url = 'http://modxtalks.artdevue.com/'+ b;
        if (!url) { return false; }
        MODx.helpWindow = new Ext.Window({
            title: _('help')
            ,width: 850
            ,height: 500
            ,resizable: true
            ,maximizable: true
            ,modal: false
            ,layout: 'fit'
            ,html: '<iframe src="' + url + '" width="100%" height="100%" frameborder="0"></iframe>'
        });
        MODx.helpWindow.show(b);
        return true;
    }

    var tokenDelimiter = ':';
    tabpanel.setActiveTab(hashCheck());
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
        tabpanel.setActiveTab(hashCheck());
    }

    tabpanel.addListener('tabchange', function(tabPanel, tab){
        Ext.History.add('settings:' + tab.id);
    });

});

