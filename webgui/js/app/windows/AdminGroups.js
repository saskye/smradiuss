

function showAdminGroupWindow() {

	var AdminGroupWindow = new Ext.ux.GenericGridWindow(
		// Window config
		{
			title: "Groups",
			
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
					tooltip:'Add group',
					iconCls:'add',
					handler: function() {
						showAdminGroupEditWindow();
					}
				}, 
				'-', 
				{
					text:'Remove',
					tooltip:'Remove group',
					iconCls:'remove',
					handler: function() {
						var selectedItem = AdminGroupWindow.getComponent('gridpanel').getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminGroupRemoveWindow(AdminGroupWindow,selectedItem.data.ID);
						} else {
							AdminGroupWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No group selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									AdminGroupWindow.getEl().unmask();
								}
							});
						}
					}
				},
				'-',
				{
					text:'Attributes',
					tooltip:'Group attributes',
					iconCls:'logs',
					handler: function() {
						var selectedItem = AdminGroupWindow.getComponent('gridpanel').getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminGroupAttributesWindow(selectedItem.data.ID);
						} else {
							AdminGroupWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No group selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									AdminGroupWindow.getEl().unmask();
								}
							});
						}
					}
				},
				'-',
				{
					text:'Members',
					tooltip:'Group members',
					iconCls:'topups',
					handler: function() {
						var selectedItem = AdminGroupWindow.getComponent('gridpanel').getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminGroupMembersWindow(selectedItem.data.ID);
						} else {
							AdminGroupWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No group selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									AdminGroupWindow.getEl().unmask();
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
					header: "Priority",
					sortable: true,
					dataIndex: 'Priority'
				},
				{
					header: "Disabled",
					sortable: true,
					dataIndex: 'Disabled'
				},
				{
					header: "Comment",
					sortable: true,
					dataIndex: 'Comment'
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
				SOAPModule: 'AdminGroups',
				SOAPFunction: 'getAdminGroups',
				SOAPParams: '__null,__search'
			}
		},
		// Filter config
		{
			filters: [
				{type: 'numeric',  dataIndex: 'ID'},
				{type: 'string',  dataIndex: 'Name'},
				{type: 'numeric',  dataIndex: 'Priority'},
				{type: 'boolean',  dataIndex: 'Disabled'},
				{type: 'string', dataIndex: 'Comment'}
			]
		}
	);

	AdminGroupWindow.show();
}


// Display edit/add form
function showAdminGroupEditWindow(id) {

	var submitAjaxConfig;
	var editMode;


	// We doing an update
	if (id) {
		submitAjaxConfig = {
			ID: id,
			SOAPFunction: 'updateAdminGroup',
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
			SOAPFunction: 'createAdminGroup',
			SOAPParams: 
				'0:AgentID,'+
				'0:GroupName,'+
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
			SOAPModule: 'AdminGroups',
			SOAPFunction: 'getClasses',
			AgentID: 1,
			SOAPParams: '0:AgentID,__search'
		}
	});

	// Create window
	var wispGroupFormWindow = new Ext.ux.GenericFormWindow(
		// Window config
		{
			title: "Group Information",

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
				SOAPModule: 'AdminGroups'
			},
			items: [
				{
					fieldLabel: 'Groupname',
					name: 'Groupname',
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
		wispGroupFormWindow.findById('agent_combobox').on({
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
	wispGroupFormWindow.show();

	if (id) {
		wispGroupFormWindow.getComponent('formpanel').load({
			params: {
				id: id,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'AdminGroups',
				SOAPFunction: 'getAdminGroups',
				SOAPParams: 'id'
			}
		});
	}
}




// Display edit/add form
function showAdminGroupRemoveWindow(parent,id) {
	// Mask parent window
	parent.getEl().mask();

	// Display remove confirm window
	Ext.Msg.show({
		title: "Confirm removal",
		msg: "Are you very sure you wish to remove this group?",
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
						SOAPModule: 'AdminGroups',
						SOAPFunction: 'removeAdminGroup',
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










