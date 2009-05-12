

function showAdminUserAttributesWindow(id) {

	var AdminUserAttributesWindow = new Ext.ux.GenericGridWindow(
		// Window config
		{
			title: "Attributes",
			
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
					tooltip:'Add attribute',
					iconCls:'add',
					handler: function() {
						showAdminUserAttributeAddEditWindow();
					}
				}, 
				'-', 
				{
					text:'Edit',
					tooltip:'Edit attribute',
					iconCls:'edit',
					handler: function() {
						var selectedItem = AdminUserAttributesWindow.getComponent('gridpanel').getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminUserAttributeAddEditWindow(selectedItem.data.ID);
						} else {
							AdminUserAttributesWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No attribute selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									AdminUserAttributesWindow.getEl().unmask();
								}
							});
						}
					}
				},
				'-', 
				{
					text:'Remove',
					tooltip:'Remove attribute',
					iconCls:'remove',
					handler: function() {
						var selectedItem = AdminUserAttributesWindow.getComponent('gridpanel').getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminUserAttributeRemoveWindow(AdminUserAttributesWindow,selectedItem.data.ID);
						} else {
							AdminUserAttributesWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No attribute selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									AdminUserAttributesWindow.getEl().unmask();
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
					header: "Operator",
					sortable: true,
					dataIndex: 'Operator'
				},
				{
					header: "Value",
					sortable: true,
					dataIndex: 'Value'
				},
				{
					header: "Disabled",
					sortable: true,
					dataIndex: 'Disabled'
				}
			]),
			autoExpandColumn: 'Name'
		},
		// Store config
		{
			baseParams: {
				ID: id,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'AdminUserAttributes',
				SOAPFunction: 'getAdminUserAttributes',
				SOAPParams: 'ID,__search'
			}
		},
		// Filter config
		{
			filters: [
				{type: 'numeric',  dataIndex: 'ID'},
				{type: 'string',  dataIndex: 'Name'},
				{type: 'string',  dataIndex: 'Operator'},
				{type: 'string',  dataIndex: 'Value'},
				{type: 'boolean', dataIndex: 'Disabled'}
			]
		}
	);

	AdminUserAttributesWindow.show();
}


// Display edit/add form
function showAdminGroupAddEditWindow(id) {

	var submitAjaxConfig;


	// We doing an update
	if (id) {
		submitAjaxConfig = {
			ID: id,
			SOAPFunction: 'updateAdminGroup',
			SOAPParams: 
				'0:ID,'+
				'0:Name'
		};

	// We doing an Add
	} else {
		submitAjaxConfig = {
			SOAPFunction: 'createAdminGroup',
			SOAPParams: 
				'0:Name'
		};
	}
	
	// Create window
	var adminGroupFormWindow = new Ext.ux.GenericFormWindow(
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

	adminGroupFormWindow.show();

	if (id) {
		adminGroupFormWindow.getComponent('formpanel').load({
			params: {
				ID: id,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'AdminGroups',
				SOAPFunction: 'getAdminGroup',
				SOAPParams: 'ID'
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
						ID: id,
						SOAPUsername: globalConfig.soap.username,
						SOAPPassword: globalConfig.soap.password,
						SOAPAuthType: globalConfig.soap.authtype,
						SOAPModule: 'AdminGroups',
						SOAPFunction: 'removeAdminGroup',
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

