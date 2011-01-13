/*
Admin Users
Copyright (C) 2007-2011, AllWorldIT

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/


function showAdminUserWindow() {

	var AdminUserWindow = new Ext.ux.GenericGridWindow(
		// Window config
		{
			title: "Users",
			iconCls: 'silk-user',
			
			width: 600,
			height: 335,
		
			minWidth: 600,
			minHeight: 335
		},
		// Grid config
		{
			// Inline toolbars
			tbar: [
				{
					text:'Add',
					tooltip:'Add user',
					iconCls:'silk-user_add',
					handler: function() {
						showAdminUserAddEditWindow(AdminUserWindow);
					}
				}, 
				'-', 
				{
					text:'Edit',
					tooltip:'Edit user',
					iconCls:'silk-user_edit',
					handler: function() {
						var selectedItem = Ext.getCmp(AdminUserWindow.gridPanelID).getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminUserAddEditWindow(AdminUserWindow,selectedItem.data.ID);
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
					iconCls:'silk-user_delete',
					handler: function() {
						var selectedItem = Ext.getCmp(AdminUserWindow.gridPanelID).getSelectionModel().getSelected();
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
					text:'Attributes',
					tooltip:'User attributes',
					iconCls:'silk-table',
					handler: function() {
						var selectedItem = Ext.getCmp(AdminUserWindow.gridPanelID).getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminUserAttributesWindow(selectedItem.data.ID);
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
					iconCls: 'silk-page_white_text',
					handler: function() {
						var selectedItem = Ext.getCmp(AdminUserWindow.gridPanelID).getSelectionModel().getSelected();
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
					iconCls:'silk-group',
					handler: function() {
						var selectedItem = Ext.getCmp(AdminUserWindow.gridPanelID).getSelectionModel().getSelected();
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
				},
				'-',
				{
					text:'Topups',
					tooltip:'User topups',
					iconCls:'silk-chart_bar',
					handler: function() {
						var selectedItem = Ext.getCmp(AdminUserWindow.gridPanelID).getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminUserTopupsWindow(selectedItem.data.ID);
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
function showAdminUserAddEditWindow(AdminUserWindow,id) {

	var submitAjaxConfig;
	var icon;

	// We doing an update
	if (id) {
		icon = 'silk-user_edit';
		submitAjaxConfig = {
			params: {
				ID: id,
				SOAPFunction: 'updateAdminUser',
				SOAPParams: 
					'0:ID,'+
					'0:Username'
			},
			onSuccess: function() {
				var store = Ext.getCmp(AdminUserWindow.gridPanelID).getStore();
				store.load({
					params: {
						limit: 25
					}
				});
			}
		};

	// We doing an Add
	} else {
		icon = 'silk-user_add';
		submitAjaxConfig = {
			params: {
				SOAPFunction: 'createAdminUser',
				SOAPParams: 
					'0:Username'
			},
			onSuccess: function() {
				var store = Ext.getCmp(AdminUserWindow.gridPanelID).getStore();
				store.load({
					params: {
						limit: 25
					}
				});
			}
		};
	}

	// Create window
	var adminUserFormWindow = new Ext.ux.GenericFormWindow(
		// Window config
		{
			title: "User Information",
			iconCls: icon,

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
				SOAPModule: 'AdminUsers'
			},
			items: [
				{
					fieldLabel: 'Username',
					name: 'Username',
					vtype: 'usernameRadius',
					maskRe: usernameRadiusPartRe,
					allowBlank: false
				}
			]
		},
		// Submit button config
		submitAjaxConfig
	);

	adminUserFormWindow.show();

	if (id) {
		Ext.getCmp(adminUserFormWindow.formPanelID).load({
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
function showAdminUserRemoveWindow(AdminUserWindow,id) {
	// Mask AdminUserWindow window
	AdminUserWindow.getEl().mask();

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
				uxAjaxRequest(AdminUserWindow,{
					params: {
						ID: id,
						SOAPUsername: globalConfig.soap.username,
						SOAPPassword: globalConfig.soap.password,
						SOAPAuthType: globalConfig.soap.authtype,
						SOAPModule: 'AdminUsers',
						SOAPFunction: 'removeAdminUser',
						SOAPParams: 'ID'
					},
					customSuccess: function() {
						var store = Ext.getCmp(AdminUserWindow.gridPanelID).getStore();
						store.load({
							params: {
								limit: 25
							}
						});
					}
				});


			// Unmask if user answered no
			} else {
				AdminUserWindow.getEl().unmask();
			}
		}
	});
}











// vim: ts=4
