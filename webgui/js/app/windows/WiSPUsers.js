/*
WiSP Users
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


function showWiSPUserWindow() {

	var WiSPUserWindow = new Ext.ux.GenericGridWindow(
		// Window config
		{
			title: "Users",
			iconCls: 'silk-user',

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
					tooltip:'Add user',
					iconCls:'silk-user_add',
					handler: function() {
						showWiSPUserAddEditWindow(WiSPUserWindow);
					}
				},
				'-',
				{
					text:'Edit',
					tooltip:'Edit user',
					iconCls:'silk-user_edit',
					handler: function() {
						var selectedItem = Ext.getCmp(WiSPUserWindow.gridPanelID).getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showWiSPUserAddEditWindow(WiSPUserWindow,selectedItem.data.ID);
						} else {
							WiSPUserWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No user selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									WiSPUserWindow.getEl().unmask();
								}
							});
						}
					}
				},
				'-',
				{
					text:'Remove',
					tooltip:'Remove user',
					iconCls:'silk-user_delete',
					handler: function() {
						var selectedItem = Ext.getCmp(WiSPUserWindow.gridPanelID).getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showWiSPUserRemoveWindow(WiSPUserWindow,selectedItem.data.ID);
						} else {
							WiSPUserWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No user selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									WiSPUserWindow.getEl().unmask();
								}
							});
						}
					}
				},
				'-',
				{
					text:'Logs',
					tooltip:'User logs',
					iconCls: 'silk-page_white_text',
					handler: function() {
						var selectedItem = Ext.getCmp(WiSPUserWindow.gridPanelID).getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showWiSPUserLogsWindow(selectedItem.data.ID);
						} else {
							WiSPUserWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No user selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									WiSPUserWindow.getEl().unmask();
								}
							});
						}
					}
				},
				'-',
				{
					text:'Topups',
					tooltip:'User topups',
					iconCls:'silk-chart_bar',
					handler: function() {
						var selectedItem = Ext.getCmp(WiSPUserWindow.gridPanelID).getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showWiSPUserTopupsWindow(selectedItem.data.ID);
						} else {
							WiSPUserWindow.getEl().mask();

							// Display error
							Ext.Msg.show({
								title: "Nothing selected",
								msg: "No user selected",
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.Msg.CANCEL,
								modal: false,
								fn: function() {
									WiSPUserWindow.getEl().unmask();
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
					header: "UserID",
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
				},
				{
					header: "First Name",
					sortable: true,
					dataIndex: 'Firstname'
				},
				{
					header: "Last Name",
					sortable: true,
					dataIndex: 'Lastname'
				},
				{
					header: "Email",
					sortable: true,
					dataIndex: 'Email'
				},
				{
					header: "Phone",
					sortable: true,
					dataIndex: 'Phone'
				}
			]),
			autoExpandColumn: 'Username'
		},
		// Store config
		{
			baseParams: {
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'WiSPUsers',
				SOAPFunction: 'getWiSPUsers',
				SOAPParams: '__null,__search'
			}
		},
		// Filter config
		{
			filters: [
				{type: 'numeric',  dataIndex: 'ID'},
				{type: 'string',  dataIndex: 'Username'},
				{type: 'boolean',  dataIndex: 'Disabled'},
				{type: 'string',  dataIndex: 'Firstname'},
				{type: 'string',  dataIndex: 'Lastname'},
				{type: 'string',  dataIndex: 'Email'},
				{type: 'string',  dataIndex: 'Phone'}
			]
		}
	);

	WiSPUserWindow.show();
}


// Display edit/add form
function showWiSPUserAddEditWindow(WiSPUserWindow,id) {

	var submitAjaxConfig;
	var editMode;
	var icon;

	// Arrays for removed items
	var RemovedAttributes = new Array();
	var RemovedGroups = new Array();

	// To identify newly inserted rows
	var attributeInsertID = -1;
	var groupInsertID = -1;

	// Attribute record that can be added to below store
	var attributeRecord = Ext.data.Record.create([
		{name: 'ID'},
		{name: 'Name'},
		{name: 'Operator'},
		{name: 'Value'},
		{name: 'Modifier'}
	]);

	// Attribute store
	var attributeStore;
	// If this is an update we need to pull in record
	if (id) {
		attributeStore = new Ext.ux.JsonStore({
			pruneModifiedRecords: true,
			baseParams: {
				ID: id,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'WiSPUsers',
				SOAPFunction: 'getWiSPUserAttributes',
				SOAPParams: 'ID'
			}
		});
	} else {
		attributeStore = new Ext.data.SimpleStore({
			pruneModifiedRecords: true,
			fields: ['ID', 'Name', 'Operator', 'Value', 'Modifer']
		});
	}

	// Group record that can be added to below store
	var groupRecord = Ext.data.Record.create([
		{name: 'Name'}
	]);

	// Group store
	var groupStore;
	// If this is an update we need to pull in record
	if (id) {
		groupStore = new Ext.ux.JsonStore({
			pruneModifiedRecords: true,
			baseParams: {
				ID: id,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'WiSPUsers',
				SOAPFunction: 'getWiSPUserGroups',
				SOAPParams: 'ID'
			}
		});
	} else {
		groupStore = new Ext.data.SimpleStore({
			pruneModifiedRecords: true,
			fields: [
				'Name'
			]
		});
	}

	// We doing an update
	if (id) {
		icon = 'silk-user_edit';
		submitAjaxConfig = {
			params: {
				ID: id,
				SOAPFunction: 'updateWiSPUser',
				SOAPParams:
					'0:ID,'+
					'0:Username,'+
					'0:Password,'+
					'0:Firstname,'+
					'0:Lastname,'+
					'0:Phone,'+
					'0:LocationID,'+
					'0:Attributes,'+
					'0:Groups,'+
					'0:Email,'+
					'0:RGroups,'+
					'0:RAttributes'
			},
			onSuccess: function() {
				var store = Ext.getCmp(WiSPUserWindow.gridPanelID).getStore();
				store.load({
					params: {
						limit: 25
					}
				});
			},

			hook: function() {
				// Get modified attribute records
				var attributes = attributeStore.getModifiedRecords();
				// Get modified group records
				var groups = groupStore.getModifiedRecords();

				var ret = { };
				// Set attributes we will be adding
				for (var i = 0, len = attributes.length; i < len; i++) {
					var attribute = attributes[i];

					// Safe to add this attribute
					ret['Attributes['+i+'][ID]'] = attribute.get('ID');
					ret['Attributes['+i+'][Name]'] = attribute.get('Name');
					ret['Attributes['+i+'][Operator]'] = attribute.get('Operator');
					ret['Attributes['+i+'][Value]'] = attribute.get('Value');
					ret['Attributes['+i+'][Modifier]'] = attribute.get('Modifier');
				}
				// Set groups we will be adding
				for (var i = 0, len = groups.length; i < len; i++) {
					var group = groups[i];

					// Safe to add this attribute
					ret['Groups['+i+'][Name]'] = group.get('Name');
				}

				// Add removed attributes
				if ((id) && (RemovedAttributes.length > 0)) {
					var c = 0;
					var len = RemovedAttributes.length;
					for (var i = 0; i < len; i++) {
						// If this is a new add then the user has no attributes
						if (RemovedAttributes[i] >= 0) {
							ret['RAttributes['+c+']'] = RemovedAttributes[i];
							c++;
						}
					}
				}

				// Add removed groups
				if ((id) && (RemovedGroups.length > 0)) {
					var c = 0;
					var len = RemovedGroups.length;
					for (var i = 0; i < len; i++) {
						// If this is a new add then the user has no attributes
						if (RemovedGroups[i] >= 0) {
							ret['RGroups['+c+']'] = RemovedGroups[i];
							c++;
						}
					}
				}

				return ret;
			}
		};
	// We doing an Add
	} else {
		icon = 'silk-user_add';
		submitAjaxConfig = {
			params: {
				SOAPFunction: 'createWiSPUser',
				SOAPParams:
					'0:Username,'+
					'0:Password,'+
					'0:Firstname,'+
					'0:Lastname,'+
					'0:Phone,'+
					'0:Email,'+
					'0:LocationID,'+
					'0:Attributes,'+
					'0:Groups,'+
					'0:Number,'+
					'0:Prefix'
			},
			onSuccess: function() {
				var store = Ext.getCmp(WiSPUserWindow.gridPanelID).getStore();
				store.load({
					params: {
						limit: 25
					}
				});
			},

			hook: function() {
				// Get modified attribute records
				var attributes = attributeStore.getModifiedRecords();
				// Get modified group records
				var groups = groupStore.getModifiedRecords();

				var ret = { };
				// Set attributes we will be adding
				for (var i = 0, len = attributes.length; i < len; i++) {
					var attribute = attributes[i];

					// Safe to add this attribute
					ret['Attributes['+i+'][ID]'] = attribute.get('ID');
					ret['Attributes['+i+'][Name]'] = attribute.get('Name');
					ret['Attributes['+i+'][Operator]'] = attribute.get('Operator');
					ret['Attributes['+i+'][Value]'] = attribute.get('Value');
					ret['Attributes['+i+'][Modifier]'] = attribute.get('Modifier');
				}
				// Set groups we will be adding
				for (var i = 0, len = groups.length; i < len; i++) {
					var group = groups[i];

					// Safe to add this attribute
					ret['Groups['+i+'][Name]'] = group.get('Name');
				}
				return ret;
			}
		};
	}


	// Build the attribute editor grid
	var attributeEditor = new Ext.grid.EditorGridPanel({
		plain: true,
		autoHeight: true,

		// Set row selection model
		selModel: new Ext.grid.RowSelectionModel({
			singleSelect: true
		}),

		// Inline toolbars
		tbar: [
			{
				text:'Add',
				tooltip:'Add attribute',
				iconCls:'silk-table_add',
				handler: function() {
					var newAttrStoreRecord = new attributeRecord({
						ID: attributeInsertID,
						Name: '',
						Operator: '',
						Value: '',
						Modifier: ''
					});
					attributeStore.insert(0,newAttrStoreRecord);
					attributeInsertID -= 1;
				}
			},
			'-',
			{
				text:'Remove',
				tooltip:'Remove attribute',
				iconCls:'silk-table_delete',
				handler: function() {
					var selectedItem = attributeEditor.getSelectionModel().getSelected();

					// Check if we have selected item
					if (selectedItem) {
						// Get selected item value
						var attributeID = selectedItem.get('ID');

						// Remove selected
						attributeStore.remove(selectedItem);

						// Add to list of removed attributes
						RemovedAttributes.push(attributeID);
					} else {
						wispUserFormWindow.getEl().mask();

						// Display error
						Ext.Msg.show({
							title: "Nothing selected",
							msg: "No attribute selected",
							icon: Ext.MessageBox.ERROR,
							buttons: Ext.Msg.CANCEL,
							modal: false,
							fn: function() {
								wispUserFormWindow.getEl().unmask();
							}
						});
					}
				}
			}
		],

		cm: new Ext.grid.ColumnModel([
			{
				id: 'ID',
				header: 'ID',
				dataIndex: 'ID',
				hidden: true,
				width: 30
			},
			{
				id: 'Name',
				header: 'Name',
				dataIndex: 'Name',
				width: 150,
				editor: new Ext.form.ComboBox({
					allowBlank: false,
					mode: 'local',
					store: [
						[ 'SMRadius-Capping-Traffic-Limit', 'Traffic Limit' ],
						[ 'SMRadius-Capping-Uptime-Limit', 'Uptime Limit' ],
						[ 'Framed-IP-Address', 'IP Address' ],
						[ 'Calling-Station-Id', 'MAC Address' ]
					],
					triggerAction: 'all',
					editable: false
				})
			},
			{
				id: 'Operator',
				header: 'Operator',
				dataIndex: 'Operator',
				width: 300,
				editor: new Ext.form.ComboBox({
					allowBlank: false,
					mode: 'local',
					store: [
						[ '=', 'Add as reply if unique' ], 
						[ ':=', 'Set configuration value'  ],
						[ '==', 'Match value in request' ], 
						[ '+=', 'Add reply and set configuration' ],
						[ '!=', 'Inverse match value in request' ],
						[ '<', 'Match less-than value in request' ],
						[ '>', 'Match greater-than value in request' ],
						[ '<=', 'Match less-than or equal value in request' ],
						[ '>=', 'Match greater-than or equal value in request' ],
						[ '=~', 'Match string containing regex in request' ],
						[ '!~', 'Match string not containing regex in request' ],
						[ '=*', 'Match if attribute is defined in request' ],
						[ '!*', 'Match if attribute is not defined in request' ],
						[ '||==', 'Match any of these values in request' ]
					],
					triggerAction: 'all',
					editable: true
				})
			},
			{
				id: 'Value',
				header: 'Value',
				dataIndex: 'Value',
				width: 100,
				editor: new Ext.form.TextField({
					allowBlank: false
				})
			},
			{
				id: 'Modifier',
				header: 'Modifier',
				dataIndex: 'Modifier',
				width: 80,
				editor: new Ext.form.ComboBox({
					allowBlank: false,
					mode: 'local',
					store: [ 
						[ 'Seconds', 'Seconds' ],
						[ 'Minutes', 'Minutes' ],
						[ 'Hours', 'Hours' ],
						[ 'Days', 'Days' ],
						[ 'Weeks', 'Weeks' ],
						[ 'Months', 'Months' ],
						[ 'MBytes', 'MBytes' ],
						[ 'GBytes', 'GBytes' ],
						[ 'TBytes', 'TBytes' ]
					],
					triggerAction: 'all',
					editable: true
				})
			}
		]),
		store: attributeStore
	});

	// Build the group editor grid
	var groupEditor = new Ext.grid.EditorGridPanel({
		plain: true,
		autoHeight: true,

		// Set row selection model
		selModel: new Ext.grid.RowSelectionModel({
			singleSelect: true
		}),
		
		// Inline toolbars
		tbar: [
			{
				text:'Add',
				tooltip:'Add group',
				iconCls:'silk-group_add',
				handler: function() {
					var newGroupStoreRecord = new groupRecord({
						ID: groupInsertID,
						Name: ''
					});
					groupStore.insert(0,newGroupStoreRecord);
					groupInsertID -= 1;
				}
			},
			'-',
			{
				text:'Remove',
				tooltip:'Remove group',
				iconCls:'silk-group_delete',
				handler: function() {
					var selectedItem = groupEditor.getSelectionModel().getSelected();

					// Check if we have selected item
					if (selectedItem) {
						// Get selected item value
						var groupID = selectedItem.get('ID');

						// Remove selected
						groupStore.remove(selectedItem);

						// Add to our removed groups hash
						RemovedGroups.push(groupID);
					} else {
						wispUserFormWindow.getEl().mask();

						// Display error
						Ext.Msg.show({
							title: "Nothing selected",
							msg: "No group selected",
							icon: Ext.MessageBox.ERROR,
							buttons: Ext.Msg.CANCEL,
							modal: false,
							fn: function() {
								wispUserFormWindow.getEl().unmask();
							}
						});
					}
				}
			}
		],

		cm: new Ext.grid.ColumnModel([
			{
				id: 'ID',
				header: 'ID',
				dataIndex: 'ID',
				hidden: true,
				width: 30
			},
			{
				id: 'Name',
				header: 'Name',
				dataIndex: 'Name',
				width: 150,
				editor: new Ext.form.ComboBox({
					allowBlank: false,
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
					forceSelection: true,
					triggerAction: 'all',
					editable: false
				})
			}
		]),
		store: groupStore
	});

	// Create window
	var wispUserFormWindow = new Ext.ux.GenericFormWindow(
		// Window config
		{
			title: "User Information",
			iconCls: icon,

			width: 700,
			height: 342,

			minWidth: 700,
			minHeight: 342
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
					fieldLabel: 'Username',
					name: 'Username',
					vtype: 'usernameRadius',
					maskRe: usernameRadiusPartRe,
					allowBlank: true
				},
				{
					fieldLabel: 'Password',
					name: 'Password',
					allowBlank: true
				},
				{
					xtype: 'tabpanel',
					plain: 'true',
					deferredRender: false, // Load all panels!
					activeTab: 0,
					height: 200,
					maxHeight: 200,
					defaults: {
						layout: 'form',
						bodyStyle: 'padding: 10px;'
					},

					items: [
						{
							title: 'Personal',
							iconCls: 'silk-user_comment',
							layout: 'form',
							defaultType: 'textfield',
							items: [
								{
									fieldLabel: 'First Name',
									name: 'Firstname',
									vtype: 'usernamePart',
									allowBlank: true
								},
								{
									fieldLabel: 'Last Name',
									name: 'Lastname',
									vtype: 'usernamePart',
									allowBlank: true
								},
								{
									fieldLabel: 'Phone',
									name: 'Phone',
									vtype: 'number',
									allowBlank: true
								},
								{
									fieldLabel: 'Email',
									name: 'Email',
									allowBlank: true
								},
								{
									xtype: 'combo',
									fieldLabel: 'Location',
									name: 'Location',
									allowBlank: true,
									width: 140,

									store: new Ext.ux.JsonStore({
										sortInfo: { field: "Name", direction: "ASC" },
										baseParams: {
											SOAPUsername: globalConfig.soap.username,
											SOAPPassword: globalConfig.soap.password,
											SOAPAuthType: globalConfig.soap.authtype,
											SOAPModule: 'WiSPUsers',
											SOAPFunction: 'getWiSPLocations',
											SOAPParams: '__null,__search'
										}
									}),
									displayField: 'Name',
									valueField: 'ID',
									hiddenName: 'LocationID',
									forceSelection: true,
									triggerAction: 'all',
									editable: false
								}
							]
						},
						{
							title: 'Groups',
							iconCls: 'silk-group',
							layout: 'form',
							autoScroll: true,
							defaultType: 'textfield',
							items: [
								groupEditor
							]
						},
						{
							title: 'Attributes',
							iconCls: 'silk-table',
							layout: 'form',
							autoScroll: true,
							defaultType: 'textfield',
							items: [
								attributeEditor
							]
						},
						{
							title: 'Add Many',
							iconCls: 'silk-user_suit',
							layout: 'form',
							defaultType: 'textfield',
							items: [
								{
									fieldLabel: 'Prefix',
									name: 'Prefix',
									allowBlank: true
								},
								{
									fieldLabel: 'Number',
									name: 'Number',
									vtype: 'number',
									allowBlank: true
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
	wispUserFormWindow.show();

	if (id) {
		Ext.getCmp(wispUserFormWindow.formPanelID).load({
			params: {
				ID: id,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'WiSPUsers',
				SOAPFunction: 'getWiSPUser',
				SOAPParams: 'ID'
			}
		});
		attributeStore.load();
		groupStore.load();
	}
}




// Display remove form
function showWiSPUserRemoveWindow(WiSPUserWindow,id) {
	// Mask WiSPUserWindow window
	WiSPUserWindow.getEl().mask();

	// Display remove confirm window
	Ext.Msg.show({
		title: "Confirm removal",
		msg: "Are you very sure you wish to remove this user?",
		icon: Ext.MessageBox.ERROR,
		buttons: Ext.Msg.YESNO,
		modal: false,
		fn: function(buttonId,text) {
			// Check if user clicked on 'yes' button
			if (buttonId == 'yes') {

				// Do ajax request
				uxAjaxRequest(WiSPUserWindow,{
					params: {
						ID: id,
						SOAPUsername: globalConfig.soap.username,
						SOAPPassword: globalConfig.soap.password,
						SOAPAuthType: globalConfig.soap.authtype,
						SOAPModule: 'WiSPUsers',
						SOAPFunction: 'removeWiSPUser',
						SOAPParams: 'ID'
					},
					customSuccess: function() {
						var store = Ext.getCmp(WiSPUserWindow.gridPanelID).getStore();
						store.load({
							params: {
								limit: 25
							}
						});
					}
				});


			// Unmask if user answered no
			} else {
				WiSPUserWindow.getEl().unmask();
			}
		}
	});
}











// vim: ts=4
