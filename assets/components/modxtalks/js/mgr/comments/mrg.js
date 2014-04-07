Ext.onReady(function() {
	Ext.Ajax.request({
        url: MODx.config.assets_url+'components/modxtalks/connector.php',
        params: {
            action: 'mgr/unconfirmed/getlist',
            ctx: 'mgr',
            limit: 0,
            start: 0
        },
        success: function(a) {
            var data = Ext.decode(a.responseText);
            if (data.total > 0) {
            	mtUnmenu = document.getElementById('comments_unconfirmed');
            	if (mtUnmenu) {
            		mtUnmenu.innerHTML = '<span>' + data.total + '</span>';
            		mtUnmenu.style.display = 'block';
            	}
            }
        }
    });
});
