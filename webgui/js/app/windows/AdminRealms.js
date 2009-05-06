

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
						showAdminRealmAddWindow();
					}
				}, 
				'-',
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
			autoExpandColumn: 'Service'
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
				{type: 'string',  dataIndex: 'Realmname'},
				{type: 'boolean', dataIndex: 'Disabled'}
			]
		}
	);

	AdminRealmWindow.show();
}


// Display edit/add form
function showAdminRealmEditWindow(id) {

	var submitAjaxConfig;
	var editMode;


	// We doing an update
	if (id) {
		submitAjaxConfig = {
			ID: id,
			SOAPFunction: 'updateAdminRealm',
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
			SOAPFunction: 'createAdminRealm',
			SOAPParams: 
				'0:AgentID,'+
				'0:RealmName,'+
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
			SOAPModule: 'AdminRealms',
			SOAPFunction: 'getClasses',
			AgentID: 1,
			SOAPParams: '0:AgentID,__search'
		}
	});

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
					fieldLabel: 'Realmname',
					name: 'Realmname',
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
		adminRealmFormWindow.findById('agent_combobox').on({
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
	adminRealmFormWindow.show();

	if (id) {
		adminRealmFormWindow.getComponent('formpanel').load({
			params: {
				id: id,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'AdminRealms',
				SOAPFunction: 'getAdminRealm',
				SOAPParams: 'id'
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
						SOAPModule: 'AdminRealms',
						SOAPFunction: 'removeAdminRealm',
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










