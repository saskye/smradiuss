

function showAdminUserGroupsWindow(userID) {

	var AdminUserGroupsWindow = new Ext.ux.GenericGridWindow(
		// Window config
		{
			title: "Groups",
			
			width: 400,
			height: 335,
		
			minWidth: 400,
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
						showAdminUserGroupAddWindow(userID);
					}
				}, 
				'-', 
				{
					text:'Remove',
					tooltip:'Remove group',
					iconCls:'remove',
					handler: function() {
						var selectedItem = AdminUserGroupsWindow.getComponent('gridpanel').getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminUserGroupRemoveWindow(AdminUserGroupsWindow,selectedItem.data.ID);
						} else {
							AdminUserGroupsWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No group selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									AdminUserGroupsWindow.getEl().unmask();
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
				ID: userID,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'AdminUserGroups',
				SOAPFunction: 'getAdminUserGroups',
				SOAPParams: 'ID,__search'
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

	AdminUserGroupsWindow.show();
}


// Display edit/add form
function showAdminUserGroupAddWindow(userID,id) {

	var submitAjaxConfig;


	// We doing an update
	if (id) {
		submitAjaxConfig = {
			ID: id,
			SOAPFunction: 'updateAdminGroup',
			SOAPParams: 
				'0:ID,'+
				'0:GroupID'
		};

	// We doing an Add
	} else {
		submitAjaxConfig = {
			UserID: userID,
			SOAPFunction: 'addAdminUserGroup',
			SOAPParams: 
				'0:UserID,'+
				'0:GroupID'
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
					xtype: 'combo',
					//id: 'combo',
					fieldLabel: 'Group',
					name: 'Group',
					allowBlank: false,
					width: 160,

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
					forceSelection: true,
					triggerAction: 'all',
					editable: false
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
function showAdminUserGroupRemoveWindow(parent,id) {
	// Mask parent window
	parent.getEl().mask();

	// Display remove confirm window
	Ext.Msg.show({
		title: "Confirm removal",
		msg: "Are you very sure you wish to unlink this group?",
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
						SOAPModule: 'AdminUserGroups',
						SOAPFunction: 'removeAdminUserGroup',
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

