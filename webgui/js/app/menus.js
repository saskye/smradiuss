var radiusMenu = new Ext.menu.Menu({
	items: [
	
		{
			text: 'Users',
			handler: function() {
			}
		},

		{
			text: 'Groups',
			handler: function() {
			}
		},

		{
			text: 'Realms',
			handler: function() {
			}
		}
	
	]
});


var wispMenu = new Ext.menu.Menu({
	items: [
	
		{
			text: 'Users',
			handler: function() {
				showWiSPUserWindow();
			}
		},

		{
			text: 'Resellers',
			handler: function() {
				showWiSPResellersWindow();
			}
		},

		{
			text: 'Locations',
			handler: function() {
			}
		}
	
	]
});

