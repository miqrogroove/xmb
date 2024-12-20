<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textavatarurl'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>">
<input type="hidden" name="newavatarcheck" id="newavatarcheck" value="no" />
<input type="text" name="newavatar" size="25" onblur="avatarCheck(this, '<?= $SETTINGS['max_avatar_size']', ?> <?= $js_https_only) ?>" />
<p id="avatarCheck" style="display: inline;"></p>
</td>
</tr>
