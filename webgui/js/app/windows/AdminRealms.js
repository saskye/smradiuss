

function showAdminRealmWindow() {

	var AdminRealmWindow = new Ext.ux.GenericGridWindow(
		// Window config
		{
			title: "Realms",
			
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
					tooltip:'Add realm',
					iconCls:'add',
					handler: function() {
						showAdminRealmAddEditWindow();
					}
				}, 
				'-',
				{
					text:'Edit',
					tooltip:'Edit realm',
					iconCls:'edit',
					handler: function() {
						var selectedItem = AdminRealmWindow.getComponent('gridpanel').getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminRealmAddEditWindow(selectedItem.data.ID);
						} else {
							AdminRealmWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No realm selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									AdminRealmWindow.getEl().unmask();
								}
							});
						}
					}
				},
				{
					text:'Remove',
					tooltip:'Remove realm',
					iconCls:'remove',
					handler: function() {
						var selectedItem = AdminRealmWindow.getComponent('gridpanel').getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminRealmRemoveWindow(AdminRealmWindow,selectedItem.data.ID);
						} else {
							AdminRealmWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No realm selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									AdminRealmWindow.getEl().unmask();
								}
							});
						}
					}
				},
				'-',
				{
					text:'Members',
					tooltip:'Realm members',
					iconCls:'logs',
					handler: function() {
						var selectedItem = AdminRealmWindow.getComponent('gridpanel').getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminRealmMembersWindow(selectedItem.data.ID);
						} else {
							AdminRealmWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No realm selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									AdminRealmWindow.getEl().unmask();
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
				},
				{
					header: "Disabled",
					sortable: false,
					dataIndex: 'Disabled'
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
				SOAPModule: 'AdminRealms',
				SOAPFunction: 'getAdminRealms',
				SOAPParams: '__null,__search'
			}
		},
		// Filter config
		{
			filters: [
				{type: 'numeric',  dataIndex: 'ID'},
				{type: 'string',  dataIndex: 'Name'},
				{type: 'boolean', dataIndex: 'Disabled'}
			]
		}
	);

	AdminRealmWindow.show();
}


// Display edit/add form
function showAdminRealmAddEditWindow(id) {

	var submitAjaxConfig;


	// We doing an update
	if (id) {
		submitAjaxConfig = {
			ID: id,
			SOAPFunction: 'updateAdminRealm',
			SOAPParams: 
				'0:ID,'+
				'0:Name'
		};

	// We doing an Add
	} else {
		submitAjaxConfig = {
			SOAPFunction: 'createAdminRealm',
			SOAPParams: 
				'0:Name'
		};
	}

	// Create window
	var adminRealmFormWindow = new Ext.ux.GenericFormWindow(
		// Window config
		{
			title: "Realm Information",

			width: 475,
			height: 260,

			minWidth: 475,
			minHeight: 260
		},
		// Form panel config
		{
			labelWidth: 85,
			baseParams: {
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'AdminRealms'
			},
			items: [
				{
					fieldLabel: 'Name',
					name: 'Name',
					vtype: 'usernamePart',
					maskRe: usernamePartRe,
					allowBlank: false
				},
			],
		},
		// Submit button config
		submitAjaxConfig
	);

	adminRealmFormWindow.show();

	if (id) {
		adminRealmFormWindow.getComponent('formpanel').load({
			params: {
				ID: id,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'AdminRealms',
				SOAPFunction: 'getAdminRealm',
				SOAPParams: 'ID'
			}
		});
	}
}




// Display edit/add form
function showAdminRealmRemoveWindow(parent,id) {
	// Mask parent window
	parent.getEl().mask();

	// Display remove confirm window
	Ext.Msg.show({
		title: "Confirm removal",
		msg: "Are you very sure you wish to remove this realm?",
		icon: Ext.MessageBox.ERROR,
		buttons: Ext.Msg.YESNO,
		modal: false,
		fn: function(buttonId,text) {
			// Check if user clicked on 'yes' button
			if (buttonId == 'yes') {

				// Do ajax request
				uxAjaxRequest(parent,{
					params: {
						ID: id,
						SOAPUsername: globalConfig.soap.username,
						SOAPPassword: globalConfig.soap.password,
						SOAPAuthType: globalConfig.soap.authtype,
						SOAPModule: 'AdminRealms',
						SOAPFunction: 'removeAdminRealm',
						SOAPParams: 'ID'
					}
				});


			// Unmask if user answered no
			} else {
				parent.getEl().unmask();
			}
		}
	});
}










