<ul>
	<li><?php echo anchor('', tr('Dashboard')); ?></li>
	<li><a href="javascript:void(0);" id="compose_sms_normal"><?php echo tr('Compose');?></a></li>
	<li>
		<span style="color: #FFF;"><?php echo tr('Folders');?></span>
		<div id="f_child_menu">
			<ul>
				<li>
					<?php echo anchor('messages/folder/inbox', tr('Inbox'));?>
					<span class="unread_inbox_notif">
						<?php
	$tmp_unread = model('MessageModel')->get_messages(array('readed' => FALSE, 'uid' => session()->get('id_user')))->getNumRows();
	if ($tmp_unread > 0)
	{
		echo ' ('.$tmp_unread.')';
	}
	?>
					</span>
				</li>
				<li><?php echo anchor('messages/folder/outbox', tr('Outbox')); ?></li>
				<li><?php echo anchor('messages/folder/sentitems', tr('Sent items')); ?> </li>
				<?php if ((service('uri')->getTotalSegments() >= 3 && service('uri')->getSegment(3) === 'sentitems') || (service('uri')->getTotalSegments() >= 4 && service('uri')->getSegment(4) === 'sentitems')) : ?>
				<li style="list-style: none;"><?php echo anchor('messages/conversation/folder/sentitems/sending_error', tr('Sending error')); ?> </li>
				<?php endif; ?>
				<li><?php echo anchor('messages/my_folder/inbox/6', tr('Spam')); ?>
					<span class="unread_spam_notif">
						<?php
	$tmp_unread = model('MessageModel')->get_messages(array('readed' => FALSE, 'id_folder' => '6', 'uid' => session()->get('id_user')))->getNumRows();
	if ($tmp_unread > 0)
	{
		echo ' ('.$tmp_unread.')';
	}
	?>
					</span>
				</li>
				<li><?php echo anchor('messages/my_folder/inbox/5', tr('Trash')); ?></li>

			</ul>
		</div>
	</li>
	<li>
		<div style="float: left"><span style="color: #FFF;"><?php echo tr('My folders');?></span></div>
		<div style="float: right"><sup><a id="addfolder" href="javascript:void(0);" title="<?php echo tr('Add a new folder');?>"><?php echo tr('Add'); ?></a></sup></div>
		<div class="clear">&nbsp;</div>
		<div id="mf_child_menu">
			<ul>
				<?php foreach (model('KalkunModel')->get_folders('all')->getResult() as $folder):?>
				<li>
					<?php echo anchor('messages/my_folder/inbox/'.$folder->id_folder, htmlentities($folder->name, ENT_QUOTES));
	$tmp_unread = model('MessageModel')->get_messages(array('readed' => FALSE, 'id_folder' => $folder->id_folder, 'uid' => session()->get('id_user')))->getNumRows();
	if ($tmp_unread > 0)
	{
		echo ' ('.$tmp_unread.')';
	}
	?>
				</li><?php endforeach;?>
			</ul>
		</div>
	</li>
	<li><?php echo  anchor('phonebook', tr('Phonebook')); ?></li>
	<?php
$level = session()->get('level');
if ($level === 'admin'):?>
	<li><?php echo anchor('users', tr('Users')); ?></li>
	<?php if (false): // if (config('Kalkun')->sms_content): // CI4-TODO. Check the meaning. This didn't exist in kalkun 0.8 anyway. Probably something from the past... ?>
	<li id="bottom"><?php echo anchor('member', tr('Member')); ?></li>
	<?php endif; ?>
	<li><?php echo anchor('pluginss', tr('Plugins')); ?></li>
	<?php endif; ?>
</ul>
