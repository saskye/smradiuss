/*
Admin Group Members
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


function showAdminGroupMembersWindow(groupID) {

	var AdminGroupMembersWindow = new Ext.ux.GenericGridWindow(
		// Window config
		{
			title: "Members",
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
					text:'Remove',
					tooltip:'Remove member',
					iconCls:'silk-user_delete',
					handler: function() {
						var selectedItem = Ext.getCmp(AdminGroupMembersWindow.gridPanelID).getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showAdminGroupMemberRemoveWindow(AdminGroupMembersWindow,selectedItem.data.ID);
						} else {
							AdminGroupMembersWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No member selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									AdminGroupMembersWindow.getEl().unmask();
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
				ID: groupID,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'AdminGroupMembers',
				SOAPFunction: 'getAdminGroupMembers',
				SOAPParams: 'ID,__search'
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

	AdminGroupMembersWindow.show();
}


// Display remove form
function showAdminGroupMemberRemoveWindow(AdminGroupMembersWindow,id) {
	// Mask AdminGroupMembersWindow window
	AdminGroupMembersWindow.getEl().mask();

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
				uxAjaxRequest(AdminGroupMembersWindow,{
					params: {
						ID: id,
						SOAPUsername: globalConfig.soap.username,
						SOAPPassword: globalConfig.soap.password,
						SOAPAuthType: globalConfig.soap.authtype,
						SOAPModule: 'AdminGroupMembers',
						SOAPFunction: 'removeAdminGroupMember',
						SOAPParams: 'ID'
					},
					customSuccess: function() {
						var store = Ext.getCmp(AdminGroupMembersWindow.gridPanelID).getStore();
						store.load({
							params: {
								limit: 25
							}
						});
					}
				});


			// Unmask if user answered no
			} else {
				AdminGroupMembersWindow.getEl().unmask();
			}
		}
	});
}


// vim: ts=4
