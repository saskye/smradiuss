

function showAdminUserWindow() {

	var AdminUserWindow = new Ext.ux.GenericGridWindow(
		// Window config
		{
			title: "Users",
			
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
					tooltip:'Add user',
					iconCls:'add',
					handler: function() {
						showAdminUserAddEditWindow();
					}
				}, 
				'-', 
				{
					text:'Edit',
					tooltip:'Edit user',
					iconCls:'option',
					handler: function() {
						var selectedItem = AdminUserWindow.getComponent('gridpanel').getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminUserAddEditWindow(selectedItem.data.ID);
						} else {
							AdminUserWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No user selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									AdminUserWindow.getEl().unmask();
								}
							});
						}
					}
				},
				'-',
				{
					text:'Remove',
					tooltip:'Remove user',
					iconCls:'remove',
					handler: function() {
						var selectedItem = AdminUserWindow.getComponent('gridpanel').getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminUserRemoveWindow(AdminUserWindow,selectedItem.data.ID);
						} else {
							AdminUserWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No user selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									AdminUserWindow.getEl().unmask();
								}
							});
						}
					}
				},
				'-',
				{
					text:'Logs',
					tooltip:'User logs',
					iconCls:'logs',
					handler: function() {
						var selectedItem = AdminUserWindow.getComponent('gridpanel').getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminUserLogsWindow(selectedItem.data.ID);
						} else {
							AdminUserWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No user selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									AdminUserWindow.getEl().unmask();
								}
							});
						}
					}
				},
				'-',
				{
					text:'Groups',
					tooltip:'User groups',
					iconCls:'topups',
					handler: function() {
						var selectedItem = AdminUserWindow.getComponent('gridpanel').getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminUserGroupsWindow(selectedItem.data.ID);
						} else {
							AdminUserWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No user selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									AdminUserWindow.getEl().unmask();
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
					header: "Username",
					sortable: true,
					dataIndex: 'Username'
				},
				{
					header: "Disabled",
					sortable: true,
					dataIndex: 'Disabled'
				}
			]),
			autoExpandColumn: 'Username'
		},
		// Store config
		{
			baseParams: {
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'AdminUsers',
				SOAPFunction: 'getAdminUsers',
				SOAPParams: '__null,__search'
			}
		},
		// Filter config
		{
			filters: [
				{type: 'numeric',  dataIndex: 'ID'},
				{type: 'string',  dataIndex: 'Username'},
				{type: 'boolean', dataIndex: 'Disabled'}
			]
		}
	);

	AdminUserWindow.show();
}


// Display edit/add form
function showAdminUserAddEditWindow(id) {

	var submitAjaxConfig;

	// We doing an update
	if (id) {
		submitAjaxConfig = {
			ID: id,
			SOAPFunction: 'updateAdminUser',
			SOAPParams: 
				'0:ID,'+
				'0:Username'
		};

	// We doing an Add
	} else {
		submitAjaxConfig = {
			SOAPFunction: 'createAdminUser',
			SOAPParams: 
				'0:Username'
		};
	}

	// Create window
	var adminUserFormWindow = new Ext.ux.GenericFormWindow(
		// Window config
		{
			title: "User Information",

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
				SOAPModule: 'AdminUsers'
			},
			items: [
				{
					fieldLabel: 'Username',
					name: 'Username',
					vtype: 'usernamePart',
					maskRe: usernamePartRe,
					allowBlank: false,
				},
			],
		},
		// Submit button config
		submitAjaxConfig
	);

	adminUserFormWindow.show();

	if (id) {
		adminUserFormWindow.getComponent('formpanel').load({
			params: {
				ID: id,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'AdminUsers',
				SOAPFunction: 'getAdminUser',
				SOAPParams: 'ID'
			}
		});
	}
}




// Display edit/add form
function showAdminUserRemoveWindow(parent,id) {
	// Mask parent window
	parent.getEl().mask();

	// Display remove confirm window
	Ext.Msg.show({
		title: "Confirm removal",
		msg: "Are you very sure you wish to remove this user?",
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
						SOAPModule: 'AdminUsers',
						SOAPFunction: 'removeAdminUser',
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










