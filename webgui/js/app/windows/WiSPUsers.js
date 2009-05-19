

function showWiSPUserWindow() {

	var WiSPUserWindow = new Ext.ux.GenericGridWindow(
		// Window config
		{
			title: "Users",
			
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
					iconCls:'add',
					handler: function() {
						showWiSPUserAddEditWindow();
					}
				}, 
				'-', 
				{
					text:'Edit',
					tooltip:'Edit user',
					iconCls:'option',
					handler: function() {
						var selectedItem = WiSPUserWindow.getComponent('gridpanel').getSelectionModel().getSelected();
						// Check if we have selected item
						if (selectedItem) {
							// If so display window
							showWiSPUserAddEditWindow(selectedItem.data.ID);
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
					iconCls:'remove',
					handler: function() {
						var selectedItem = WiSPUserWindow.getComponent('gridpanel').getSelectionModel().getSelected();
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
					iconCls:'logs',
					handler: function() {
						var selectedItem = WiSPUserWindow.getComponent('gridpanel').getSelectionModel().getSelected();
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
function showWiSPUserAddEditWindow(id) {

	var submitAjaxConfig;
	var editMode;


	// Attribute store
	var attributeStore;
	attributeStore = new Ext.data.SimpleStore({
		fields: [
			'name', 'operator', 'value', 'modifier'
		],
	});
	// Attribute record that can be added to above store
	var attributeRecord = Ext.data.Record.create([
		{name: 'name'},
		{name: 'operator'},
		{name: 'value'},
		{name: 'modifier'}
	]);

	// We doing an update
	if (id) {
		submitAjaxConfig = {
			ID: id,
			SOAPFunction: 'updateWiSPUser',
			SOAPParams: 
				'0:ID,'+
				'0:Username,'+
				'0:Password,'+
				'0:Firstname,'+
				'0:Lastname,'+
				'0:Phone,'+
				'0:Email,'+
				'0:MACAddress,'+
				'0:IPAddress,'+
				'0:Datalimit,'+
				'0:Uptimelimit'
		};

	// We doing an Add
	} else {
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
					'0:Attributes'
			},

			hook: function() {
				// Get modified records
				var attributes = attributeStore.getModifiedRecords();

				var ret = { };
				// Loop and add to our hash
        		for(var i = 0, len = attributes.length; i < len; i++){
					var attribute = attributes[i];
					ret['Attributes['+i+'][Name]'] = attribute.get('name');
					ret['Attributes['+i+'][Operator]'] = attribute.get('operator');
					ret['Attributes['+i+'][Value]'] = attribute.get('value');
					ret['Attributes['+i+'][Modifier]'] = attribute.get('modifier');
		        }

				return ret;
			}
		};
	}


	// Build the attribute editor grid
	var attributeEditor = new Ext.grid.EditorGridPanel({
		plain: true,
		height: 150,
		autoScroll: true,

		// Set row selection model
		selModel: new Ext.grid.RowSelectionModel({
			singleSelect: true
		}),
		
		// Inline toolbars
		tbar: [
			{
				text:'Add',
				tooltip:'Add attribute',
				iconCls:'add',
				handler: function() {
					var newAttrStoreRecord = new attributeRecord({
						name: '',
						operator: '',
						value: '',
						modifier: ''
					});
					attributeStore.insert(0,newAttrStoreRecord);
				}
			}, 
			'-', 
			{
				text:'Remove',
				tooltip:'Remove attribute',
				iconCls:'remove',
				handler: function() {
					var selectedItem = attributeEditor.getSelectionModel().getSelected();

					// Check if we have selected item
					if (selectedItem) {
						// If so remove
						attributeStore.remove(selectedItem);

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
			},
		],

		cm: new Ext.grid.ColumnModel([
			{
				id: 'name',
				header: 'Name',
				dataIndex: 'name',
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
					editable: false,
				})
			},
			{
				id: 'operator',
				header: 'Operator',
				dataIndex: 'operator',
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
					editable: true,
				})
			},
			{
				id: 'value',
				header: 'Value',
				dataIndex: 'value',
				width: 100,
				editor: new Ext.form.TextField({
					allowBlank: false,
				})
			},
			{
				id: 'modifier',
				header: 'Modifier',
				dataIndex: 'modifier',
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
						[ 'TBytes', 'TBytes' ],
					],
					triggerAction: 'all',
					editable: true,
				})
			},
		]),
		store: attributeStore
	});


	// Create window
	var wispUserFormWindow = new Ext.ux.GenericFormWindow(
		// Window config
		{
			title: "User Information",

			width: 700,
			height: 392,

			minWidth: 700,
			minHeight: 392
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
					vtype: 'usernamePart',
					maskRe: usernamePartRe,
					allowBlank: false,
				},
				{
					fieldLabel: 'Password',
					name: 'Password',
					vtype: 'usernamePart',
					maskRe: usernamePartRe,
					allowBlank: false,
				},
				{
					xtype: 'tabpanel',
					plain: 'true',
					deferredRender: false, // Load all panels!
					activeTab: 0,
					height: 250,
					defaults: {
						layout: 'form',
						bodyStyle: 'padding: 10px;'
					},
					
					items: [
						{
							title: 'Personal',
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
								}
							]
						},
						{
							title: 'Attributes',
							layout: 'form',
							defaultType: 'textfield',
							items: [
								attributeEditor
							]
						},
					]
				},
			],
		},
		// Submit button config
		submitAjaxConfig
	);
	wispUserFormWindow.show();

	if (id) {
		wispUserFormWindow.getComponent('formpanel').load({
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
	}
}




// Display edit/add form
function showWiSPUserRemoveWindow(parent,id) {
	// Mask parent window
	parent.getEl().mask();

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
				uxAjaxRequest(parent,{
					params: {
						ID: id,
						SOAPUsername: globalConfig.soap.username,
						SOAPPassword: globalConfig.soap.password,
						SOAPAuthType: globalConfig.soap.authtype,
						SOAPModule: 'WiSPUsers',
						SOAPFunction: 'removeWiSPUser',
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










