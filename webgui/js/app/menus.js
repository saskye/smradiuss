var radiusMenu = new Ext.menu.Menu({
	items: [
	
		{
			text: 'Users',
			handler: function() {
				showAdminUserWindow();
			}
		},

		{
			text: 'Groups',
			handler: function() {
				showAdminGroupWindow();
			}
		},

		{
			text: 'Realms',
			handler: function() {
				showAdminRealmWindow();
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

//		{
//			text: 'Resellers',
//			handler: function() {
//				showWiSPResellersWindow();
//			}
//		},

		{
			text: 'Locations',
			handler: function() {
				showWiSPLocationWindow();
			}
		}
	
	]
});

