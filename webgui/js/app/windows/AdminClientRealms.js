/*
Admin Client Realms
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


function showAdminClientRealmsWindow(clientID) {

	var AdminClientRealmsWindow = new Ext.ux.GenericGridWindow(
		// Window config
		{
			title: "Realms",
			iconCls: 'silk-world',
			
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
					tooltip:'Add realm',
					iconCls: 'silk-world_add',
					handler: function() {
						showAdminClientRealmAddWindow(clientID);
					}
				}, 
				'-', 
				{
					text:'Remove',
					tooltip:'Remove realm',
					iconCls: 'silk-world_delete',
					handler: function() {
						var selectedItem = Ext.getCmp(AdminClientRealmsWindow.gridPanelID).getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminClientRealmRemoveWindow(AdminClientRealmsWindow,selectedItem.data.ID);
						} else {
							AdminClientRealmsWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No realm selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									AdminClientRealmsWindow.getEl().unmask();
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
				ID: clientID,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'AdminClientRealms',
				SOAPFunction: 'getAdminClientRealms',
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

	AdminClientRealmsWindow.show();
}


// Display edit/add form
function showAdminClientRealmAddWindow(clientID,id) {

	var submitAjaxConfig;
	var icon;

	// We doing an update
	if (id) {
		icon = 'silk-world_edit',
		submitAjaxConfig = {
			ID: id,
			SOAPFunction: 'updateAdminRealm',
			SOAPParams: 
				'0:ID,'+
				'0:RealmID'
		};

	// We doing an Add
	} else {
		icon = 'silk-world_add',
		submitAjaxConfig = {
			ClientID: clientID,
			SOAPFunction: 'addAdminClientRealm',
			SOAPParams: 
				'0:ClientID,'+
				'0:RealmID'
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
					xtype: 'combo',
					//id: 'combo',
					fieldLabel: 'Realm',
					name: 'Realm',
					allowBlank: false,
					width: 160,

					store: new Ext.ux.JsonStore({
						sortInfo: { field: "Name", direction: "ASC" },
						baseParams: {
							SOAPUsername: globalConfig.soap.username,
							SOAPPassword: globalConfig.soap.password,
							SOAPAuthType: globalConfig.soap.authtype,
							SOAPModule: 'AdminClientRealms',
							SOAPFunction: 'getAdminRealms',
							SOAPParams: '__null,__search'
						}
					}),
					displayField: 'Name',
					valueField: 'ID',
					hiddenName: 'RealmID',
					forceSelection: true,
					triggerAction: 'all',
					editable: false
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




// Display edit/add form
function showAdminClientRealmRemoveWindow(parent,id) {
	// Mask parent window
	parent.getEl().mask();

	// Display remove confirm window
	Ext.Msg.show({
		title: "Confirm removal",
		msg: "Are you very sure you wish to unlink this realm?",
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
						SOAPModule: 'AdminClientRealms',
						SOAPFunction: 'removeAdminClientRealm',
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


// vim: ts=4
