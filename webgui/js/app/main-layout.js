// Main viewport
function initViewport() {

	// Create viewport
	var viewport = new Ext.Viewport({
		layout: 'border',
		items: [
			{
				// Top bar
				region:'north',
	
				height: 30,
				border: false,
	
				items: [
					{
						// Add toolbar
						xtype: 'toolbar',
						items: [
							{
								text: "Radius Control Panel",
								menu: radiusMenu
							},
							{
								text: "WiSP Control Panel",
								menu: wispMenu
							}
						]
					}
				]
			},
			// Main content
			{
				xtype: 'panel',
				region: 'center',
				autoScroll: true,
				border: true,
				margins:'5 5 5 5'
//				items: [
//					mainWindow
//				]
			},
			{
				id: 'main-statusbar',
				xtype: 'statusbar',
				region: 'south',
				border: true
			}
/*
			{
				region: 'east',
				html: '<img src="resources/custom/images/smarty_icon.gif"></img>',
				margins:'5 5 5 5',
				border: true
			}
*/
		]	
	});
}




