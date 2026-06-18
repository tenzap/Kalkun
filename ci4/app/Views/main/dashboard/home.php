<?php helper('kalkun'); ?>
<div id="space_area">

	<?php if (isset($alerts) && count($alerts) > 0): ?>
	<div class="dash_box_titlebar"><?php echo tr('Alerts');?></div>
	<div class="dash_box">
		<?php
foreach ($alerts as $msg):
   echo '<div class="warning">'.htmlentities($msg, ENT_QUOTES).'</div>';;
endforeach;
?>
	</div>
	<br>
	<?php endif; ?>

	<div class="dash_box_titlebar"><?php echo tr('Statistics');?></div>
	<div class="dash_box">
		<?php echo view('main/dashboard/statistic');?>
	</div>
	<br>

	<?php if (session()->get('level') === 'admin'): ?>
	<div class="dash_box_titlebar"><?php echo tr('System information');?></div>
	<div class="dash_box">
		<table class="sysinfo">
			<tr>
				<td><?php echo tr('Operating system');?></td>
				<td>:</td>
				<td><?php echo  filter_data(PHP_OS); ?></td>
			</tr>
			<tr>
				<td><?php echo tr('Gammu version');?></td>
				<td>:</td>
				<td><?php
				echo  filter_data(htmlentities(model('KalkunModel')->get_gammu_info('gammu_version')->getRow('Client') !== NULL ? model('KalkunModel')->get_gammu_info('gammu_version')->getRow('Client') : '', ENT_QUOTES)); ?></td>
			</tr>
			<tr>
				<td><?php echo tr('Gammu DB schema');?></td>
				<td>:</td>
				<td><?php echo  filter_data(htmlentities(model('KalkunModel')->get_gammu_info('db_version')->getRow('Version')), ENT_QUOTES); ?></td>
			</tr>
			<tr>
				<td><?php echo tr('Modem IMEI');?></td>
				<td>:</td>
				<td><?php echo  filter_data(htmlentities(model('KalkunModel')->get_gammu_info('phone_imei')->getRow('IMEI') !== NULL ? model('KalkunModel')->get_gammu_info('phone_imei')->getRow('IMEI') : '', ENT_QUOTES)); ?></td>
			</tr>
		</table>
	</div>
</div>
<?php endif;?>
