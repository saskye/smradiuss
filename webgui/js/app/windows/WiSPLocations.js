

function showWiSPLocationWindow() {

	var WiSPLocationWindow = new Ext.ux.GenericGridWindow(
		// Window config
		{
			title: "Locations",
			
			width: 600,
			height: 335,
		
			minWidth: 600,
			minHeight: 335,
		},
		// Grid config
		{
			// Inline toolbars
			tbar: [
				{
					text:'Add',
					tooltip:'Add location',
					iconCls:'add',
					handler: function() {
						showWiSPLocationAddEditWindow();
					}
				}, 
				'-',
				{
					text:'Edit',
					tooltip:'Edit location',
					iconCls:'edit',
					handler: function() {
						var selectedItem = WiSPLocationWindow.getComponent('gridpanel').getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showWiSPLocationAddEditWindow(selectedItem.data.ID);
						} else {
							WiSPLocationWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No location selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									WiSPLocationWindow.getEl().unmask();
								}
							});
						}
					}
				},
				'-', 
				{
					text:'Remove',
					tooltip:'Remove location',
					iconCls:'remove',
					handler: function() {
						var selectedItem = WiSPLocationWindow.getComponent('gridpanel').getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showWiSPLocationRemoveWindow(WiSPLocationWindow,selectedItem.data.ID);
						} else {
							WiSPLocationWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No location selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									WiSPLocationWindow.getEl().unmask();
								}
							});
						}
					}
				},
				'-',
				{
					text:'Members',
					tooltip:'List members',
					iconCls:'remove',
					handler: function() {
						var selectedItem = WiSPLocationWindow.getComponent('gridpanel').getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showWiSPLocationMembersWindow(selectedItem.data.ID);
						} else {
							WiSPLocationWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No location selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									WiSPLocationWindow.getEl().unmask();
								}
							});
						}
					}
				}
			],
			// Column model
			colModel: new Ext.grid.ColumnModel([
				{
					id: 'ID',
					header: "ID",
					sortable: true,
					dataIndex: 'ID'
				},
				{
					header: "Name",
					sortable: true,
					dataIndex: 'Name'
				}
			]),
			autoExpandColumn: 'Name'
		},
		// Store config
		{
			baseParams: {
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'WiSPLocations',
				SOAPFunction: 'getWiSPLocations',
				SOAPParams: '__null,__search'
			}
		},
		// Filter config
		{
			filters: [
				{type: 'numeric',  dataIndex: 'ID'},
				{type: 'string',  dataIndex: 'Name'}
			]
		}
	);

	WiSPLocationWindow.show();
}


// Display edit/add form
function showWiSPLocationAddEditWindow(id) {

	var submitAjaxConfig;

	// We doing an update
	if (id) {
		submitAjaxConfig = {
			ID: id,
			SOAPFunction: 'updateWiSPLocation',
			SOAPParams: 
				'0:ID,'+
				'0:Name'
		};

	// We doing an Add
	} else {
		submitAjaxConfig = {
			SOAPFunction: 'createWiSPLocation',
			SOAPParams: 
				'0:Name'
		};
	}
	
	// Create window
	var wispLocationFormWindow = new Ext.ux.GenericFormWindow(
		// Window config
		{
			title: "Location Information",

			width: 310,
			height: 113,

			minWidth: 310,
			minHeight: 113
		},
		// Form panel config
		{
			labelWidth: 85,
			baseParams: {
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'WiSPLocations'
			},
			items: [
				{
					fieldLabel: 'Name',
					name: 'Name',
					allowBlank: false
				},
			],
		},
		// Submit button config
		submitAjaxConfig
	);

	wispLocationFormWindow.show();

	if (id) {
		wispLocationFormWindow.getComponent('formpanel').load({
			params: {
				ID: id,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'WiSPLocations',
				SOAPFunction: 'getWiSPLocation',
				SOAPParams: 'ID'
			}
		});
	}
}


// Display remove form
function showWiSPLocationRemoveWindow(parent,id) {
	// Mask parent window
	parent.getEl().mask();

	// Display remove confirm window
	Ext.Msg.show({
		title: "Confirm removal",
		msg: "Are you very sure you wish to remove this location?",
		icon: Ext.MessageBox.ERROR,
		buttons: Ext.Msg.YESNO,
		modal: false,
		fn: function(buttonId,text) {
			// Check if user clicked on 'yes' button
			if (buttonId == 'yes') {

				// Do ajax request
				uxAjaxRequest(parent,{
					params: {
						id: id,
						SOAPUsername: globalConfig.soap.username,
						SOAPPassword: globalConfig.soap.password,
						SOAPAuthType: globalConfig.soap.authtype,
						SOAPModule: 'WiSPLocations',
						SOAPFunction: 'removeWiSPLocation',
						SOAPParams: 'id'
					}
				});


			// Unmask if user answered no
			} else {
				parent.getEl().unmask();
			}
		}
	});
}

