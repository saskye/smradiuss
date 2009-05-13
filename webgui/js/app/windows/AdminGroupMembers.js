

function showAdminGroupMembersWindow(groupID) {

	var AdminGroupMembersWindow = new Ext.ux.GenericGridWindow(
		// Window config
		{
			title: "Members",
			
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
					tooltip:'Add member',
					iconCls:'add',
					handler: function() {
						showAdminGroupMemberAddEditWindow(groupID);
					}
				}, 
				'-', 
				{
					text:'Edit',
					tooltip:'Edit member',
					iconCls:'edit',
					handler: function() {
						var selectedItem = AdminGroupMembersWindow.getComponent('gridpanel').getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminGroupMemberAddEditWindow(groupID,selectedItem.data.ID);
						} else {
							AdminGroupMembersWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No member selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									AdminGroupMembersWindow.getEl().unmask();
								}
							});
						}
					}
				},
				'-', 
				{
					text:'Remove',
					tooltip:'Remove member',
					iconCls:'remove',
					handler: function() {
						var selectedItem = AdminGroupMembersWindow.getComponent('gridpanel').getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminGroupMemberRemoveWindow(AdminGroupMembersWindow,selectedItem.data.ID);
						} else {
							AdminGroupMembersWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No member selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									AdminGroupMembersWindow.getEl().unmask();
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
					sortable: true,
					dataIndex: 'Disabled'
				}
			]),
			autoExpandColumn: 'Name'
		},
		// Store config
		{
			baseParams: {
				ID: groupID,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'AdminGroupMembers',
				SOAPFunction: 'getAdminGroupMembers',
				SOAPParams: 'ID,__search'
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

	AdminGroupMembersWindow.show();
}


// Display edit/add form
function showAdminGroupMemberAddEditWindow(groupID,userID) {

	var submitAjaxConfig;


	// We doing an update
	if (userID) {
		submitAjaxConfig = {
			ID: userID,
			SOAPFunction: 'updateAdminGroupMember',
			SOAPParams: 
				'0:ID,'+
				'0:Name'
		};

	// We doing an Add
	} else {
		submitAjaxConfig = {
			GroupID: groupID,
			SOAPFunction: 'addAdminGroupMember',
			SOAPParams: 
				'0:GroupID,'+
				'0:Name'
		};
	}
	
	// Create window
	var adminGroupMembersFormWindow = new Ext.ux.GenericFormWindow(
		// Window config
		{
			title: "Member Information",

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
				SOAPModule: 'AdminGroupMembers'
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

	adminGroupMembersFormWindow.show();

	if (userID) {
		adminGroupMembersFormWindow.getComponent('formpanel').load({
			params: {
				ID: userID,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'AdminGroupMembers',
				SOAPFunction: 'getAdminGroupMember',
				SOAPParams: 'ID'
			}
		});
	}
}




// Display remove form
function showAdminGroupMemberRemoveWindow(parent,id) {
	// Mask parent window
	parent.getEl().mask();

	// Display remove confirm window
	Ext.Msg.show({
		title: "Confirm removal",
		msg: "Are you very sure you wish to remove this member?",
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
						SOAPModule: 'AdminGroupMembers',
						SOAPFunction: 'removeAdminGroupMember',
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

