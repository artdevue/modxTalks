MODx.ModxTalksUnconfirmedViewPanel = function (config) {
    config = config || {};
    this._initTemplates();
    Ext.applyIf(config, {
        url: modxTalks.config.connectorUrl
        , fields: ['id', 'ip', 'content', 'time', 'userId', 'username', 'useremail', 'conversationId', 'funny_date',
            'conversationName', 'conversationUrl', 'actions', 'name', 'avatar', 'date']
        , baseParams: {
            action: 'mgr/unconfirmed/getlist'
            , ctx: 'mgr'
            , limit: 5
            , start: 0
        }
        , tpl: this.templates.thumb
        , prepareData: this.formatData.createDelegate(this)
        , overClass: 'x-view-over'
        , selectedClass: 'selected'
        , itemSelector: 'div.wrapper-modxtalks'
        , loadingText: '<div class="empty-msg"><h4>' + _('modxtalks.loading') + '</h4></div>'
        , emptyText: '<div class="empty-msg"><h4>' + _('modxtalks.items_empty_unconfirmed_msg') + '</h4></div>'
    });
    MODx.ModxTalksUnconfirmedViewPanel.superclass.constructor.call(this, config);
    this.on('selectionchange', this.showDetails, this, {buffer: 100});
};
Ext.extend(MODx.ModxTalksUnconfirmedViewPanel, MODx.DataView, {
    templates: {}
    , run: function (p) {
        var v = {};
        Ext.applyIf(v, this.store.baseParams);
        Ext.applyIf(v, p);
        this.store.load({
            params: v
            /* Fix layout after the store's loaded */
            , callback: function (rec, options, success) {
                setTimeout(function () {
                    Ext.getCmp('modx-content').doLayout();
                }, 500);
            }
        });
    }

    , showDetails: function () {
        var selNode = this.getSelectedNodes();
        if (selNode && selNode.length > 0) {
            selNode = selNode[0];
            var data = this.lookup[selNode.id];
            //Show set as cover button if necessary
            var comment = Ext.getCmp('modxtalks-talks-comment').comment;
            if (data) {
                Ext.getCmp('modxtalks-comment-item-details').updateDetail(data);
            }
        } else {
            mtmenu = document.getElementById('comments_unconfirmed');
            if (mtmenu) {
                mtmenu.innerHTML = '';
                mtmenu.style.display = 'none';
            }
        }
    }

    , formatData: function (data) {
        this.lookup['modxtalks-comment-item-' + data.id] = data;
        return data;
    }

    , _initTemplates: function () {
        this.templates.thumb = new Ext.XTemplate('<tpl for=".">'
        + '<div class="wrapper-modxtalks" id="modxtalks-comment-item-{id}">'
        + '<div class="avatar"><img src="{avatar}" alt="" class="avatar"></div>'
        + '<div class="postСontent comment-mt">'
        + '<div class="postheader">'
        + '<div class="info"><h3>{username}</h3>'
        + '<p class="time" ext:qtip="{date}">{funny_date}</p>'
        + '</div>'
        + '<div class="controls">'
        + '<p class="creatip">{ip}</p>'
        + '</div>'
        + '<div class="postBody">'
        + '<a href="{conversationUrl}" ext:qtip="' + _('modxtalks.goto_web') + '" alt="{conversationUrl}" class="link-mt" />{conversationName}</a>'
        + '</div>'
        + '</div>'
        + '</div>'
        + '</div>'
        + '</tpl>'
        + '<div class="clear"></div>', {
            compiled: true
            , checkThumb: function (v) {
                return v;
            }
        });
    }

    , beautify: function () {
        var container = Ext.fly('modxtalks-comment-view' + this.uid);
        var uid = this.uid;
        if (container !== null) {
            if (container.hasClass('loaded')) {
                container.removeClass('loaded');
            }
            // count avatars
            var images = container.select('img');
            var count = images.getCount();
            images.on('load', function (e) {
                count--;
                if (count == 0) {
                    setTimeout(function () {
                        Ext.fly('modxtalks-comment-view' + uid).addClass('loaded');
                    }, 500);
                }
                /* Hide the loading spinner */
                var loader = e.getTarget().parentElement.lastChild;
                Ext.get(loader).fadeOut();
            });
        }
    }
});
Ext.reg('modxtalks-unconfirmed-viewpanel', MODx.ModxTalksUnconfirmedViewPanel);

