modxTalks.page.Home = function (config) {
	config = config || {};
	Ext.applyIf(config, {
		components: [{
			xtype: 'modxtalks-panel-home',
			renderTo: 'modxtalks-panel-home-div'
		}]
	});

	modxTalks.page.Home.superclass.constructor.call(this, config);
};

Ext.extend(modxTalks.page.Home, MODx.Component);
Ext.reg('modxtalks-page-home', modxTalks.page.Home);
