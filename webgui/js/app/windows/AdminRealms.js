/*
Admin Realms
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


function showAdminRealmWindow() {

	var AdminRealmWindow = new Ext.ux.GenericGridWindow(
		// Window config
		{
			title: "Realms",
			iconCls: 'silk-world',
			
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
					tooltip:'Add realm',
					iconCls:'silk-world_add',
					handler: function() {
						showAdminRealmAddEditWindow(AdminRealmWindow);
					}
				}, 
				'-',
				{
					text:'Edit',
					tooltip:'Edit realm',
					iconCls:'silk-world_edit',
					handler: function() {
						var selectedItem = Ext.getCmp(AdminRealmWindow.gridPanelID).getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminRealmAddEditWindow(AdminRealmWindow,selectedItem.data.ID);
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
					text:'Remove',
					tooltip:'Remove realm',
					iconCls:'silk-world_delete',
					handler: function() {
						var selectedItem = Ext.getCmp(AdminRealmWindow.gridPanelID).getSelectionModel().getSelected();
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
					text:'Attributes',
					tooltip:'Realm attributes',
					iconCls:'silk-table',
					handler: function() {
						var selectedItem = Ext.getCmp(AdminRealmWindow.gridPanelID).getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminRealmAttributesWindow(selectedItem.data.ID);
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
					iconCls:'silk-server',
					handler: function() {
						var selectedItem = Ext.getCmp(AdminRealmWindow.gridPanelID).getSelectionModel().getSelected();
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
			autoExpandColumn: 'Name'
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
				{type: 'string',  dataIndex: 'Name'},
				{type: 'boolean', dataIndex: 'Disabled'}
			]
		}
	);

	AdminRealmWindow.show();
}


// Display edit/add form
function showAdminRealmAddEditWindow(AdminRealmWindow,id) {

	var submitAjaxConfig;
	var icon;


	// We doing an update
	if (id) {
		icon = 'silk-world_edit';
		submitAjaxConfig = {
			params: {
				ID: id,
				SOAPFunction: 'updateAdminRealm',
				SOAPParams: 
					'0:ID,'+
					'0:Name'
			},
			onSuccess: function() {
				var store = Ext.getCmp(AdminRealmWindow.gridPanelID).getStore();
				store.load({
					params: {
						limit: 25
					}
				});
			}
		};

	// We doing an Add
	} else {
		icon = 'silk-world_add';
		submitAjaxConfig = {
			params: {
				SOAPFunction: 'createAdminRealm',
				SOAPParams: 
					'0:Name'
			},
			onSuccess: function() {
				var store = Ext.getCmp(AdminRealmWindow.gridPanelID).getStore();
				store.load({
					params: {
						limit: 25
					}
				});
			}
		};
	}

	// Create window
	var adminRealmFormWindow = new Ext.ux.GenericFormWindow(
		// Window config
		{
			title: "Realm Information",
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
				SOAPModule: 'AdminRealms'
			},
			items: [
				{
					fieldLabel: 'Name',
					name: 'Name',
					allowBlank: false
				}
			]
		},
		// Submit button config
		submitAjaxConfig
	);

	adminRealmFormWindow.show();

	if (id) {
		Ext.getCmp(adminRealmFormWindow.formPanelID).load({
			params: {
				ID: id,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'AdminRealms',
				SOAPFunction: 'getAdminRealm',
				SOAPParams: 'ID'
			}
		});
	}
}




// Display remove form
function showAdminRealmRemoveWindow(AdminRealmWindow,id) {
	// Mask AdminRealmWindow window
	AdminRealmWindow.getEl().mask();

	// Display remove confirm window
	Ext.Msg.show({
		title: "Confirm removal",
		msg: "Are you very sure you wish to remove this realm?",
		icon: Ext.MessageBox.ERROR,
		buttons: Ext.Msg.YESNO,
		modal: false,
		fn: function(buttonId,text) {
			// Check if user clicked on 'yes' button
			if (buttonId == 'yes') {

				// Do ajax request
				uxAjaxRequest(AdminRealmWindow,{
					params: {
						ID: id,
						SOAPUsername: globalConfig.soap.username,
						SOAPPassword: globalConfig.soap.password,
						SOAPAuthType: globalConfig.soap.authtype,
						SOAPModule: 'AdminRealms',
						SOAPFunction: 'removeAdminRealm',
						SOAPParams: 'ID'
					},
					customSuccess: function() {
						var store = Ext.getCmp(AdminRealmWindow.gridPanelID).getStore();
						store.load({
							params: {
								limit: 25
							}
						});
					}
				});


			// Unmask if user answered no
			} else {
				AdminRealmWindow.getEl().unmask();
			}
		}
	});
}











// vim: ts=4
