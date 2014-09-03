modxTalks.grid.posts = function (config) {
	config = config || {};
	Ext.applyIf(config, {
		id: 'modxtalks-grid-posts',
		url: modxTalks.config.connectorUrl,
		baseParams: {
			action: 'mgr/conversations/getlist'
		},
		save_action: 'mgr/conversations/updateFromGrid',
		fields: ['id', 'title', 'conversation', 'total', 'deleted', 'unconfirmed', 'link'],
		paging: true,
		autosave: true,
		remoteSort: true,
		loadingText: '<div class="empty-msg"><h4>' + _('modxtalks.loading') + '</h4></div>',
		emptyText: '<div class="empty-msg"><h4>' + _('modxtalks.items_empty_conversation_msg') + '</h4></div>',
		autoExpandColumn: 'id',
		columns: [{
			dataIndex: 'id',
			hidden: true
		},{
			header: '<div class="conversations-h">' + _('modxtalks.conversations') + '</div>',
			dataIndex: 'conversation',
			sortable: true,
			cls: 'conver',
			renderer: function (v, md, rec) {
				linrec = rec.data.link != 0 ? '<a class="linkconv" target="_blank" href="' + rec.data.link + '">' + rec.data.link + '</a>' : '<p class="linkconv">' + _('modxtalks.resources_no') + '</p>';
				return '<div class="talks-ip-column"><h3 class="main-column grey">' + rec.data.title + ' <small>(' + v + ')</small></h3></div>' + linrec;
			}
		},{
			header: '<div class="total-h">' + _('modxtalks.comments_totals') + '</div>',
			dataIndex: 'total',
			sortable: true,
			width: 40,
			align: 'center',
			renderer: function (val) {
				return '<div class="wi_val">' + val + '</div>';
			}
		},{
			header: '<div class="delete-h">' + _('modxtalks.comments_delete') + '</div>',
			dataIndex: 'deleted',
			sortable: true,
			width: 40,
			align: 'center',
			renderer: function (val) {
				return '<div class="wi_val">' + val + '</div>';
			}
		},{
			header: '<div class="unconfirmed-h">' + _('modxtalks.comments_unconfirmed') + '</div>',
			dataIndex: 'unconfirmed',
			sortable: true,
			width: 40,
			align: 'center',
			renderer: function (val) {
				return '<div class="wi_val">' + val + '</div>';
			}
		},{
			header: '<img src="' + modxTalks.config.cssUrl + '../img/mgr/16-Tools.png" alt="' + _('modxtalks.delet_conversation') + '" class="modxtalks-ip-col-header" />',
			width: 15,
			align: 'center',
			renderer: function (v, md, rec) {
				return '<ul class="actions del">'
				+ '<li><a href="#homeTab:home"><img class="controlBtn deleteconversations" src="' + modxTalks.config.cssUrl + '../img/mgr/24-Trashcan_b.png"'
				+ ' ext:qtip="' + _('modxtalks.delet_conversation') + '" /></a></li>'
				+ '</ul>'
			}
		}],
		tbar: ['->', {
			xtype: 'textfield',
			id: 'modxtalks-search-filter',
			emptyText: _('modxtalks.search...'),
			listeners: {
				change: {
					fn: this.search,
					scope: this
				},
				render: {
					fn: function (cmp) {
						new Ext.KeyMap(cmp.getEl(), {
							key: Ext.EventObject.ENTER,
							fn: function () {
								this.fireEvent('change', this);
								this.blur();
								return true;
							}, scope: cmp
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
	},
	deleteСonversations: function (btn, e) {
		MODx.msg.confirm({
			title: _('modxtalks.delet_conversation'),
			text: _('modxtalks.delet_conversation_desc'),
			url: this.config.url,
			params: {
				action: 'mgr/conversation/remove',
				id: this.menu.record.id
			},
			listeners: {
				success: {
					fn: this.refresh,
					scope: this
				}
			}
		});
	},
	onClick: function (e) {
		var t = e.getTarget();
		var elm = t.className.split(' ')[0];
		if (elm == 'controlBtn') {
			var action = t.className.split(' ')[1];
			this.menu.record = this.getSelectionModel().getSelected();
			switch (action) {
				case 'deleteconversations':
					this.deleteСonversations();
					break;
				default:
					break;
			}
		}
	}
});
Ext.reg('modxtalks-grid-posts', modxTalks.grid.posts);
