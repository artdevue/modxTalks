modxTalks.grid.ipBlock = function (config) {
    config = config || {};
    this.sm = new Ext.grid.CheckboxSelectionModel();
    Ext.applyIf(config, {
        id: 'modxtalks-grid-ipblock'
        , url: modxTalks.config.connectorUrl
        , baseParams: {action: 'mgr/ip/getlist'}
        , save_action: 'mgr/ip/update'
        , fields: ['id', 'ip', 'date', 'intro', 'publishedon_date', 'publishedon_time', 'actions']
        , paging: true
        , autosave: true
        , remoteSort: true
        , sm: this.sm
        , loadingText: '<div class="empty-msg"><h4>' + _('modxtalks.loading') + '</h4></div>'
        , emptyText: '<div class="empty-msg"><h4>' + _('modxtalks.items_empty_ip_msg') + '</h4></div>'
        , autoExpandColumn: 'date'
        , columns: [this.sm, {
            hidden: true
            , hideable: false
            , dataIndex: 'id'
        }, {
            header: _('modxtalks.date_create')
            , dataIndex: 'date'
            , sortable: true
            , width: 25
            , renderer: {fn: this._renderDate, scope: this}
        }, {
            header: _('modxtalks.ip_adress')
            , dataIndex: 'ip'
            , id: 'ip'
            , width: 30
            , sortable: true
            , editor: {xtype: 'textfield'}
            , renderer: {fn: this._renderPageTitle, scope: this}
        }, {
            header: _('modxtalks.description')
            , dataIndex: 'intro'
            , cls: 'intro'
            , editor: {xtype: 'textfield'}
            , renderer: function (val) {
                return '<p class="sp_text">' + val + '</p>';
            }
        }, {
            header: ''
            , width: 10
            , align: 'center'
            , renderer: {fn: this._renderIpDelete, scope: this}
        }]
        , tbar: [{
            text: _('modxtalks.talks_selected_delete')
            , handler: this.deleteIpSelect
            , scope: this
        }, ' ', {
            text: _('modxtalks.ip_block_create')
            , handler: this.createIp
            , scope: this
        }, '->', {
            xtype: 'textfield'
            , id: 'modxtalks-search-filterip'
            , emptyText: _('modxtalks.search...')
            , listeners: {
                'change': {fn: this.search, scope: this}
                , 'render': {
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
    modxTalks.grid.ipBlock.superclass.constructor.call(this, config)
    this._makeTemplates();
    this.on('rowclick', MODx.fireResourceFormChange);
    this.on('click', this.onClick, this);
};
Ext.extend(modxTalks.grid.ipBlock, MODx.grid.Grid, {
    search: function (tf, nv, ov) {
        var s = this.getStore();
        s.baseParams.query = tf.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }

    , createIp: function (e) {
        gid = 0;
        var w = MODx.load({
            xtype: 'modxtalks-window-ip-create'
            , title: _('modxtalks.ip_create')
            , disable_categories: true
            , action: 'mgr/ip/create'
            , listeners: {
                success: {
                    fn: function () {
                        Ext.getCmp('modxtalks-grid-ipblock').store.reload();
                    }, scope: this
                },
                show: {
                    fn: function () {
                        this.center();
                    }
                }
            }
        });

        w.show(e.target, function () {
            Ext.isSafari ? w.setPosition(null, 30) : w.center();
        }, this);
    }

    , _makeTemplates: function () {
        this.tplDate = new Ext.XTemplate('<tpl for=".">'
        + '<div class="talks-grid-date">{publishedon_date}<span class="talks-grid-time">{publishedon_time}</span></div>'
        + '</tpl>', {
            compiled: true
        });
        this.tplPageTitle = new Ext.XTemplate('<tpl for="."><div class="talks-ip-column">'
        + '<h3 class="main-column grey">{ip}</h3></div>'
        + '</tpl>', {
            compiled: true
        });
        this.tplIpDelete = new Ext.XTemplate('<tpl for=".">'
        + '<ul class="actions del">'
        + '<tpl for="actions">'
        + '<li><a href="#homeTab:ip-blocking"><img class="controlBtn deleteip" src="' + modxTalks.config.cssUrl + '../img/mrg/24-Trashcan_b.png" ext:qtip="{text}" /></a></li>'
        + '</tpl>'
        + '</ul>'
        + '</tpl>', {
            compiled: true
        });
    }
    , _renderPageTitle: function (v, md, rec) {
        return this.tplPageTitle.apply(rec.data);
    }
    , _renderDate: function (v, md, rec) {
        return this.tplDate.apply(rec.data);
    }
    , _renderIpDelete: function (v, md, rec) {
        return this.tplIpDelete.apply(rec.data);
    }

    , deleteIp: function (btn, e) {
        MODx.msg.confirm({
            title: _('modxtalks.ip_remove')
            , text: _('modxtalks.ip_remove_confirm')
            , url: this.config.url
            , params: {
                action: 'mgr/ip/remove'
                , id: this.menu.record.id
            }
            , listeners: {
                success: {
                    fn: this.refresh,
                    scope: this
                }
            }
        });
    }
    , deleteIpSelect: function (btn, e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;

        MODx.msg.confirm({
            title: _('modxtalks.ip_removes')
            , text: _('modxtalks.ip_removes_confirm')
            , url: this.config.url
            , params: {
                action: 'mgr/ip/deleteMultiple'
                , ids: cs
            }
            , listeners: {
                success: {
                    fn: function (r) {
                        this.getSelectionModel().clearSelections(true);
                        this.refresh();
                    }, scope: this
                }
            }
        });
        return true;
    }
    , onClick: function (e) {
        var t = e.getTarget();
        var elm = t.className.split(' ')[0];
        if (elm == 'controlBtn') {
            var action = t.className.split(' ')[1];
            this.menu.record = this.getSelectionModel().getSelected();
            switch (action) {
                case 'deleteip':
                    this.deleteIp();
            }
        }
    }
});
Ext.reg('modxtalks-grid-ipblock', modxTalks.grid.ipBlock);

modxTalks.window.CreateIp = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        title: _('modxtalks.ip_block_create')
        , url: modxTalks.config.connectorUrl
        , baseParams: {
            action: 'mgr/ip/create'
        }
        , fields: [{
            xtype: 'textfield'
            , fieldLabel: _('modxtalks.ip_adress')
            , name: 'ip'
            , anchor: '100%'
        }, {
            xtype: 'textarea'
            , fieldLabel: _('modxtalks.ip_adress_desc')
            , name: 'intro'
            , anchor: '100%'
        }]
    });
    modxTalks.window.CreateIp.superclass.constructor.call(this, config);
};
Ext.extend(modxTalks.window.CreateIp, MODx.Window);
Ext.reg('modxtalks-window-ip-create', modxTalks.window.CreateIp);
