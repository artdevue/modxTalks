modxTalks.grid.posts = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        id: 'modxtalks-grid-posts'
        , url: modxTalks.config.connectorUrl
        , baseParams: {action: 'mgr/conversation/getlist'}
        , fields: ['id', 'conversation', 'total', 'deleted', 'unconfirmed', 'link', 'title']
        , paging: true
        , remoteSort: true
        , loadingText: '<div class="empty-msg"><h4>' + _('modxtalks.loading') + '</h4></div>'
        , emptyText: '<div class="empty-msg"><h4>' + _('modxtalks.items_empty_conversation_msg') + '</h4></div>'
        , autoExpandColumn: 'id'
        , columns: [{
            header: _('id')
            , dataIndex: 'id'
            , hidden: false
            , width: 10
            , sortable: true
        }, {
            header: _('modxtalks.conversations')
            , dataIndex: 'conversation'
            , sortable: true
            , cls: 'conver'
            , renderer: function (v, md, rec) {
                var link = rec.data.link != 0 ? '<a class="linkconv" target="_blank" href="' + rec.data.link + '">' + rec.data.link + '</a>' : '<p class="linkconv">' + _('modxtalks.resources_no') + '</p>';
                return '<div class="talks-ip-column"><h3>' + rec.data.title + '</h3>' + v + '</div>' + link;
            }
        }, {
            header: _('modxtalks.comments_totals')
            , dataIndex: 'total'
            , sortable: false
            , width: 40
            , align: 'center'
        }, {
            header: _('modxtalks.comments_delete')
            , dataIndex: 'deleted'
            , sortable: false
            , width: 40
            , align: 'center'
        }, {
            header: _('modxtalks.comments_unconfirmed')
            , dataIndex: 'unconfirmed'
            , sortable: false
            , width: 40
            , align: 'center'
        }, {
            header: ''
            , width: 20
            , align: 'center'
            , renderer: function (v, md, rec) {
                return '<a onclick="return false" href="#"><img class="controlBtn deleteconversations" src="' + modxTalks.config.cssUrl + '../img/mrg/24-Trashcan_b.png" ext:qtip="' + _('modxtalks.delete_conversation') + '" /></a>'
            }
        }]
        , tbar: ['->', {
            xtype: 'textfield'
            , id: 'modxtalks-search-filter'
            , emptyText: _('modxtalks.search...')
            , width: 200
            , listeners: {
                change: {fn: this.search, scope: this}
                , render: {
                    fn: function (cmp) {
                        new Ext.KeyMap(cmp.getEl(), {
                            key: Ext.EventObject.ENTER
                            , fn: function () {
                                this.fireEvent('change', this);
                                this.blur();
                                return true;
                            }
                            , scope: cmp
                        });
                    }, scope: this
                }
            }
        }]
    });
    modxTalks.grid.posts.superclass.constructor.call(this, config);
    this.on('rowclick', MODx.fireResourceFormChange);
    this.on('click', this.onClick, this);
};
Ext.extend(modxTalks.grid.posts, MODx.grid.Grid, {
    search: function (tf, nv, ov) {
        var s = this.getStore();
        s.baseParams.query = tf.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }

    , deleteСonversations: function (btn, e) {
        MODx.msg.confirm({
            title: _('modxtalks.delete_conversation')
            , text: _('modxtalks.delete_conversation_desc')
            , url: this.config.url
            , params: {
                action: 'mgr/conversation/remove'
                , id: this.menu.record.id
            }
            , listeners: {
                success: {fn: this.refresh, scope: this}
            }
        });
    }

    , onClick: function (e) {
        var t = e.getTarget();
        var elm = t.className.split(' ')[0];
        if (elm == 'controlBtn') {
            var action = t.className.split(' ')[1];
            this.menu.record = this.getSelectionModel().getSelected();
            switch (action) {
                case 'deleteconversations':
                    this.deleteСonversations();
            }
        }
    }
});
Ext.reg('modxtalks-grid-posts', modxTalks.grid.posts);
