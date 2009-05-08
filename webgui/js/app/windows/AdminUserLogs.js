

function showWiSPUserLogsWindow(wispUserID) {
	// Calculate dates we going to need
	var today = new Date();
	var firstOfMonth = today.getFirstDateOfMonth();
	var firstOfNext = today.getLastDateOfMonth().add(Date.DAY, 1);
	
	var wispUserLogsWindow = new Ext.ux.GenericGridWindow(
		// Window config
		{
			title: 'User Logs',
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
							vtype: 'daterange',
							format: 'Y-m-d',
							value: firstOfMonth,
							endDateField: 'before',
						},
						{
							id: 'before',
							name: 'before',
							width: 180,
							fieldLabel: 'To',
							vtype: 'daterange',
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
								var timestampFilter = gridFilters.getFilter('Timestamp');

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
					id: 'ID',
					header: "ID",
					hidden: true,
					dataIndex: 'ID'
				},
				{
					header: "Username",
					hidden: true,
					dataIndex: 'Username'
				},
				{
					header: "Status",
					sortable: true,
					hidden: true,
					dataIndex: 'Status'
				},
				{
					header: "Timestamp",
					sortable: true,
					dataIndex: 'Timestamp'
				},
				{
					header: "Session ID",
					hidden: true,
					dataIndex: 'AcctSessionID'
				},
				{
					header: "Session Time",
					dataIndex: 'AcctSessionTime'
				},
				{
					header: "NAS IP",
					hidden: true,
					dataIndex: 'NASIPAddress'
				},
				{
					header: "Port Type",
					hidden: true,
					dataIndex: 'NASPortType'
				},
				{
					header: "NAS Port",
					dataIndex: 'NASPort'
				},
				{
					header: "Called Station",
					hidden: true,
					dataIndex: 'CalledStationID'
				},
				{
					header: "Calling Station",
					sortable: true,
					dataIndex: 'CallingStationID'
				},
				{
					header: "NAS Xmit Rate",
					dataIndex: 'NASTransmitRate'
				},
				{
					header: "NAS Recv Rate",
					hidden: true,
					dataIndex: 'NASReceiveRate'
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
					header: "Last Update",
					hidden: true,
					dataIndex: 'LastAcctUpdate'
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
				SOAPUsername: globalConfig.soap.username,
				SOAPPassword: globalConfig.soap.password,
				SOAPAuthType: globalConfig.soap.authtype,
				SOAPModule: 'WiSPUsers',
				SOAPFunction: 'getWiSPUserLogs',
				SOAPParams: '0:UserID,__search',
				UserID: wispUserID
			}
		},
		// Filter config
		{
			filters: [
				{type: 'numeric',  dataIndex: 'ID'},
				{type: 'string',  dataIndex: 'Username'},
				{type: 'numeric',  dataIndex: 'Status'},
				{
					type: 'date',  
					dataIndex: 'Timestamp', 
					value: {
						after: firstOfMonth,
						before: firstOfNext
					}
				},

				{type: 'string',  dataIndex: 'AcctSessionID'},
				{type: 'numeric',  dataIndex: 'AcctSessionTime'},

				{type: 'string',  dataIndex: 'NASIPAddress'},
				{type: 'string',  dataIndex: 'NASPortType'},
				{type: 'string',  dataIndex: 'NASPort'},
				{type: 'string',  dataIndex: 'CalledStationID'},
				{type: 'string',  dataIndex: 'CallingStationID'},

				{type: 'string',  dataIndex: 'NASTransmitRate'},
				{type: 'string',  dataIndex: 'NASReceiveRate'},

				{type: 'string',  dataIndex: 'FramedIPAddress'},

				{type: 'date',  dataIndex: 'LastAcctUpdate'},

				{type: 'string',  dataIndex: 'ConnectTermReason'}
			]
		}
	);
	// Grab store
	var store = wispUserLogsWindow.getComponent('gridpanel').getStore();

	store.on('load',function() {
		var inputTotal = store.sum('AcctInputMbyte');
		var outputTotal = store.sum('AcctOutputMbyte');

		var userCap = 3000;
		var userTopups = 1000;
		
		// Total up into this ... 
		
		var userTotalAllowed = userCap + userTopups;
		var userUsage = inputTotal + outputTotal;
		var userLeft = userTotalAllowed - userUsage;

		var form = wispUserLogsWindow.getComponent('summary-form');
		var summaryTotal = form.getForm().findField('summaryTotal');

		summaryTotal.setValue(
				sprintf('Cap Total: %6d\nTopups   : %6d\n-----------------\n           %6d\n-----------------\nUsage    : %6d\n=================\nAvailable: %6d',userCap,userTopups,userTotalAllowed,userUsage,userLeft)
		);
	});
	wispUserLogsWindow.show();				
}