

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
				'0:Username,'+
				'0:Password,'+
				'0:Firstname,'+
				'0:Lastname,'+
				'0:Phone,'+
				'0:Email,'+
				'0:MACAddress,'+
				'0:IPAddress,'+
				'0:Datalimit,'+
				'0:Uptimelimit'
		};

	// We doing an Add
	} else {
		submitAjaxConfig = {
			SOAPFunction: 'createWiSPUser',
			SOAPParams: 
				'0:Username,'+
				'0:Password,'+
				'0:Firstname,'+
				'0:Lastname,'+
				'0:Phone,'+
				'0:Email,'+
				'0:MACAddress,'+
				'0:IPAddress,'+
				'0:Datalimit,'+
				'0:Uptimelimit'
		};
	}

	// Create window
	var wispUserFormWindow = new Ext.ux.GenericFormWindow(
		// Window config
		{
			title: "User Information",

			width: 475,
			height: 340,

			minWidth: 475,
			minHeight: 340
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
				{
					fieldLabel: 'Password',
					name: 'Password',
					vtype: 'usernamePart',
					maskRe: usernamePartRe,
					allowBlank: false,
				},
				{
					xtype: 'tabpanel',
					plain: 'true',
					deferredRender: false, // Load all panels!
					activeTab: 0,
					height: 200,
					defaults: {
						layout: 'form',
						bodyStyle: 'padding: 10px;'
					},
					
					items: [
						{
							title: 'Personal',
							layout: 'form',
							defaultType: 'textfield',
							items: [
								{
									fieldLabel: 'First Name',
									name: 'Firstname',
									vtype: 'usernamePart',
									allowBlank: true
								},
								{
									fieldLabel: 'Last Name',
									name: 'Lastname',
									vtype: 'usernamePart',
									allowBlank: true
								},
								{
									fieldLabel: 'Phone',
									name: 'Phone',
									vtype: 'number',
									allowBlank: true
								},
								{
									fieldLabel: 'Email',
									name: 'Email',
									allowBlank: true
								}
							]
						},
						{
							title: 'Attributes',
							layout: 'form',
							defaultType: 'textfield',
							items: [
								{
									xtype: 'combo',
									//id: 'combo',
									fieldLabel: 'Name',
									name: 'Name',
									allowBlank: false,
									width: 160,

									store: new Ext.ux.JsonStore({
										sortInfo: { field: "Name", direction: "ASC" },
										baseParams: {
											SOAPUsername: globalConfig.soap.username,
											SOAPPassword: globalConfig.soap.password,
											SOAPAuthType: globalConfig.soap.authtype,
											SOAPModule: 'WiSPUsers',
											SOAPFunction: 'getWiSPUserAttributeNames',
											SOAPParams: '__null,__search'
										}
									}),
									displayField: 'Name',
									valueField: 'Name',
									hiddenName: 'Name',
									forceSelection: true,
									triggerAction: 'all',
									editable: false
								},
								{
									xtype: 'combo',
									//id: 'combo',
									fieldLabel: 'Value',
									name: 'Value',
									allowBlank: false,
									width: 160,

									store: new Ext.ux.JsonStore({
										sortInfo: { field: "Value", direction: "ASC" },
										baseParams: {
											SOAPUsername: globalConfig.soap.username,
											SOAPPassword: globalConfig.soap.password,
											SOAPAuthType: globalConfig.soap.authtype,
											SOAPModule: 'WiSPUsers',
											SOAPFunction: 'getWiSPUserAttributeValues',
											SOAPParams: '__null,__search'
										}
									}),
									displayField: 'Value',
									valueField: 'Value',
									hiddenName: 'Value',
									forceSelection: true,
									triggerAction: 'all',
									editable: false
								},
								/*{
									xtype: 'combo',
									//id: 'combo',
									fieldLabel: 'Group',
									name: 'Group',
									allowBlank: true,
									width: 140,

									store: new Ext.ux.JsonStore({
										sortInfo: { field: "Name", direction: "ASC" },
										baseParams: {
											SOAPUsername: globalConfig.soap.username,
											SOAPPassword: globalConfig.soap.password,
											SOAPAuthType: globalConfig.soap.authtype,
											SOAPModule: 'AdminUserGroups',
											SOAPFunction: 'getAdminGroups',
											SOAPParams: '__null,__search'
										}
									}),
									displayField: 'Name',
									valueField: 'ID',
									hiddenName: 'GroupID',
									forceSelection: false,
									triggerAction: 'all',
									editable: false
								},*/
								/*{
									xtype: 'combo',
									//id: 'combo',
									fieldLabel: 'Location',
									name: 'Location',
									allowBlank: true,
									width: 160,

									store: new Ext.ux.JsonStore({
										sortInfo: { field: "Name", direction: "ASC" },
										baseParams: {
											SOAPUsername: globalConfig.soap.username,
											SOAPPassword: globalConfig.soap.password,
											SOAPAuthType: globalConfig.soap.authtype,
											SOAPModule: 'AdminUserGroups',
											SOAPFunction: 'getWiSPLocations',
											SOAPParams: '__null,__search'
										}
									}),
									displayField: 'Name',
									valueField: 'ID',
									hiddenName: 'LocationID',
									forceSelection: false,
									triggerAction: 'all',
									editable: false
								},*/
								/*{
									fieldLabel: 'MAC Address',
									name: 'MACAddress',
									allowBlank: true
								},
								{
									fieldLabel: 'IP Address',
									name: 'IPAddress',
									allowBlank: true
								},
								{
									fieldLabel: 'Data Limit',
									name: 'Datalimit',
									vtype: 'number',
									allowBlank: true
								},
								{
									fieldLabel: 'Uptime Limit',
									name: 'Uptimelimit',
									vtype: 'number',
									allowBlank: true
								}*/
							]
						},
					]
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










