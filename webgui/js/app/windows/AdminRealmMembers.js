/*
Admin Realm Members
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


function showAdminRealmMembersWindow(realmID) {

	var AdminRealmMembersWindow = new Ext.ux.GenericGridWindow(
		// Window config
		{
			title: "Members",
			iconCls: 'silk-server',
			
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
					text:'Remove',
					tooltip:'Remove member',
					iconCls:'silk-server_delete',
					handler: function() {
						var selectedItem = Ext.getCmp(AdminRealmMembersWindow.gridPanelID).getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminRealmMemberRemoveWindow(AdminRealmMembersWindow,selectedItem.data.ID);
						} else {
							AdminRealmMembersWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No member selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									AdminRealmMembersWindow.getEl().unmask();
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
				ID: realmID,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'AdminRealmMembers',
				SOAPFunction: 'getAdminRealmMembers',
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

	AdminRealmMembersWindow.show();
}


// Display remove form
function showAdminRealmMemberRemoveWindow(AdminRealmMembersWindow,id) {
	// Mask AdminRealmMembersWindow window
	AdminRealmMembersWindow.getEl().mask();

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
				uxAjaxRequest(AdminRealmMembersWindow,{
					params: {
						ID: id,
						SOAPUsername: globalConfig.soap.username,
						SOAPPassword: globalConfig.soap.password,
						SOAPAuthType: globalConfig.soap.authtype,
						SOAPModule: 'AdminRealmMembers',
						SOAPFunction: 'removeAdminRealmMember',
						SOAPParams: 'ID'
					},
					customSuccess: function() {
						var store = Ext.getCmp(AdminRealmMembersWindow.gridPanelID).getStore();
						store.load({
							params: {
								limit: 25
							}
						});
					}
				});


			// Unmask if user answered no
			} else {
				AdminRealmMembersWindow.getEl().unmask();
			}
		}
	});
}


// vim: ts=4
