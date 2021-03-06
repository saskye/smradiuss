/*
Admin User Groups
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


function showAdminUserGroupsWindow(userID) {

	var AdminUserGroupsWindow = new Ext.ux.GenericGridWindow(
		// Window config
		{
			title: "Groups",
			iconCls: 'silk-group',
			
			width: 400,
			height: 335,
		
			minWidth: 400,
			minHeight: 335
		},
		// Grid config
		{
			// Inline toolbars
			tbar: [
				{
					text:'Add',
					tooltip:'Add group',
					iconCls:'silk-group_add',
					handler: function() {
						showAdminUserGroupAddWindow(AdminUserGroupsWindow,userID);
					}
				}, 
				'-', 
				{
					text:'Remove',
					tooltip:'Remove group',
					iconCls:'silk-group_delete',
					handler: function() {
						var selectedItem = Ext.getCmp(AdminUserGroupsWindow.gridPanelID).getSelectionModel().getSelected();
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
function showAdminUserGroupAddWindow(AdminUserGroupsWindow,userID,id) {

	var submitAjaxConfig;
	var icon;


	// We doing an update
	if (id) {
		icon = 'silk-group_edit';
		submitAjaxConfig = {
			params: {
				ID: id,
				SOAPFunction: 'updateAdminGroup',
				SOAPParams: 
					'0:ID,'+
					'0:GroupID'
			},
			onSuccess: function() {
				var store = Ext.getCmp(AdminUserGroupsWindow.gridPanelID).getStore();
				store.load({
					params: {
						limit: 25
					}
				});
			}
		};

	// We doing an Add
	} else {
		icon = 'silk-group_add';
		submitAjaxConfig = {
			params: {
				UserID: userID,
				SOAPFunction: 'addAdminUserGroup',
				SOAPParams: 
					'0:UserID,'+
					'0:GroupID'
			},
			onSuccess: function() {
				var store = Ext.getCmp(AdminUserGroupsWindow.gridPanelID).getStore();
				store.load({
					params: {
						limit: 25
					}
				});
			}
		};
	}
	
	// Create window
	var adminGroupFormWindow = new Ext.ux.GenericFormWindow(
		// Window config
		{
			title: "Group Information",
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
				}
			]
		},
		// Submit button config
		submitAjaxConfig
	);

	adminGroupFormWindow.show();

	if (id) {
		Ext.getCmp(adminGroupFormWindow.formPanelID).load({
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
function showAdminUserGroupRemoveWindow(AdminUserGroupsWindow,id) {
	// Mask AdminUserGroupsWindow window
	AdminUserGroupsWindow.getEl().mask();

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
				uxAjaxRequest(AdminUserGroupsWindow,{
					params: {
						ID: id,
						SOAPUsername: globalConfig.soap.username,
						SOAPPassword: globalConfig.soap.password,
						SOAPAuthType: globalConfig.soap.authtype,
						SOAPModule: 'AdminUserGroups',
						SOAPFunction: 'removeAdminUserGroup',
						SOAPParams: 'ID'
					},
					customSuccess: function() {
						var store = Ext.getCmp(AdminUserGroupsWindow.gridPanelID).getStore();
						store.load({
							params: {
								limit: 25
							}
						});
					}
				});


			// Unmask if user answered no
			} else {
				AdminUserGroupsWindow.getEl().unmask();
			}
		}
	});
}


// vim: ts=4