modxTalks.panel.unConfirmed = function (config) {
    config = config || {};
    Ext.apply(config, {
        id: 'modxtalks-talks-comment'
        , cls: 'main-wrapper modx-template-detail'
        , bodyCssClass: 'body-wrapper'
        , layout: 'column'
        , border: false
        , autoHeight: true
        , tbar: []
        , border: false
        , autoHeight: true
        , items: []
    });
    modxTalks.panel.unConfirmed.superclass.constructor.call(this, config);
    this._loadView();
    this._init();
    this._initDescTpl();
};
Ext.extend(modxTalks.panel.unConfirmed, MODx.Panel, {
    _init: function () {
        this.add({
            items: this.view
            , border: false
            , bbar: new Ext.PagingToolbar({
                pageSize: 5
                , store: this.view.store
                , displayInfo: true
                , autoLoad: true
            })
            , columnWidth: 1
        }, {
            xtype: 'modx-template-panel'
            , id: 'modxtalks-comment-item-details'
            , cls: 'aside-details'
            , width: 330
            , startingText: '<p>' + _('modxtalks.select_comment') + '</p>'
            , markup: this._descTpl()
        });
    }
    , _initDescTpl: function () {
        this.albumDescTpl = new Ext.XTemplate('<tpl for=".">' + _('modxtalks.post') + '</tpl>', {
            compiled: true
        });
    }
    , _loadView: function () {
        this.ident = 'modxtalks-comment-ident';
        this.view = MODx.load({
            id: 'modxtalks-comment-view'
            , xtype: 'modxtalks-unconfirmed-viewpanel'
            , container: this.id
            , uid: this.uid
            , containerScroll: true
            , ident: this.ident
            , border: false
        });
    }
    , _descTpl: function () {
        return '<div class="details">'
            + '<tpl for=".">'
            + '<div class="head-wiev-comment">'
            + '<h3>{conversationName}</h3>'
            + '<a href="{conversationUrl}" ext:qtip="' + _('modxtalks.goto_web') + '" alt="{conversationUrl}" class="link-mt" />{conversationUrl}</a>'
            + '<p class="user-mt">{username}</p>'
            + '<p class="email-mt">{useremail}</p>'
            + '<p class="date-mt">{date}</p>'
            + '</div>'
            + '<div class="spbutmt">'
            + '<ul class="splitbuttons">'
            + '<li class="inline-button ban-comment"><button ext:qtip="' + _('modxtalks.ban_comment') + '" ext:trackMouse=true ext:anchorToTarget=false" onclick="Ext.getCmp(\'modxtalks-talks-comment\').banComment(\'{id}\'); return false;">' + _('modxtalks.ban') + '</button></li>'
            + '<li class="inline-button set-comment"><button ext:qtip="' + _('modxtalks.add_comment') + '" ext:trackMouse=true ext:anchorToTarget=false" onclick="Ext.getCmp(\'modxtalks-talks-comment\').setComment(\'{id}\'); return false;">' + _('modxtalks.add') + '</button></li>'
            + '<li class="inline-button delete"><button ext:qtip="' + _('modxtalks.delete_comment') + '" ext:trackMouse=true ext:anchorToTarget=false" onclick="Ext.getCmp(\'modxtalks-talks-comment\').deleteComment(\'{id}\'); return false;">' + _('modxtalks.delete') + '</button></li>'
            + '</ul>'
            + '</div>'
            + '<div class="content-wiev-comment">{content}</div>'
            + '</tpl>'
            + '</div>';
    }
    , activate: function (rec) {
        if (rec !== undefined) {
            this.comment = rec;
        }
        this.view.store.setBaseParam('comment', this.comment.id);
        this.view.run();
        Ext.getCmp('modxtalks-comment-item-details').reset();
    }
    , banComment: function (id) {
        MODx.msg.confirm({
            title: _('modxtalks.ban')
            , text: _('modxtalks.ban_comment')
            , url: modxTalks.config.connectorUrl
            , params: {
                action: 'mgr/unconfirmed/ban'
                , id: id
                , ctx: 'mgr'
            }
            , listeners: {
                'success': {
                    fn: function (r) {
                        this.activate(r.data);
                    }, scope: this
                }
            }
            , animEl: this.id
        });
    }
    , setComment: function (id) {
        Ext.Ajax.request({
            url: modxTalks.config.connectorUrl
            , params: {
                action: 'mgr/unconfirmed/approve'
                , id: id
                , ctx: 'mgr'
            }
            , success: function (a) {
                Ext.ComponentMgr.all.map['modxtalks-comment-view'].store.reload();
                var data = Ext.decode(a.responseText);
                MODx.msg.status({
                    message: data.message,
                    delay: 5
                });
                Ext.getCmp('modxtalks-comment-item-details').reset();
            }
            , animEl: this.id
        });
    }
    , deleteComment: function (id) {
        MODx.msg.confirm({
            title: _('modxtalks.delete_comment')
            , text: _('modxtalks.delete_comment_desc')
            , url: modxTalks.config.connectorUrl
            , params: {
                action: 'mgr/unconfirmed/remove'
                , id: id
                , ctx: 'mgr'
            }
            , listeners: {
                'success': {
                    fn: function (r) {
                        this.activate(r.data);
                    }, scope: this
                }
            }
            , animEl: this.id
        });
    }
});
Ext.reg('modxtalks-panel-unconfirmed', modxTalks.panel.unConfirmed);
