/*
WiSP User Topups
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


function showWiSPUserTopupsWindow(userID) {

	var wispUserTopupsWindow = new Ext.ux.GenericGridWindow(
		// Window config
		{
			title: "User Topups",
			iconCls: 'silk-building',

			width: 500,
			height: 335,
			minWidth: 500,
			minHeight: 335,
		},
		// Grid config
		{
			// Inline toolbars
			tbar: [
				{
					text:'Add',
					tooltip:'Add topup',
					iconCls:'silk-building_add',
					handler: function() {
						showWiSPUserTopupAddEditWindow(userID,0);
					}
				}, 
				'-',
				{
					text:'Edit',
					tooltip:'Edit topup',
					iconCls:'silk-building_edit',
					handler: function() {
						var selectedItem = Ext.getCmp(wispUserTopupsWindow.gridPanelID).getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showWiSPUserTopupAddEditWindow(userID,selectedItem.data.ID);
						} else {
							wispUserTopupsWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No topup selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									wispUserTopupsWindow.getEl().unmask();
								}
							});
						}
					}
				},
				'-',
				{
					text:'Remove',
					tooltip:'Remove topup',
					iconCls:'silk-building_delete',
					handler: function() {
						var selectedItem = Ext.getCmp(wispUserTopupsWindow.gridPanelID).getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showWiSPUserTopupRemoveWindow(wispUserTopupsWindow,selectedItem.data.ID);
						} else {
							wispUserTopupsWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No topup selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									wispUserTopupsWindow.getEl().unmask();
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
					hidden: true,
					dataIndex: 'ID'
				},
				{
					header: "Type",
					sortable: true,
					dataIndex: 'Type'
				},
				{
					header: "Value",
					sortable: true,
					dataIndex: 'Value'
				},
				{
					header: "Timestamp",
					sortable: true,
					hidden: true,
					dataIndex: 'Timestamp'
				},
				{
					header: "ValidFrom",
					sortable: true,
					dataIndex: 'ValidFrom'
				},
				{
					header: "ValidTo",
					sortable: true,
					dataIndex: 'ValidTo'
				}
			]),
		},
		// Store config
		{
			baseParams: {
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'WiSPUsers',
				SOAPFunction: 'getWiSPUserTopups',
				SOAPParams: '0:UserID,__search',
				UserID: userID
			}
		},
		// Filter config
		{
			filters: [
				{type: 'numeric',  dataIndex: 'ID'},
				{type: 'date',  dataIndex: 'Timestamp'},
				{type: 'numeric',  dataIndex: 'Value'},
				{type: 'date',  dataIndex: 'ValidFrom'},
				{type: 'date',  dataIndex: 'ValidTo'}
			]
		}
	);

	wispUserTopupsWindow.show();
}


// Display edit/add form
function showWiSPUserTopupAddEditWindow(userID,topupID) {
	var today = new Date();
	var firstOfMonth = today.getFirstDateOfMonth();
	var firstOfNext = today.getLastDateOfMonth().add(Date.DAY, 1);

	var submitAjaxConfig;
	var icon;

	// We doing an update
	if (topupID) {
		icon = 'silk-building_edit';
		submitAjaxConfig = {
			ID: topupID,
			SOAPFunction: 'updateWiSPUserTopup',
			SOAPParams: 
				'0:ID,0:Value,0:Type,'+
				'0:ValidFrom,0:ValidTo'
		};
	// We doing an Add
	} else {
		icon = 'silk-building_add';
		submitAjaxConfig = {
			UserID: userID,
			SOAPFunction: 'createWiSPUserTopup',
			SOAPParams: 
				'0:UserID,0:Value,0:Type,'+
				'0:ValidFrom,0:ValidTo'
		};
	}

	// Create window
	var wispUserTopupFormWindow = new Ext.ux.GenericFormWindow(
		// Window config
		{
			title: "Topup Information",
			iconCls: icon,

			width: 400,
			height: 200,
			minWidth: 400,
			minHeight: 200
		},
		// Form panel config
		{
			labelWidth: 85,
			baseParams: {
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'WiSPUsers'
			},
			items: [
				{
					xtype: 'combo',
					fieldLabel: 'Type',
					name: 'Type',
					allowBlank: false,
					width: 157,
					store: [ 
						[ '1', 'Traffic' ],
						[ '2', 'Uptime' ]
					],
					displayField: 'Type',
					valueField: 'Type',
					hiddenName: 'Type',
					forceSelection: true,
					triggerAction: 'all',
					editable: false
				},
				{
					xtype: 'numberfield',
					fieldLabel: 'Value',
					name: 'Value',
					minValue: 1,
					allowBlank: false
				},
				{
					xtype: 'datefield',
					fieldLabel: 'ValidFrom',
					name: 'ValidFrom',
					id: 'ValidFrom',
					vtype: 'daterange',
					value: firstOfMonth,
					format: 'Y-m-d',
					endDateField: 'ValidTo'
				},
				{
					xtype: 'datefield',
					fieldLabel: 'ValidTo',
					name: 'ValidTo',
					id: 'ValidTo',
					vtype: 'daterange',
					value: firstOfNext,
					format: 'Y-m-d',
					startDateField: 'ValidFrom'
				}
			],
		},
		// Submit button config
		submitAjaxConfig
	);

	wispUserTopupFormWindow.show();

	if (topupID) {
		Ext.getCmp(wispUserTopupFormWindow.formPanelID).load({
			params: {
				id: topupID,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'WiSPUsers',
				SOAPFunction: 'getWiSPUserTopup',
				SOAPParams: 'id'
			}
		});
	}
}




// Display edit/add form
function showWiSPUserTopupRemoveWindow(parent,id) {
	// Mask parent window
	parent.getEl().mask();

	// Display remove confirm window
	Ext.Msg.show({
		title: "Confirm removal",
		msg: "Are you very sure you wish to remove this topup?",
		icon: Ext.MessageBox.ERROR,
		buttons: Ext.Msg.YESNO,
		modal: false,
		fn: function(buttonId,text) {
			// Check if user clicked on 'yes' button
			if (buttonId == 'yes') {

				// Do ajax request
				uxAjaxRequest(parent,{
					params: {
						id: id,
						SOAPUsername: globalConfig.soap.username,
						SOAPPassword: globalConfig.soap.password,
						SOAPAuthType: globalConfig.soap.authtype,
						SOAPModule: 'WiSPUsers',
						SOAPFunction: 'removeWiSPUserTopup',
						SOAPParams: 'id'
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
