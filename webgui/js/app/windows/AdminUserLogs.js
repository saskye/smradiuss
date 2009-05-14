

function showAdminUserLogsWindow(id) {
	// Calculate dates we going to need
	var today = new Date();
	var firstOfMonth = today.getFirstDateOfMonth();
	var firstOfNext = today.getLastDateOfMonth().add(Date.DAY, 1);
	
	var adminUserLogsWindow = new Ext.ux.GenericGridWindow(
		// Window config
		{
			title: 'Logs',
			layout:'border',
			height: 480,
			width: 700,
			minHeight: 480,
			minWidth: 700,
			closable: true,
			plain: true,
			uxItems: [
				{
					xtype: 'form',
					id: 'search-form',
					title: 'Search',
					region: 'west',
					border: true,
					frame: true,
					defaultType: 'datefield',
					height: 180,
					width: 300,
					labelWidth: 100,
					items: [
						{
							id: 'after',
							name: 'after',
							width: 180,
							fieldLabel: 'From',
//							vtype: 'daterange',
							format: 'Y-m-d',
							value: firstOfMonth,
							endDateField: 'before',
						},
						{
							id: 'before',
							name: 'before',
							width: 180,
							fieldLabel: 'To',
//							vtype: 'daterange',
							format: 'Y-m-d',
							value: firstOfNext,
							startDateField: 'after'
						}
					],
					buttons: [
						{
							text: 'Search',
							id: 'formbtn',
							handler: function() {
								// Pull in window, grid & form	
								var mainWindow = this.ownerCt.ownerCt;
								var grid = mainWindow.getComponent('gridpanel');
								var form = mainWindow.getComponent('search-form');

								// Grab store
								var store = grid.getStore();

								// Grab timestamp filter
								var gridFilters = grid.filters;
								var timestampFilter = gridFilters.getFilter('EventTimestamp');

								// Grab	form fields
								var afterField = form.getForm().findField('after');
								var beforeField = form.getForm().findField('before');

								// Set filter values from form
								timestampFilter.setValue({
									after: afterField.getValue(),
									before: beforeField.getValue()
								});

								// Trigger store reload
								store.reload();
							}
						}
					],
					buttonAlign: 'center'
				},
				{
					xtype: 'form',
					id: 'summary-form',
					region: 'center',
					split: true,
					border: true,
					autoScroll: true,
					defaultType: 'textarea',
					height: 180,
					width: 400,
					labelWidth: 80,
					items: [
						{
							id: 'summaryTotal',
							name: 'summaryTotal',
							readOnly: true,
							height: 135,
							width: 200,
							fieldLabel: 'Summary',
							fieldClass: 'font-family: monospace; font-size: 10px;',
							value: ''
						}
					]					
				}
			]
		},
		// Grid config
		{
			region: 'south',
			width: 700,
			border: true,
			tbar: [
				{
					text: 'Add Port Lock',
					tooltip: 'Add port lock',
					iconCls: 'add'
				}	
			],
			// Column model
			colModel: new Ext.grid.ColumnModel([
				{
					header: "ID",
					hidden: true,
					dataIndex: 'ID'
				},
				{
					header: "Timestamp",
					sortable: true,
					dataIndex: 'EventTimestamp'
				},
				{
					header: "Status",
					sortable: true,
					hidden: true,
					dataIndex: 'AcctStatusType'
				},
				{
					header: "Service Type",
					sortable: true,
					dataIndex: 'ServiceType'
				},
				{
					header: "Framed Protocol",
					sortable: true,
					dataIndex: 'FramedProtocol'
				},
				{
					header: "NAS Port Type",
					hidden: true,
					dataIndex: 'NASPortType'
				},
				{
					header: "Calling Station",
					sortable: true,
					dataIndex: 'CallingStationID'
				},
				{
					header: "Called Station",
					hidden: true,
					dataIndex: 'CalledStationID'
				},
				{
					header: "Session ID",
					hidden: true,
					dataIndex: 'AcctSessionID'
				},
				{
					header: "IP Address",
					hidden: true,
					dataIndex: 'FramedIPAddress'
				},
				{
					header: "Input Mbyte",
					dataIndex: 'AcctInputMbyte'
				},
				{
					header: "Output Mbyte",
					dataIndex: 'AcctOutputMbyte'
				},
				{
					header: "Term. Reason",
					dataIndex: 'ConnectTermReason'
				}
			])
		},
		// Store config
		{
			baseParams: {
				ID: id,
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'AdminUserLogs',
				SOAPFunction: 'getAdminUserLogs',
				SOAPParams: 'ID,__search'
			}
		},
		// Filter config
		{
			filters: [
				{type: 'numeric', dataIndex: 'ID'},
				/*{
					type: 'date',  
					dataIndex: 'EventTimestamp', 
					value: {
						after: firstOfMonth,
						before: firstOfNext
					}
				},*/
				{type: 'numeric',  dataIndex: 'AcctStatusType'},
				{type: 'numeric',  dataIndex: 'ServiceType'},
				{type: 'numeric',  dataIndex: 'FramedProtocol'},
				{type: 'numeric',  dataIndex: 'NASPortType'},
				{type: 'string',  dataIndex: 'NASPortID'},
				{type: 'string',  dataIndex: 'CallingStationID'},
				{type: 'string',  dataIndex: 'CalledStationID'},
				{type: 'string',  dataIndex: 'AcctSessionID'},
				{type: 'string',  dataIndex: 'FramedIPAddress'},
				{type: 'numeric',  dataIndex: 'AcctInputMbyte'},
				{type: 'numeric',  dataIndex: 'AcctOutputMbyte'},
				{type: 'string',  dataIndex: 'ConnectTermReason'}
			]
		}
	);
	// Grab store
	var store = adminUserLogsWindow.getComponent('gridpanel').getStore();

	store.on('load',function() {
		var inputTotal = store.sum('AcctInputMbyte');
		var outputTotal = store.sum('AcctOutputMbyte');

		var userCap = 3000;
		var userTopups = 1000;
		
		// Total up into this ... 
		
		var userTotalAllowed = userCap + userTopups;
		var userUsage = inputTotal + outputTotal;
		var userLeft = userTotalAllowed - userUsage;

		var form = adminUserLogsWindow.getComponent('summary-form');
		var summaryTotal = form.getForm().findField('summaryTotal');

		summaryTotal.setValue(
				sprintf('Cap Total: %6d\nTopups   : %6d\n-----------------\n           %6d\n-----------------\nUsage    : %6d\n=================\nAvailable: %6d',userCap,userTopups,userTotalAllowed,userUsage,userLeft)
		);
	});
	adminUserLogsWindow.show();				
}
