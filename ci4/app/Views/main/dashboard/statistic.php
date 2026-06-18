<?php echo view('js_init/js_dashboard');?>

<!--base href="<?= config('App')->baseURL ?>" /-->
<script src="<?php echo config('Kalkun')->js_path;?>chart.umd.js"></script>

<div style="text-align: right;">
	<a href="<?php echo site_url('kalkun/get_statistic/days');?>" class="stats-toggle"><?php echo tr('date_day');?></a>&nbsp; &nbsp;
	<a href="<?php echo site_url('kalkun/get_statistic/weeks');?>" class="stats-toggle"><?php echo tr('date_week');?></a>&nbsp; &nbsp;
	<a href="<?php echo site_url('kalkun/get_statistic/months');?>" class="stats-toggle"><?php echo tr('date_month');?></a>&nbsp; &nbsp;
</div>

<div class="chart-container" style="position: relative; height:200px; width:650px">
	<canvas id="myChart" width="650" height="200" aria-label="Statistics chart" role="img" style="background:#fff; border:1px solid #ccc;">
		<p>Stats Fallback</p>
	</canvas>
</div>

<script>
	var chart_version_major;
	if (typeof Chart.version === 'undefined') {
		chart_version_major = 2;
	} else {
		// chartjs >= 3 does set the version variable
		chart_version_major = parseInt(Chart.version.split(".")[0]);
	}
	//alert("version: " + chart_version_major );

	var canvas = document.getElementById('myChart');

	switch (chart_version_major) {
		case 2:
			var myChart = new Chart(canvas, {
				type: 'bar',
				data: {
					labels: [<?php echo tr_js('No data'); ?>],
					datasets: [{
						label: <?php echo tr_js('No data'); ?>,
						data: [0]
					}]
				},
				options: {
					scales: {
						yAxes: [{
							ticks: {
								beginAtZero: true
							}
						}]
					}
				}
			});
			break;
		case 3:
		case 4:
			var myChart = new Chart(canvas, {
				type: 'bar',
				data: {
					labels: [<?php echo tr_js('No data'); ?>],
					datasets: [{
						label: <?php echo tr_js('No data'); ?>,
						data: [0]
					}]
				},
				options: {
					scales: {
						y: {
							beginAtZero: true
						}
					}
				}
			});
			break;
		default:
			var context = canvas.getContext("2d");
			context.textAlign = 'center';
			context.textBaseline = 'middle';
			context.fillText("chartjs version " + chart_version_major + " is not supported.", (canvas.width / 2), (canvas.height / 2));
			break;
	}

</script>

<?php
$uid = session()->get('id_user');
$inbox = model('MessageModel')->get_messages(array('type' => 'inbox', 'uid' => $uid))->getNumRows();
$outbox = model('MessageModel')->get_messages(array('type' => 'outbox', 'uid' => $uid))->getNumRows();
$sentitems = model('MessageModel')->get_messages(array('type' => 'sentitems', 'uid' => $uid))->getNumRows();
$trash_inbox = model('MessageModel')->get_messages(array('type' => 'inbox', 'id_folder' => '5', 'uid' => $uid))->getNumRows();
$trash_sentitems = model('MessageModel')->get_messages(array('type' => 'sentitems', 'id_folder' => '5', 'uid' => $uid))->getNumRows();
$trash = $trash_inbox + $trash_sentitems;
?>

<div style="float: left; width: 200px;">
	<h1><?php echo tr('Folders');?>: </h1>
	<p><span><?php echo tr('Inbox');?>:</span> <?php echo $inbox;?></p>
	<p><span><?php echo tr('Outbox');?>:</span> <?php echo $outbox;?></p>
	<p><span><?php echo tr('Sent items');?>:</span> <?php echo $sentitems;?></p>
	<p><span><?php echo tr('Trash');?>:</span> <?php echo $trash;?></p>
</div>

<div style="float: left; width: 250px;">
	<h1><?php echo tr('My folders');?>: </h1>
	<?php
foreach (model('KalkunModel')->get_folders('all')->getResult() as $val):
$folder_count_inbox = model('MessageModel')->get_messages(array('type' => 'inbox', 'id_folder' => $val->id_folder, 'uid' => session()->get('id_user')))->getNumRows();
$folder_count_sentitems = model('MessageModel')->get_messages(array('type' => 'sentitems', 'id_folder' => $val->id_folder, 'uid' => session()->get('id_user')))->getNumRows();
$folder_count = $folder_count_inbox + $folder_count_sentitems;
echo '<p><span>'.htmlentities($val->name, ENT_QUOTES).': </span>'.$folder_count.'</p>';
endforeach;
?>
</div>

<div style="float: left; width: 200px;">
	<h1><?php echo tr('Phonebook');
?>: </h1>
	<p><span><?php echo tr('Contact');?>: </span>
		<?php echo  model('PhonebookModel')->get_phonebook(array('option' => 'all'))->getNumRows();?></p>
	<p><span><?php echo tr('Groups');?>: </span>
		<?php echo  model('PhonebookModel')->get_phonebook(array('option' => 'group'))->getNumRows();?></p>
</div>

<div style="clear: both;">&nbsp;</div>
