/*
WiSP User Logs
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


function showWiSPUserLogsWindow(id) {
	// Calculate dates we going to need
	var today = new Date();
	var firstOfMonth = today.getFirstDateOfMonth();
	var firstOfNext = today.getLastDateOfMonth().add(Date.DAY, 1);

	var wispUserLogsWindow = new Ext.ux.GenericGridWindow(
		// Window config
		{
			title: 'Logs',
			iconCls: 'logs',
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
								var grid = Ext.getCmp(mainWindow.gridPanelID);
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
				SOAPModule: 'WiSPUserLogs',
				SOAPFunction: 'getWiSPUserLogs',
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
	var store = Ext.getCmp(wispUserLogsWindow.gridPanelID).getStore();

	store.on('load',function() {
		var inputTotal = store.sum('AcctInputMbyte');
		var outputTotal = store.sum('AcctOutputMbyte');
		var uptimeTotal = store.sum('AcctSessionTime');

		var searchForm = wispUserLogsWindow.getComponent('search-form');
		var afterField = (searchForm.getForm().findField('after')).getValue();
		var beforeField = (searchForm.getForm().findField('before')).getValue();

		// Mask parent window
		wispUserLogsWindow.getEl().mask();

		uxAjaxRequest(
			wispUserLogsWindow,
			{
				params: {
					From: afterField,
					To: beforeField,
					ID: id,
					SOAPUsername: globalConfig.soap.username,
					SOAPPassword: globalConfig.soap.password,
					SOAPAuthType: globalConfig.soap.authtype,
					SOAPModule: 'WiSPUserLogs',
					SOAPFunction: 'getWiSPUserLogsSummary',
					SOAPParams: '0:ID,0:From,0:To'
				},

				customSuccess: function (result) {
					response = Ext.decode(result.responseText);

					// Traffic variables
					var trafficCap = response.data.trafficCap; // value of -1: prepaid
					
					var trafficCurrentTopupUsed = response.data.trafficCurrentTopupUsed; // value of -1: no current topup
					var trafficCurrentTopupCap = response.data.trafficCurrentTopupCap; // value of -1: no current topup
					var trafficTopupRemaining = response.data.trafficTopupRemaining;

					// Uptime variables
					var uptimeCap = response.data.uptimeCap; // value of -1: prepaid

					var uptimeCurrentTopupUsed = response.data.uptimeCurrentTopupUsed; // value of -1: no current topup
					var uptimeCurrentTopupCap = response.data.uptimeCurrentTopupCap; // value of -1: no current topup
					var uptimeTopupRemaining = response.data.uptimeTopupRemaining;

					// Total up traffic
					var trafficTotalAllowed;
					var validTrafficTopups;
					if (trafficCurrentTopupCap > 0) {
						validTrafficTopups = trafficCurrentTopupCap;
						validTrafficTopups += trafficTopupRemaining;
					} else {
						validTrafficTopups = trafficTopupRemaining;
					}

					if (trafficCap < 0) {
						trafficTotalAllowed = validTrafficTopups;
					} else {
						trafficTotalAllowed = trafficCap + validTrafficTopups;
					}

					// Traffic usage
					var trafficUsage = inputTotal + outputTotal;

					// Total up uptime
					var uptimeTotalAllowed;
					var validUptimeTopups;
					if (uptimeCurrentTopupCap > 0) {
						validUptimeTopups = uptimeCurrentTopupCap;
						validUptimeTopups += uptimeTopupRemaining;
					} else {
						validUptimeTopups = uptimeTopupRemaining;
					}

					if (uptimeCap < 0) {
						uptimeTotalAllowed = validUptimeTopups;
					} else {
						uptimeTotalAllowed = uptimeCap + validUptimeTopups;
					}

					// Get summary field
					var form = wispUserLogsWindow.getComponent('summary-form');
					var summaryTotal = form.getForm().findField('summaryTotal');

					// Format string before printing
					var trafficString = '';
					// Prepaid traffic
					if (trafficCap == -1) {
						trafficCap = 'Prepaid';
						trafficString += sprintf('               Traffic\nCap: %s MB Topup: %d MB\n'+
								'Usage: %d/%d MB\n=====================================\n',
								trafficCap,validTrafficTopups,trafficUsage,trafficTotalAllowed);
					// Uncapped traffic
					} else if (trafficCap == 0) {
						trafficString += sprintf('               Traffic\nCap: Uncapped Used: %d\n=====================================n',
								trafficUsage);
					// Capped traffic
					} else {
						trafficString += sprintf('               Traffic\nCap: %d MB Topup: %d MB\n'+
								'Usage: %d/%d MB\n=====================================\n',
								trafficCap,validTrafficTopups,trafficUsage,trafficTotalAllowed);
					}

					// Format string before printing
					var uptimeString = '';
					// Prepaid uptime
					if (uptimeCap == -1) {
						uptimeCap = 'Prepaid';
						uptimeString += sprintf('               Uptime\nCap: %s MB Topup: %d MB\n'+
								'Usage: %d/%d MB',
								uptimeCap,validUptimeTopups,uptimeTotal,uptimeTotalAllowed);
					// Uncapped uptime
					} else if (uptimeCap == 0) {
						uptimeString += sprintf('               Uptime\nCap: Uncapped Used: %d',
								uptimeTotal);
					// Capped uptime
					} else {
						uptimeString += sprintf('               Uptime\nCap: %d MB Topup: %d MB\n'+
								'Usage: %d/%d MB',
								uptimeCap,validUptimeTopups,uptimeTotal,uptimeTotalAllowed);
					}

					summaryTotal.setValue(trafficString+uptimeString);
				},
				failure: function (result) {
					Ext.MessageBox.alert('Failed', 'Couldn\'t fetch data: '+result.date);
				}
			}
		);
	});
	wispUserLogsWindow.show();				
}

// vim: ts=4
