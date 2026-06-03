<?php echo view('js_init/users/js_users');
if ($users->getNumRows() === 0):
	if ($_POST)
	{
		echo '<p><i>'.tr('User not found').'</i></p>';
	}
	else
	{
		echo '<p><i>'.tr('No users in the database.').'</i></p>';
	}
else: ?>
<table>
	<?php foreach ($users->getResult() as $tmp): ?>
	<tr id="<?php echo $tmp->id_user;?>">
		<td>
			<div class="two_column_container contact_list hover_show">
				<div class="left_column">
					<div id="pbkname">
						<input type="checkbox" class="select_user">&nbsp;<span style="font-weight: bold;"><?php echo htmlentities($tmp->realname, ENT_QUOTES);?></span>
						<?php if (in_array($tmp->id_user, config('Kalkun')->inbox_owner_id))
{
	echo '<sup>('.tr('Inbox Master').')</sup>';
} ?>
					</div>
				</div>
				<div class="right_column">
					<span class="pbk_menu no-touch-hidden">
						<a class="edit_user simplelink" href="javascript:void(0);"><?php echo tr('Edit'); ?></a>
					</span>
				</div>
			</div>
		</td>
	</tr>
	<?php endforeach;?>
</table>
<?php endif; ?>
