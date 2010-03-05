/*
Admin User Logs
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


function showAdminUserLogsWindow(id) {
	// Calculate dates we going to need
	var today = new Date();
	var firstOfMonth = today.getFirstDateOfMonth();
	var firstOfNext = today.getLastDateOfMonth().add(Date.DAY, 1);

	var formID = Ext.id();
	var formPeriodKeyID = Ext.id();
	var formSearchButtonID = Ext.id();

	var summaryFormID = Ext.id();
	var summaryTotalID = Ext.id();

	var currentPeriod = today.format('Y-m');

	var adminUserLogsWindow = new Ext.ux.GenericGridWindow(
		// Window config
		{
			title: 'Logs',
			iconCls: 'silk-page_white_text',
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
					id: formID,
					title: 'Search',
					region: 'west',
					border: true,
					frame: true,
					height: 180,
					width: 320,
					labelWidth: 100,
					items: [
						{
							id: formPeriodKeyID,
							xtype: 'textfield',
							name: 'periodkey',
							regex: /^\d{4}\-(0[1-9]|1(0|1|2))$/,
							regexText: 'Please enter month in the format: YYYY-MM',
							height: 25,
							width: 100,
							fieldLabel: 'Period',
							value: currentPeriod
						}
					],
					buttons: [
						{
							text: 'Search',
							id: formSearchButtonID,
							handler: function() {
								// Pull in window, grid & form	
								var grid = Ext.getCmp(adminUserLogsWindow.gridPanelID);

								// Grab store
								var store = grid.getStore();

								// Grab timestamp filter
								var gridFilters = grid.filters;
								var timestampFilter = gridFilters.getFilter('EventTimestamp');

								// Grab	form field
								var periodKeyField = Ext.getCmp(formPeriodKeyID);
								if (periodKeyField.isValid()) {
									var periodKeyValue = periodKeyField.getValue();

									// Convert our periodKey into DateTime values
									var dtSearchStart = Date.parseDate(periodKeyValue+'-01','Y-m-d');
									var dtSearchEnd = dtSearchStart.add(Date.MONTH,1);

									// Set filter values from form
									timestampFilter.setValue({
										after: dtSearchStart,
										before: dtSearchEnd
									});

									// Trigger store reload
									store.reload();
								}
							}
						}
					],
					buttonAlign: 'center'
				},
				{
					xtype: 'form',
					id: summaryFormID,
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
							id: summaryTotalID,
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
					dataIndex: 'AcctInput',
					renderer: renderUsageFloat
				},
				{
					header: "Output Mbyte",
					dataIndex: 'AcctOutput',
					renderer: renderUsageFloat
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
				{type: 'numeric',  dataIndex: 'AcctInput'},
				{type: 'numeric',  dataIndex: 'AcctOutput'},
				{type: 'numeric',  dataIndex: 'AcctSessionTime'},
				{type: 'string',  dataIndex: 'ConnectTermReason'}
			]
		}
	);
	// Grab store
	var store = Ext.getCmp(adminUserLogsWindow.gridPanelID).getStore();

	store.on('load',function() {

		// Fetch periodKey from form
		var periodKeyField = (Ext.getCmp(formPeriodKeyID)).getValue();

		// Mask parent window
		adminUserLogsWindow.getEl().mask();

		uxAjaxRequest(
			adminUserLogsWindow,
			{
				params: {
					PeriodKey: periodKeyField,
					ID: id,
					SOAPUsername: globalConfig.soap.username,
					SOAPPassword: globalConfig.soap.password,
					SOAPAuthType: globalConfig.soap.authtype,
					SOAPModule: 'AdminUserLogs',
					SOAPFunction: 'getAdminUserLogsSummary',
					SOAPParams: '0:ID,0:PeriodKey'
				},

				customSuccess: function (result) {
					response = Ext.decode(result.responseText);

					// Caps
					var trafficCap = response.data.trafficCap; // value of -1: prepaid
					var uptimeCap = response.data.uptimeCap; // value of -1: prepaid
					
					// Usage
					var trafficUsage = response.data.trafficUsage;
					var uptimeUsage = response.data.uptimeUsage;

					// Topups
					var trafficTopups = response.data.trafficTopups;
					var uptimeTopups = response.data.uptimeTopups;

					// Format string before printing
					var trafficString = '';
					// Prepaid traffic
					if (trafficCap == -1) {
						trafficCap = 'Prepaid';
						trafficString += sprintf('               Traffic\nCap: %s MB Topup: %d MB\n'+
								'Usage: %d/%d MB\n=====================================\n',
								trafficCap,trafficTopups,trafficUsage,trafficTopups);
					// Uncapped traffic
					} else if (trafficCap == 0) {
						trafficString += sprintf('               Traffic\nCap: Uncapped Used: %d MB\n=====================================\n',
								trafficUsage);
					// Capped traffic
					} else {
						var combinedTrafficCap = trafficCap + trafficTopups;
						trafficString += sprintf('               Traffic\nCap: %d MB Topup: %d MB\n'+
								'Usage: %d/%d MB\n=====================================\n',
								trafficCap,trafficTopups,trafficUsage,combinedTrafficCap);
					}

					// Format string before printing
					var uptimeString = '';
					// Prepaid uptime
					if (uptimeCap == -1) {
						uptimeCap = 'Prepaid';
						uptimeString += sprintf('               Uptime\nCap: %s Min Topup: %d Min\n'+
								'Usage: %d/%d Min',
								uptimeCap,uptimeTopups,uptimeUsage,uptimeTopups);
					// Uncapped uptime
					} else if (uptimeCap == 0) {
						uptimeString += sprintf('               Uptime\nCap: Uncapped Used: %d Min',
								uptimeUsage);
					// Capped uptime
					} else {
						var combinedUptimeCap = uptimeCap + uptimeTopups;
						uptimeString += sprintf('               Uptime\nCap: %d Min Topup: %d Min\n'+
								'Usage: %d/%d Min',
								uptimeCap,uptimeTopups,uptimeUsage,combinedUptimeCap);
					}

					// Get summary field
					var form = Ext.getCmp(summaryFormID);
					var summaryField = Ext.getCmp(summaryTotalID);
					summaryField.setValue(trafficString+uptimeString);
				},
				failure: function (result) {
					Ext.MessageBox.alert('Failed', 'Couldn\'t fetch data: '+result.date);
				}
			}
		);
	});
	adminUserLogsWindow.show();
}


// vim: ts=4
