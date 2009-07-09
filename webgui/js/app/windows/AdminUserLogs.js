

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
					width: 320,
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
							endDateField: 'before'
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
							height: 139,
							width: 275,
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
					header: "Session Uptime",
					dataIndex: 'AcctSessionTime'
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
				{
					type: 'date',  
					format: 'Y-m-d H:i:s',
					dataIndex: 'EventTimestamp', 
					value: {
						after: firstOfMonth,
						before: firstOfNext
					}
				},
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
				{type: 'numeric',  dataIndex: 'AcctSessionTime'},
				{type: 'string',  dataIndex: 'ConnectTermReason'}
			]
		}
	);
	// Grab store
	var store = adminUserLogsWindow.getComponent('gridpanel').getStore();

	store.on('load',function() {
		var inputTotal = store.sum('AcctInputMbyte');
		var outputTotal = store.sum('AcctOutputMbyte');
		var uptimeTotal = store.sum('AcctSessionTime');

		var searchForm = adminUserLogsWindow.getComponent('search-form');
		var afterField = (searchForm.getForm().findField('after')).getValue();
		var beforeField = (searchForm.getForm().findField('before')).getValue();

		var trafficCap;
		var uptimeCap;
		var trafficTopups;
		var uptimeTopups;

		var response;

		// Mask parent window
		adminUserLogsWindow.getEl().mask();

		uxAjaxRequest(
			adminUserLogsWindow,
			{
				params: {
						From: afterField,
						To: beforeField,
						ID: id,
						SOAPUsername: globalConfig.soap.username,
						SOAPPassword: globalConfig.soap.password,
						SOAPAuthType: globalConfig.soap.authtype,
						SOAPModule: 'AdminUserLogs',
						SOAPFunction: 'getAdminUserLogsSummary',
						SOAPParams: '0:ID,0:From,0:To'
					},
			customSuccess: function (result) { 
				response = Ext.decode(result.responseText);

				trafficCap = response.data.trafficCap;
				uptimeCap = response.data.uptimeCap;
				trafficTopups = response.data.trafficTopups;
				uptimeTopups = response.data.uptimeTopups;

				// Total up traffic 
				var trafficTotalAllowed;
				if (trafficCap < 0) {
					trafficTotalAllowed = trafficTopups;
				} else {
					trafficTotalAllowed = trafficCap + trafficTopups;
				}
				var trafficUsage = inputTotal + outputTotal;
				var trafficRemaining = trafficTotalAllowed - trafficUsage;

				var form = adminUserLogsWindow.getComponent('summary-form');
				var summaryTotal = form.getForm().findField('summaryTotal');

				// Format string before printing
				var summaryString = '';
				if (trafficCap == -1) {
					trafficCap = 'Prepaid';
					summaryString += sprintf(
						'Traffic Cap: %s Traffic Topups: %d\n------------------------------------\n'+
						'Allowed: %d Used: %d\n-------------------------------\nRemaining: %d',
						trafficCap,trafficTopups,trafficTotalAllowed,trafficUsage,trafficRemaining
					);

				} else if (trafficCap == 0) {
					summaryString += sprintf(
						'Traffic Cap: Uncapped\n---------------------------------\nUsed: %d',
						trafficUsage
					);

				} else {
					summaryString += sprintf(
						'Traffic Cap: %d Traffic Topups: %d\n------------------------------------\n'+
						'Allowed: %d Used: %d\n-------------------------------\nRemaining: %d',
						trafficCap,trafficTopups,trafficTotalAllowed,trafficUsage,trafficRemaining
					);
				}

alert(summaryString);
				summaryTotal.setValue(summaryString);
			},
			failure: function (result) { 
				Ext.MessageBox.alert('Failed', 'Couldn\'t fetch data: '+result.date); 
			},
		});
		
	});
	adminUserLogsWindow.show();				
}

// vim: ts=4
