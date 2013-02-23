modxTalks.grid.emailBlock = function(config) {
    config = config || {};
    this.sm = new Ext.grid.CheckboxSelectionModel();
    Ext.applyIf(config,{
        id: 'modxtalks-grid-emailblock'
        ,url: modxTalks.config.connectorUrl
        ,baseParams: { action: 'mgr/email/getlist' }
        ,save_action: 'mgr/email/update'
        ,fields: ['id', 'email', 'date', 'intro', 'publishedon_date', 'publishedon_time', 'actions']
        ,paging: true
        ,autosave: true
        ,remoteSort: true
        ,sm: this.sm
    	,loadingText : '<div class="empty-msg"><h4>'+_('modxtalks.loading')+'</h4></div>'
    	,emptyText : '<div class="empty-msg"><h4>'+_('modxtalks.items_empty_email_msg')+'</h4></div>'
        ,autoExpandColumn: 'date'
        ,columns: [this.sm,{
            hidden: true
            ,hideable: false
            ,dataIndex: 'id'
        },{
            header: '<div class="date-h">' + _('modxtalks.date_create') + '</div>'
            ,dataIndex: 'date'
            ,sortable: true
            ,width: 25
            ,renderer: {fn:this._renderDate,scope:this}
        },{
            header: '<div class="email-h">' + _('modxtalks.email_adress') + '</div>'
            ,dataIndex: 'email'
            ,id: 'email'
    	    ,sortable: true
    	    ,editor: { xtype: 'textfield' }
            ,renderer : {fn:this._renderPageTitle,scope:this}
        },{
            header: '<div class="description-h">' + _('modxtalks.description') + '</div>'
            ,dataIndex: 'intro'
            ,cls: 'intro'
            ,width: 40
            ,editor: { xtype: 'textfield' }
	    ,renderer: function(val) {
                return '<p class="sp_text">'+val+'</p>';
            }
        },{
            header: '<img src="'+modxTalks.config.cssUrl+'../img/mrg/16-Tools.png" alt="'+  _('modxtalks.delet_conversation') +'" class="modxtalks-email-col-header" />'
    	    ,width: 10
    	    ,align: 'center'	    
                ,renderer : {fn:this._renderEmailDelete,scope:this}
            }]
            ,tbar: [{
    		text: _('modxtalks.talks_selected_delete')
    		,iconCls:'icon-delete'
    		,handler: this.deleteEmailSelect
    		,scope: this			
    	    },' ',{
    		text: _('modxtalks.email_block_create')
    		,handler: this.createEmail
    		,scope: this
    		,iconCls:'icon-add'			
	    },'->',{
            xtype: 'textfield'
            ,id: 'modxtalks-search-filteremail'
            ,emptyText: _('modxtalks.search...')
            ,listeners: {
                'change': {fn:this.search,scope:this}
                ,'render': {fn: function(cmp) {
                    new Ext.KeyMap(cmp.getEl(), {
                        key: Ext.EventObject.ENTER
                        ,fn: function() {
                            this.fireEvent('change',this);
                            this.blur();
                            return true;
                        }
                        ,scope: cmp
                    });
                },scope:this}
            }
        }]
    });
    modxTalks.grid.emailBlock.superclass.constructor.call(this,config)
    this._makeTemplatesEmail();
    this.on('rowclick',MODx.fireResourceFormChange); 
    this.on('click', this.onClick, this);
};
Ext.extend(modxTalks.grid.emailBlock,MODx.grid.Grid,{
    search: function(tf,nv,ov) {
        var s = this.getStore();
        s.baseParams.query = tf.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }

    ,createEmail: function(e) {
	gid = 0;
	var w = MODx.load({
	    xtype: 'modxtalks-window-email-create'
	    ,title: _('modxtalks.email_create')
	    ,disable_categories: true
	    ,action: 'mgr/email/create'
	    ,listeners: {
		'success':{fn:function() {
		    Ext.getCmp('modxtalks-grid-emailblock').store.reload();
		},scope:this}
		,'show':{fn:function() {this.center();}}
	    }
	});
	w.show(e.target,function() {
	    Ext.isSafari ? w.setPosition(null,30) : w.center();
	},this);
    }

    ,_makeTemplatesEmail: function() {
        this.tplDateEmail = new Ext.XTemplate('<tpl for=".">'
            +'<div class="talks-grid-date">{publishedon_date}<span class="talks-grid-time">{publishedon_time}</span></div>'
        +'</tpl>',{
			compiled: true
		});
        this.tplPageTitleEmail = new Ext.XTemplate('<tpl for="."><div class="talks-email-column">'
	    +'<h3 class="main-column grey">{email}</h3></div>'
	+'</tpl>',{
			compiled: true
		});
	this.tplEmailDelete = new Ext.XTemplate('<tpl for=".">'
                +'<ul class="actions del">'
                    +'<tpl for="actions">'
                        +'<li><a href="#homeTab:email-blocking"><img class="controlBtn deleteemail" src="' + modxTalks.config.cssUrl +'../img/mrg/24-Trashcan_b.png" ext:qtemail="{text}" /></a></li>'
                    +'</tpl>'
                +'</ul>'
	+'</tpl>',{
			compiled: true
		});
    }
    ,_renderPageTitle:function(v,md,rec) {        
		return this.tplPageTitleEmail.apply(rec.data);
	}
    ,_renderDate:function(v,md,rec) {
		return this.tplDateEmail.apply(rec.data);
	}
    ,_renderEmailDelete:function(v,md,rec) {
		return this.tplEmailDelete.apply(rec.data);
	}

    ,deleteEmail: function(btn,e) {
        MODx.msg.confirm({            
            title: _('modxtalks.email_remove') 
            ,text: _('modxtalks.email_remove_confirm')
            ,url: this.config.url
            ,params: {
                action: 'mgr/email/remove' 
                ,id: this.menu.record.id
            }
            ,listeners: {
                'success':{fn:this.refresh,scope:this}
            }
        });
    }
    ,deleteEmailSelect: function(btn,e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;

        MODx.msg.confirm({
            title: _('modxtalks.email_removes')
            ,text: _('modxtalks.email_removes_confirm')
            ,url: this.config.url
            ,params: {
                action: 'mgr/email/deleteMultiple'
                ,ids: cs
            }
            ,listeners: {
                'success': {fn:function(r) {
                    this.getSelectionModel().clearSelections(true);
                    this.refresh();
                },scope:this}
            }
        });
        return true;
    }
    ,onClick: function(e){
		var t = e.getTarget();
		var elm = t.className.split(' ')[0];
		if(elm == 'controlBtn') {
			var action = t.className.split(' ')[1];
			var record = this.getSelectionModel().getSelected();
                        this.menu.record = record;
			switch (action) {
                            case 'deleteemail':
                                this.deleteEmail();
			    default:
				break;
            }
	}
    }
});
Ext.reg('modxtalks-grid-emailblock',modxTalks.grid.emailBlock); 

modxTalks.window.CreateEmail = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        title: _('modxtalks.email_block_create')
        ,url: modxTalks.config.connectorUrl
        ,baseParams: {
            action: 'mgr/email/create'
        }
        ,fields: [{
            xtype: 'textfield'
            ,fieldLabel: _('modxtalks.email_adress')
            ,name: 'email'
            ,anchor: '100%'
        },{
            xtype: 'textarea'
            ,fieldLabel: _('modxtalks.ip_adress_desc')
            ,name: 'intro'
            ,anchor: '100%'
        }]
    });
    modxTalks.window.CreateEmail.superclass.constructor.call(this,config);    
};
Ext.extend(modxTalks.window.CreateEmail,MODx.Window);
Ext.reg('modxtalks-window-email-create',modxTalks.window.CreateEmail);
