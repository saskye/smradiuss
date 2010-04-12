/*
Admin Clients
Copyright (C) 2007-2009, AllWorldIT

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


function showAdminClientWindow() {

	var AdminClientWindow = new Ext.ux.GenericGridWindow(
		// Window config
		{
			title: "Clients",
			
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
					tooltip:'Add client',
					iconCls:'silk-server_add',
					handler: function() {
						showAdminClientAddEditWindow(AdminClientWindow);
					}
				}, 
				'-',
				{
					text:'Edit',
					tooltip:'Edit client',
					iconCls:'silk-server_edit',
					handler: function() {
						var selectedItem = Ext.getCmp(AdminClientWindow.gridPanelID).getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminClientAddEditWindow(AdminClientWindow,selectedItem.data.ID);
						} else {
							AdminClientWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No client selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									AdminClientWindow.getEl().unmask();
								}
							});
						}
					}
				},
				{
					text:'Remove',
					tooltip:'Remove client',
					iconCls:'silk-server_delete',
					handler: function() {
						var selectedItem = Ext.getCmp(AdminClientWindow.gridPanelID).getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminClientRemoveWindow(AdminClientWindow,selectedItem.data.ID);
						} else {
							AdminClientWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No client selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									AdminClientWindow.getEl().unmask();
								}
							});
						}
					}
				},
				'-',
				{
					text:'Attributes',
					tooltip:'Client attributes',
					iconCls:'silk-table',
					handler: function() {
						var selectedItem = Ext.getCmp(AdminClientWindow.gridPanelID).getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminClientAttributesWindow(selectedItem.data.ID);
						} else {
							AdminClientWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No client selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									AdminClientWindow.getEl().unmask();
								}
							});
						}
					}
				},
				'-',
				{
					text:'Realms',
					tooltip:'Realms',
					iconCls:'silk-world',
					handler: function() {
						var selectedItem = Ext.getCmp(AdminClientWindow.gridPanelID).getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminClientRealmsWindow(selectedItem.data.ID);
						} else {
							AdminClientWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No client selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									AdminClientWindow.getEl().unmask();
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
					header: "AccessList",
					sortable: true,
					dataIndex: 'AccessList'
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
				SOAPModule: 'AdminClients',
				SOAPFunction: 'getAdminClients',
				SOAPParams: '__null,__search'
			}
		},
		// Filter config
		{
			filters: [
				{type: 'numeric',  dataIndex: 'ID'},
				{type: 'string',  dataIndex: 'Name'},
				{type: 'string',  dataIndex: 'AccessList'}
			]
		}
	);

	AdminClientWindow.show();
}


// Display edit/add form
function showAdminClientAddEditWindow(AdminClientWindow,id) {

	var submitAjaxConfig;
	var icon;

	// We doing an update
	if (id) {
		icon = 'silk-server_edit';
		submitAjaxConfig = {
			params: {
				ID: id,
				SOAPFunction: 'updateAdminClient',
				SOAPParams: 
					'0:ID,'+
					'0:Name,'+
					'0:AccessList'
			},
			onSuccess: function() {
				var store = Ext.getCmp(AdminClientWindow.gridPanelID).getStore();
				store.load({
					params: {
						limit: 25
					}
				});
			}
		};

	// We doing an Add
	} else {
		icon = 'silk-server_add';
		submitAjaxConfig = {
			params: {
				SOAPFunction: 'createAdminClient',
				SOAPParams: 
					'0:Name,'+
					'0:AccessList'
			},
			onSuccess: function() {
				var store = Ext.getCmp(AdminClientWindow.gridPanelID).getStore();
				store.load({
					params: {
						limit: 25
					}
				});
			}
		};
	}

	// Create window
	var adminClientFormWindow = new Ext.ux.GenericFormWindow(
		// Window config
		{
			title: "Client Information",
			iconCls: icon,

			width: 310,
			height: 143,

			minWidth: 310,
			minHeight: 143
		},
		// Form panel config
		{
			labelWidth: 85,
			baseParams: {
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'AdminClients'
			},
			items: [
				{
					fieldLabel: 'Name',
					name: 'Name',
					allowBlank: false
				},
				{
					fieldLabel: 'AccessList',
					name: 'AccessList',
					allowBlank: true
				}
			]
		},
		// Submit button config
		submitAjaxConfig
	);

	adminClientFormWindow.show();

	if (id) {
		Ext.getCmp(adminClientFormWindow.formPanelID).load({
			params: {
				ID: id,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'AdminClients',
				SOAPFunction: 'getAdminClient',
				SOAPParams: 'ID'
			}
		});
	}
}


// Display remove form
function showAdminClientRemoveWindow(AdminClientWindow,id) {
	// Mask AdminClientWindow window
	AdminClientWindow.getEl().mask();

	// Display remove confirm window
	Ext.Msg.show({
		title: "Confirm removal",
		msg: "Are you very sure you wish to remove this client?",
		icon: Ext.MessageBox.ERROR,
		buttons: Ext.Msg.YESNO,
		modal: false,
		fn: function(buttonId,text) {
			// Check if user clicked on 'yes' button
			if (buttonId == 'yes') {

				// Do ajax request
				uxAjaxRequest(AdminClientWindow,{
					params: {
						ID: id,
						SOAPUsername: globalConfig.soap.username,
						SOAPPassword: globalConfig.soap.password,
						SOAPAuthType: globalConfig.soap.authtype,
						SOAPModule: 'AdminClients',
						SOAPFunction: 'removeAdminClient',
						SOAPParams: 'ID'
					},
					customSuccess: function() {
						var store = Ext.getCmp(AdminClientWindow.gridPanelID).getStore();
						store.load({
							params: {
								limit: 25
							}
						});
					}
				});


			// Unmask if user answered no
			} else {
				AdminClientWindow.getEl().unmask();
			}
		}
	});
}



// vim: ts=4
