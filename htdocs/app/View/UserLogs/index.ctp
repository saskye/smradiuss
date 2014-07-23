<style type="text/css">
body {
	padding-top: 50px;
}
#main {
	border:1px #DFE8F6 solid;
}
#search {
	background-color:#DFE8F6;
	width:400px;
	float:left;
	border:1px #C8D1D4 solid;
	height:180px;
}
#topuplogs {
	overflow-y: scroll;
	height:180px;
	padding-left:5px;
	border:1px #DFE8F6 solid;
}
</style>

<div style="padding: 15px 15px">
	<div class="row"><?php echo $this->element('left_panel');?>
		<div class="col-md-10"><legend>User Logs</legend>
			<div id="main">
				<div id="search">
					<?php echo $this->Form->create()?>
						<div style="color:#3276B1;margin:5px;">Search</div>
						<div class="form-group">
							<?php
								echo $this->Form->label(
									'Period',
									'Period',
									array(
										'class' => 'col-md-2 control-label'
									)
								);
							?>
							<div class="row" style="float:left;">
								<div class="col-md-4 input-group" style="float:left;width:100px;margin-right:0px;margin-left: 18px;">
									<?php
										// -- for year select box --
										$year = date("Y");
										$start = $year-10;
										$end = $year+10;
										$selected = '';
										$yearData = array();
										foreach (range($start, $end) as $number) {
											$yearData[$number] = $number;
										}
										echo $this->Form->input(
											'yearData',
											array(
												'label' => false,
												'class' => 'form-control',
												'type' => 'select',
												'options' => $yearData,
												'selected' => $year
											)
										);
									?>
								</div>
							</div>
							<div class="row">
								<div class="col-md-4 input-group" style="float:left;width:100px;margin-right:0px;margin-left: 18px;">
									<?php
										// -- for day select box --
										$month = date("m");
										$dayData = array();
										foreach (range(1, 12) as $number) {
											if ($number <= 9) {
												$dayData['0'.$number] = '0'.$number;
											} else {
												$dayData[$number] = $number;
											}
										}
										echo $this->Form->input(
											'dayData',
											array(
												'label' => false,
												'class' => 'form-control',
												'type' => 'select',
												'options' => $dayData,
												'selected' => $month
											)
										);
									?>
								</div>
							</div>
						</div>
						<div class="form-group">
							<button type="submit" class="btn btn-primary" style="margin-left:100px"><?php echo __('Search')?></button>
						</div>
					<?php echo $this->Form->end(); ?>
				</div>
				<div id="topuplogs">
					<?php
						$userLog1 = array_values($userLog);
						$totalvalue1 = '';
						$totalvalue2 = '';

						foreach ($userLog as $uLog) {
							if (h($uLog['UserTopup']['Type']) == '1') {
								$totalvalue1[] = h($uLog['UserTopup']['Value']).",";
							} else {
								$totalvalue1[] = '';
							}
							if (h($uLog['UserTopup']['Type']) == '2') {
								$totalvalue2[] = h($uLog['UserTopup']['Value']).",";
							} else {
								$totalvalue2[] = '';
							}
						}
						if (!empty($totalvalue1)) {
							$tValue = array_sum($totalvalue1);
						} else {
							$tValue = 0;
						}
						if (!empty($totalvalue2)) {
							$uValue = array_sum($totalvalue2);
						} else {
							$uValue = 0;
						}
					?>
					<div>Traffic:</div>
					<div>Cap: Prepaid</div>
					<div>Topup balance for current month: <?php echo $tValue; ?> MB</div>
					<div>Total Topups: <?php echo $tValue; ?> MB</div>
					<div>Usage: 0/<?php echo $tValue; ?> MB</div>
					<div>---</div>
					<div>Uptime:</div>
					<div>Cap: Prepaid</div>
					<div>Topup balance for current month: <?php echo $uValue; ?> MB</div>
					<div>Total Topups: <?php echo $uValue; ?> MB</div>
					<div>Usage: 0/<?php echo $uValue; ?> MB</div>
					<div>---</div>
					<?php
						foreach ($userLog1 as $uLog) {
							if (h($uLog['UserTopup']['Type']) == '1') { ?>
								<div>Valid Traffic Topups:</div>
								<div>ID: <?php echo h($uLog['UserTopup']['ID']); ?></div>
								<div>Usage: 0/<?php echo h($uLog['UserTopup']['Value']); ?></div>
								<div>Valid Until: <?php echo h($uLog['UserTopup']['ValidTo']); ?></div>
								<div>---</div>
								<?php
							}
						}
						foreach ($userLog1 as $log) {
							if (h($log['UserTopup']['Type']) == '2') { ?>
								<div>Valid Uptime Topups:</div>
								<div>ID: <?php echo h($log['UserTopup']['ID']); ?></div>
								<div>Usage: 0/<?php echo h($log['UserTopup']['Value']); ?></div>
								<div>Valid Until: <?php echo h($log['UserTopup']['ValidTo']); ?></div>
								<div>---</div>
								<?php
							}
						}
					?>
				</div>
				<div>
					<table class="table">
						<thead>
							<tr>
								<th><a><?php echo __('Timestamp', true);?></a></th>
								<th><a><?php echo __('Service Type', true);?></a></th>
								<th><a><?php echo __('Framed Protocol', true);?></a></th>
								<th><a><?php echo __('Calling Station', true);?></a></th>
								<th><a><?php echo __('Input Mbyte', true);?></a></th>
								<th><a><?php echo __('Output Mbyte', true);?></a></th>
								<th><a><?php echo __('Session Uptime', true);?></a></th>
								<th><a><?php echo __('Term. Reason', true);?></a></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($userAcc as $acc):
								$AcctInputOctets = h($acc['UserLog']['AcctInputOctets']) / 1024  /1024;
								$AcctInputGigawords = h($acc['UserLog']['AcctInputGigawords']) * 4096;
								$inputMbyte = $AcctInputOctets + $AcctInputGigawords;
								$AcctOutputOctets = h($acc['UserLog']['AcctOutputOctets']) / 1024  /1024;
								$AcctOutputGigawords = h($acc['UserLog']['AcctOutputGigawords']) * 4096;
								$outputMbyte = $AcctOutputOctets + $AcctOutputGigawords; ?>
								<tr>
									<td><? echo h($acc['UserLog']['EventTimestamp']); ?></td>
									<td><? echo h($acc['UserLog']['ServiceType']); ?></td>
									<td><? echo h($acc['UserLog']['FramedProtocol']); ?></td>
									<td><? echo h($acc['UserLog']['CallingStationID']); ?></td>
									<td><? echo $inputMbyte;?></td>
									<td><? echo $outputMbyte;?></td>
									<td><? echo (h($acc['UserLog']['AcctSessionTime'])/60); ?></td>
									<td><? echo h($acc['UserLog']['AcctTerminateCause']); ?></td>
								</tr>
							<? endforeach; ?>
							<tr>
								<td align="center" colspan="10" >
									<?php
										$total = $this->Paginator->counter(
											array(
												'format' => '%pages%'
											)
										);
										if ($total >1) {
											echo $this->Paginator->prev(
												'<<',
												null,
												null,
												array(
													'class' => 'disabled'
												)
											);
											echo $this->Paginator->numbers();
											// Shows the next and previous links.
											echo $this->Paginator->next(
												'>>',
												null,
												null,
												array(
													'class' => 'disabled'
												)
											);
											// Prints X of Y, where X is current page and Y is number of pages.
											echo "<span style='margin-left:20px;'>Page : ".$this->Paginator->counter()."</span>";
										}
									?>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
