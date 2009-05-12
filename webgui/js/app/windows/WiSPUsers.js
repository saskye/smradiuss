

function showWiSPUserWindow() {

	var WiSPUserWindow = new Ext.ux.GenericGridWindow(
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
						showWiSPUserEditWindow();
					}
				}, 
				'-', 
				{
					text:'Edit',
					tooltip:'Edit user',
					iconCls:'option',
					handler: function() {
						var selectedItem = WiSPUserWindow.getComponent('gridpanel').getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showWiSPUserEditWindow(selectedItem.data.ID);
						} else {
							WiSPUserWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No user selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									WiSPUserWindow.getEl().unmask();
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
						var selectedItem = WiSPUserWindow.getComponent('gridpanel').getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showWiSPUserRemoveWindow(WiSPUserWindow,selectedItem.data.ID);
						} else {
							WiSPUserWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No user selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									WiSPUserWindow.getEl().unmask();
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
						var selectedItem = WiSPUserWindow.getComponent('gridpanel').getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showWiSPUserLogsWindow(selectedItem.data.ID);
						} else {
							WiSPUserWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No user selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									WiSPUserWindow.getEl().unmask();
								}
							});
						}
					}
				},
				'-',
				{
					text:'Topups',
					tooltip:'User topups',
					iconCls:'topups',
					handler: function() {
						var selectedItem = WiSPUserWindow.getComponent('gridpanel').getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showWiSPUserTopupsWindow(selectedItem.data.ID);
						} else {
							WiSPUserWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No user selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									WiSPUserWindow.getEl().unmask();
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
					header: "UserID",
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
				},
				{
					header: "First Name",
					sortable: true,
					dataIndex: 'Firstname'
				},
				{
					header: "Last Name",
					sortable: true,
					dataIndex: 'Lastname'
				},
				{
					header: "Email",
					sortable: true,
					dataIndex: 'Email'
				},
				{
					header: "Phone",
					sortable: true,
					dataIndex: 'Phone'
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
				SOAPModule: 'WiSPUsers',
				SOAPFunction: 'getWiSPUsers',
				SOAPParams: '__null,__search'
			}
		},
		// Filter config
		{
			filters: [
				{type: 'numeric',  dataIndex: 'ID'},
				{type: 'string',  dataIndex: 'Username'},
				{type: 'boolean',  dataIndex: 'Disabled'},
				{type: 'string',  dataIndex: 'Firstname'},
				{type: 'string',  dataIndex: 'Lastname'},
				{type: 'string',  dataIndex: 'Email'},
				{type: 'string',  dataIndex: 'Phone'}
			]
		}
	);

	WiSPUserWindow.show();
}


// Display edit/add form
function showWiSPUserEditWindow(id) {

	var submitAjaxConfig;
	var editMode;


	// We doing an update
	if (id) {
		submitAjaxConfig = {
			ID: id,
			SOAPFunction: 'updateWiSPUser',
			SOAPParams: 
				'0:ID,'+
				'0:Username'
		};

	// We doing an Add
	} else {
		submitAjaxConfig = {
			SOAPFunction: 'createWiSPUser',
			SOAPParams: 
				'0:Username'
		};
	}

	// Create window
	var wispUserFormWindow = new Ext.ux.GenericFormWindow(
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
				SOAPModule: 'WiSPUsers'
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
	wispUserFormWindow.show();

	if (id) {
		wispUserFormWindow.getComponent('formpanel').load({
			params: {
				ID: id,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'WiSPUsers',
				SOAPFunction: 'getWiSPUser',
				SOAPParams: 'ID'
			}
		});
	}
}




// Display edit/add form
function showWiSPUserRemoveWindow(parent,id) {
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
						SOAPModule: 'WiSPUsers',
						SOAPFunction: 'removeWiSPUser',
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










