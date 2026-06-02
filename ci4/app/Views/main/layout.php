<?php
helper('html');
echo doctype('html5');?>
<html>

<head><?php echo view('main/header');?></head>

<body>
	<?php echo view('main/base');?>

	<div class="loading_container"><span class="loading_area hidden"><?php echo tr('Loading');?>...</span></div>
	<div id="top_navigation"><?php echo view('main/dock');?></div>

	<div id="main_container">
		<div id="header">
			<div id="header_left">
				<div id="logo"><a href="javascript:void(0);"><img src="<?php echo config('Kalkun')->img_path;?>logo.png" alt="Kalkun logo"></a></div>

			</div>
			<div id="header_right">
				<div id="top_link"><?php echo view('main/search');?></div>
				<div class="clear">&nbsp;</div>
				<div class="notification_container" style="text-align: center;"><span class="notification_area hidden"><?php echo tr('Loading');?>...</span>
					<?php if (session()->getFlashdata('notif')): ?>
					<span class="notification_area"><?php echo htmlentities(session()->getFlashdata('notif'), ENT_QUOTES);?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div id="container">
			<div id="menu"><?php echo view('main/menu');?></div>
			<div id="content">
				<div id="compose_sms_container" title="<?php echo tr('Compose SMS'); ?>" class="hidden">&nbsp;</div>
				<?php echo view($main);?>
			</div>
		</div>
		<div id="footer"><?php echo view('main/footer');?></div>
	</div>

</body>

</html>
