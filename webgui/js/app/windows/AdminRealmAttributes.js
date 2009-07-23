/*
Admin Realm Attributes
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


function showAdminRealmAttributesWindow(realmID) {

	var AdminRealmAttributesWindow = new Ext.ux.GenericGridWindow(
		// Window config
		{
			title: "Attributes",
			iconCls: 'silk-table',
			
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
					tooltip:'Add attribute',
					iconCls:'silk-table_add',
					handler: function() {
						showAdminRealmAttributeAddEditWindow(realmID);
					}
				}, 
				'-', 
				{
					text:'Edit',
					tooltip:'Edit attribute',
					iconCls:'silk-table_edit',
					handler: function() {
						var selectedItem = Ext.getCmp(AdminRealmAttributesWindow.gridPanelID).getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminRealmAttributeAddEditWindow(realmID,selectedItem.data.ID);
						} else {
							AdminRealmAttributesWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No attribute selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									AdminRealmAttributesWindow.getEl().unmask();
								}
							});
						}
					}
				},
				'-', 
				{
					text:'Remove',
					tooltip:'Remove attribute',
					iconCls:'silk-table_delete',
					handler: function() {
						var selectedItem = Ext.getCmp(AdminRealmAttributesWindow.gridPanelID).getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminRealmAttributeRemoveWindow(AdminRealmAttributesWindow,selectedItem.data.ID);
						} else {
							AdminRealmAttributesWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No attribute selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									AdminRealmAttributesWindow.getEl().unmask();
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
					header: "Operator",
					sortable: true,
					dataIndex: 'Operator'
				},
				{
					header: "Value",
					sortable: true,
					dataIndex: 'Value'
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
				ID: realmID,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'AdminRealmAttributes',
				SOAPFunction: 'getAdminRealmAttributes',
				SOAPParams: 'ID,__search'
			}
		},
		// Filter config
		{
			filters: [
				{type: 'numeric',  dataIndex: 'ID'},
				{type: 'string',  dataIndex: 'Name'},
				{type: 'string',  dataIndex: 'Operator'},
				{type: 'string',  dataIndex: 'Value'},
				{type: 'boolean', dataIndex: 'Disabled'}
			]
		}
	);

	AdminRealmAttributesWindow.show();
}


// Display edit/add form
function showAdminRealmAttributeAddEditWindow(realmID,attrID) {

	var submitAjaxConfig;
	var icon;


	// We doing an update
	if (attrID) {
		icon = 'silk-table_edit';
		submitAjaxConfig = {
			ID: attrID,
			SOAPFunction: 'updateAdminRealmAttribute',
			SOAPParams: 
				'0:ID,'+
				'0:Name,'+
				'0:Operator,'+
				'0:Value,'+
				'0:Disabled:boolean'
		};

	// We doing an Add
	} else {
		icon = 'silk-table_add';
		submitAjaxConfig = {
			RealmID: realmID,
			SOAPFunction: 'addAdminRealmAttribute',
			SOAPParams: 
				'0:RealmID,'+
				'0:Name,'+
				'0:Operator,'+
				'0:Value,'+
				'0:Disabled:boolean'
		};
	}
	
	// Create window
	var adminRealmAttributesFormWindow = new Ext.ux.GenericFormWindow(
		// Window config
		{
			title: "Attribute Information",
			iconCls: icon,

			width: 310,
			height: 200,

			minWidth: 310,
			minHeight: 200
		},
		// Form panel config
		{
			labelWidth: 85,
			baseParams: {
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'AdminRealmAttributes'
			},
			items: [
				{
					fieldLabel: 'Name',
					name: 'Name',
					allowBlank: false
				},
				{
					xtype: 'combo',
					fieldLabel: 'Operator',
					name: 'Operator',
					allowBlank: false,
					width: 157,
					store: [ 
						[ '=', '=' ], 
						[ ':=', ':='  ],
						[ '==', '==' ], 
						[ '+=', '+=' ],
						[ '!=', '!=' ],
						[ '<', '<' ],
						[ '>', '>' ],
						[ '<=', '<=' ],
						[ '>=', '>=' ],
						[ '=~', '=~' ],
						[ '!~', '!~' ],
						[ '=*', '=*' ],
						[ '!*', '!*' ],
						[ '||==', '||==' ]
					],
					displayField: 'Operator',
					valueField: 'Operator',
					hiddenName: 'Operator',
					forceSelection: true,
					triggerAction: 'all',
					editable: false
				},
				{
					fieldLabel: "Value",
					name: "Value",
					allowBlank: false
				},
				{
					xtype: 'checkbox',
					fieldLabel: 'Disabled',
					name: 'Disabled'
				},
			],
		},
		// Submit button config
		submitAjaxConfig
	);

	adminRealmAttributesFormWindow.show();

	if (attrID) {
		Ext.getCmp(adminRealmAttributesFormWindow.formPanelID).load({
			params: {
				ID: attrID,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'AdminRealmAttributes',
				SOAPFunction: 'getAdminRealmAttribute',
				SOAPParams: 'ID'
			}
		});
	}
}




// Display remove form
function showAdminRealmAttributeRemoveWindow(parent,id) {
	// Mask parent window
	parent.getEl().mask();

	// Display remove confirm window
	Ext.Msg.show({
		title: "Confirm removal",
		msg: "Are you very sure you wish to remove this attribute?",
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
						SOAPModule: 'AdminRealmAttributes',
						SOAPFunction: 'removeAdminRealmAttribute',
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
