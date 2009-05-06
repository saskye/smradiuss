

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
						showAdminUserEditWindow();
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
							showAdminUserEditWindow(selectedItem.data.ID);
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
					sortable: false,
					dataIndex: 'Disabled'
				}
			]),
			autoExpandColumn: 'Service'
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
function showAdminUserEditWindow(id) {

	var submitAjaxConfig;
	var editMode;


	// We doing an update
	if (id) {
		submitAjaxConfig = {
			ID: id,
			SOAPFunction: 'updateAdminUser',
			SOAPParams: 
				'0:ID,'+
				'0:UsageCap,'+
				'0:AgentRef,'+
				'0:AgentDisabled:boolean'
		};
		editMode = true;

	// We doing an Add
	} else {
		submitAjaxConfig = {
			SOAPFunction: 'createAdminUser',
			SOAPParams: 
				'0:AgentID,'+
				'0:UserName,'+
				'0:UsageCap,'+
				'0:AgentRef,'+
				'0:AgentDisabled:boolean'
		};
		editMode = false;
	}
	
	// Service store
	var serviceStore = new Ext.ux.JsonStore({
		ID: id,
		sortInfo: { field: "Name", direction: "ASC" },
		baseParams: {
			SOAPUsername: globalConfig.soap.username,
			SOAPPassword: globalConfig.soap.password,
			SOAPAuthType: globalConfig.soap.authtype,
			SOAPModule: 'AdminUsers',
			SOAPFunction: 'getClasses',
			AgentID: 1,
			SOAPParams: '0:AgentID,__search'
		}
	});

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
					
					disabled: editMode
				},

				{
					xtype: 'combo',

					// We use an ID so we can get the box later
					id: 'agent_combobox',

					fieldLabel: 'Agent',
					name: 'Agent',
					allowBlank: false,
					width: 225,

					store: new Ext.ux.JsonStore({
						ID: id,
						sortInfo: { field: "Name", direction: "ASC" },
						baseParams: {
							SOAPUsername: globalConfig.soap.username,
							SOAPPassword: globalConfig.soap.password,
							SOAPAuthType: globalConfig.soap.authtype,
							SOAPModule: 'Agents',
							SOAPFunction: 'getAgents',
							SOAPParams: '__search'
						}
					}),
					displayField: 'Name',
					valueField: 'ID',
					hiddenName: 'AgentID',

					forceSelection: false,
					triggerAction: 'all',
					editable: false,

					disabled: editMode
				},

				{
					xtype: 'combo',

					// We use an ID so we can get the box later
					id: 'service_combobox',

					fieldLabel: 'Service',
					name: 'Service',
					allowBlank: false,
					width: 340,

					store: serviceStore,

					displayField: 'Service',
					valueField: 'ID',
					hiddenName: 'ClassID',

					forceSelection: false,
					triggerAction: 'all',
					editable: false,

					disabled: true
				},

				{
					fieldLabel: 'Usage Cap',
					name: 'UsageCap',
				},

				{
					fieldLabel: 'Agent Ref',
					name: 'AgentRef'
				},

				{
					xtype: 'checkbox',
					fieldLabel: 'Disabled',
					name: 'AgentDisabled'
				}/*,
				{
					xtype: 'tabpanel',
					plain: 'true',
					deferredRender: false, // Load all panels!
					activeTab: 0,
					height: 100,
					defaults: {
						layout: 'form',
						bodyStyle: 'padding: 10px;'
					},
					
					items: [
						{
							title: 'Policy Settings',
							layout: 'form',
							defaultType: 'textfield',
							items: [
								{
									fieldLabel: 'Transport Policy',
									name: 'Policy',
									vtype: 'number',
									value: '1'
								}
							]
						}
					]
				}*/
			],
		},
		// Submit button config
		submitAjaxConfig
	);

	// Events
	if (!id) {
		adminUserFormWindow.findById('agent_combobox').on({
			select: {
				fn: function() {
					var tb = this.ownerCt.findById('service_combobox');

					if (this.getValue()) {
						tb.reset();
						serviceStore.baseParams.AgentID = this.getValue();
						serviceStore.reload();
						tb.enable();
					} else {
						tb.reset();
						tb.disable();
					}
				}
			},
		});
	}
	adminUserFormWindow.show();

	if (id) {
		adminUserFormWindow.getComponent('formpanel').load({
			params: {
				id: id,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'AdminUsers',
				SOAPFunction: 'getAdminUser',
				SOAPParams: 'id'
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
						id: id,
						SOAPUsername: globalConfig.soap.username,
						SOAPPassword: globalConfig.soap.password,
						SOAPAuthType: globalConfig.soap.authtype,
						SOAPModule: 'AdminUsers',
						SOAPFunction: 'removeAdminUser',
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










