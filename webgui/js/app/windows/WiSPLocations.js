/*
WiSP Locations
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


function showWiSPLocationWindow() {

	var WiSPLocationWindow = new Ext.ux.GenericGridWindow(
		// Window config
		{
			title: "Locations",
			iconCls: 'silk-map',
			
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
					tooltip:'Add location',
					iconCls:'silk-map_add',
					handler: function() {
						showWiSPLocationAddEditWindow(WiSPLocationWindow);
					}
				}, 
				'-',
				{
					text:'Edit',
					tooltip:'Edit location',
					iconCls:'silk-map_edit',
					handler: function() {
						var selectedItem = Ext.getCmp(WiSPLocationWindow.gridPanelID).getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showWiSPLocationAddEditWindow(WiSPLocationWindow,selectedItem.data.ID);
						} else {
							WiSPLocationWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No location selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									WiSPLocationWindow.getEl().unmask();
								}
							});
						}
					}
				},
				'-', 
				{
					text:'Remove',
					tooltip:'Remove location',
					iconCls:'silk-map_delete',
					handler: function() {
						var selectedItem = Ext.getCmp(WiSPLocationWindow.gridPanelID).getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showWiSPLocationRemoveWindow(WiSPLocationWindow,selectedItem.data.ID);
						} else {
							WiSPLocationWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No location selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									WiSPLocationWindow.getEl().unmask();
								}
							});
						}
					}
				},
				'-',
				{
					text:'Members',
					tooltip:'List members',
					iconCls:'silk-user',
					handler: function() {
						var selectedItem = Ext.getCmp(WiSPLocationWindow.gridPanelID).getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showWiSPLocationMembersWindow(selectedItem.data.ID);
						} else {
							WiSPLocationWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No location selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									WiSPLocationWindow.getEl().unmask();
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
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'WiSPLocations',
				SOAPFunction: 'getWiSPLocations',
				SOAPParams: '__null,__search'
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

	WiSPLocationWindow.show();
}


// Display edit/add form
function showWiSPLocationAddEditWindow(WiSPLocationWindow,id) {

	var submitAjaxConfig;
	var icon;

	// We doing an update
	if (id) {
		icon = 'silk-map_edit';
		submitAjaxConfig = {
			params: {
				ID: id,
				SOAPFunction: 'updateWiSPLocation',
				SOAPParams: 
					'0:ID,'+
					'0:Name'
			},
			onSuccess: function() {
				var store = Ext.getCmp(WiSPLocationWindow.gridPanelID).getStore();
				store.load({
					params: {
						limit: 25
					}
				});
			}
		};

	// We doing an Add
	} else {
		icon = 'silk-map_add';
		submitAjaxConfig = {
			params: {
				SOAPFunction: 'createWiSPLocation',
				SOAPParams: 
					'0:Name'
			},
			onSuccess: function() {
				var store = Ext.getCmp(WiSPLocationWindow.gridPanelID).getStore();
				store.load({
					params: {
						limit: 25
					}
				});
			}
		};
	}
	
	// Create window
	var wispLocationFormWindow = new Ext.ux.GenericFormWindow(
		// Window config
		{
			title: "Location Information",
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
				SOAPModule: 'WiSPLocations'
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

	wispLocationFormWindow.show();

	if (id) {
		Ext.getCmp(wispLocationFormWindow.formPanelID).load({
			params: {
				ID: id,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'WiSPLocations',
				SOAPFunction: 'getWiSPLocation',
				SOAPParams: 'ID'
			}
		});
	}
}


// Display remove form
function showWiSPLocationRemoveWindow(WiSPLocationWindow,id) {
	// Mask WiSPLocationWindow window
	WiSPLocationWindow.getEl().mask();

	// Display remove confirm window
	Ext.Msg.show({
		title: "Confirm removal",
		msg: "Are you very sure you wish to remove this location?",
		icon: Ext.MessageBox.ERROR,
		buttons: Ext.Msg.YESNO,
		modal: false,
		fn: function(buttonId,text) {
			// Check if user clicked on 'yes' button
			if (buttonId == 'yes') {

				// Do ajax request
				uxAjaxRequest(WiSPLocationWindow,{
					params: {
						id: id,
						SOAPUsername: globalConfig.soap.username,
						SOAPPassword: globalConfig.soap.password,
						SOAPAuthType: globalConfig.soap.authtype,
						SOAPModule: 'WiSPLocations',
						SOAPFunction: 'removeWiSPLocation',
						SOAPParams: 'id'
					},
					customSuccess: function() {
						var store = Ext.getCmp(WiSPLocationWindow.gridPanelID).getStore();
						store.load({
							params: {
								limit: 25
							}
						});
					}
				});


			// Unmask if user answered no
			} else {
				WiSPLocationWindow.getEl().unmask();
			}
		}
	});
}


// vim: ts=4
