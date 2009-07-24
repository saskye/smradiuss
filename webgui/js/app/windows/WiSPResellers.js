/*
WiSP Resellers
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


function showWiSPResellersWindow() {

	var WiSPResellerWindow = new Ext.ux.GenericGridWindow(
		// Window config
		{
			title: "Resellers",
			
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
					tooltip:'Add Reseller',
					iconCls:'add',
					handler: function() {
						showWiSPResellerEditWindow();
					}
				}, 
				'-', 
				{
					text:'Edit',
					tooltip:'Edit Reseller',
					iconCls:'option',
					handler: function() {
						var selectedItem = Ext.getCmp(WiSPResellerWindow.gridPanelID).getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showWiSPResellerEditWindow(selectedItem.data.ID);
						} else {
							WiSPResellerWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No Reseller selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									WiSPResellerWindow.getEl().unmask();
								}
							});
						}
					}
				},
				'-',
				{
					text:'Remove',
					tooltip:'Remove Reseller',
					iconCls:'remove',
					handler: function() {
						var selectedItem = Ext.getCmp(WiSPResellerWindow.gridPanelID).getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showWiSPResellerRemoveWindow(WiSPResellerWindow,selectedItem.data.ID);
						} else {
							WiSPResellerWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No Reseller selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									WiSPResellerWindow.getEl().unmask();
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
				SOAPModule: 'WiSPResellers',
				SOAPFunction: 'getWiSPResellers',
				SOAPParams: '__search'
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

	WiSPResellerWindow.show();
}


// Display edit/add form
function showWiSPResellerEditWindow(id) {

	var submitAjaxConfig;

	// We doing an update
	if (id) {
		submitAjaxConfig = {
			ID: id,
			SOAPFunction: 'updateWiSPReseller',
			SOAPParams: 
				'0:ID,0:Name,'+
				'0:ContactPerson,0:ContactTel1,0:ContactTel2,0:ContactEmail,'+
				'0:SiteQuota,0:MinSiteSize,'+
				'0:MailboxQuota,0:MinMailboxSize'
		};
	// We doing an Add
	} else {
		submitAjaxConfig = {
			SOAPFunction: 'createWiSPReseller',
			SOAPParams: 
				'0:Name,'+
				'0:ContactPerson,0:ContactTel1,0:ContactTel2,0:ContactEmail,'+
				'0:SiteQuota,0:MinSiteSize,'+
				'0:MailboxQuota,0:MinMailboxSize'
		};
	}

	// Create window
	var WiSPResellerFormWindow = new Ext.ux.GenericFormWindow(
		// Window config
		{
			title: "Reseller Information",

			width: 300,
			height: 348,

			minWidth: 300,
			minHeight: 348
		},
		// Form panel config
		{
			labelWidth: 85,
			baseParams: {
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'Admin'
			},
			items: [
				{
					fieldLabel: 'Name',
					name: 'Name',
					allowBlank: false
				},
				{
					fieldLabel: 'Contact Person',
					name: 'ContactPerson',
					allowBlank: false
				},
				{
					fieldLabel: 'Contact Tel 1',
					name: 'ContactTel1',
					allowBlank: false
				},
				{
					fieldLabel: 'Contact Tel 2',
					name: 'ContactTel2'
				},
				{
					fieldLabel: 'Contact Email',
					name: 'ContactEmail',
					vtype: 'email',
					allowBlank: false
				},
	
				{
					xtype: 'tabpanel',
					plain: 'true',
					deferredRender: false, // Load all panels!
					activeTab: 0,
					height: 100,
					defaults: {
						layout: 'form',
						bodyStyle: 'padding: 10px;'
					},
					
					items: [
						{
							title: 'Web Hosting',
							layout: 'form',
							defaultType: 'textfield',
							items: [
								{
									fieldLabel: 'Site Quota',
									name: 'SiteQuota',
									allowBlank: false,
									vtype: 'number',
									value: '0'
								},
								{
									fieldLabel: 'Min Site Size',
									name: 'MinSiteSize',
									allowBlank: false,
									vtype: 'number',
									value: '5'
								}
							]
						},
						{
							title: 'Mail Hosting',
							layout: 'form',
							defaultType: 'textfield',
							items: [
								{
									fieldLabel: 'Mail Quota',
									name: 'MailboxQuota',
									allowBlank: false,
									vtype: 'number',
									value: '0'
								},
								{
									fieldLabel: 'Min Mailbox Size',
									name: 'MinMailboxSize',
									allowBlank: false,
									vtype: 'number',
									value: '1'
								}
							]
						}
					]
				}
			]
		},
		// Submit button config
		submitAjaxConfig
	);

	WiSPResellerFormWindow.show();

	if (id) {
		Ext.getCmp(WiSPResellerFormWindow.formPanelID).load({
			params: {
				id: id,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'WiSPResellers',
				SOAPFunction: 'getWiSPReseller',
				SOAPParams: 'id'
			}
		});
	}
}




// Display edit/add form
function showWiSPResellerRemoveWindow(parent,id) {
	// Mask parent window
	parent.getEl().mask();

	// Display remove confirm window
	Ext.Msg.show({
		title: "Confirm removal",
		msg: "Are you very sure you wish to remove this WiSPReseller?",
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
						SOAPModule: 'WiSPResellers',
						SOAPFunction: 'removeWiSPReseller',
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
