

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
						showAdminGroupAddEditWindow();
					}
				}, 
				'-', 
				{
					text:'Edit',
					tooltip:'Edit group',
					iconCls:'edit',
					handler: function() {
						var selectedItem = AdminGroupWindow.getComponent('gridpanel').getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminGroupAddEditWindow(selectedItem.data.ID);
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
					iconCls:'attributes',
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
					iconCls:'groups',
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

