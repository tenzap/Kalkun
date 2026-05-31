<div id="top_navigation_container">
	<div id="top_navigation_left">
		<span class="modem_status">
			<?php echo view('main/notification');?>
		</span>
	</div>

	<div id="top_navigation_center">
		<?php
	$fmt = new IntlDateFormatter(
	service('language')->locale,
	IntlDateFormatter::FULL,
	IntlDateFormatter::SHORT
);
	echo $fmt->format(time()) . ' ' . IntlTimeZone::createDefault()->getDisplayName(FALSE, IntlTimeZone::DISPLAY_SHORT, service('language')->locale);
?>
	</div>

	<div id="top_navigation_right">
		<?php echo session()->get('username');?> |
		<a href="<?php echo site_url('settings/general');?>" id="setting"><?php echo tr('Settings'); ?></a> |
		<a href="<?php echo site_url('settings/filters');?>" id="filters"><?php echo tr('Filters'); ?></a> |
		<a href="<?php echo site_url('logout');?>" id="logout"><?php echo tr('Logout');?></a>
	</div>
</div>
