<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><strong><?= $lang['attachment'] ?></strong>
<br />
<div style="padding-left: 10px;">
<?= $lang['textfilename'] ?> <em><?= $postinfo['filename'] ?></em>
<br />
<?= $lang['textfilesize'] ?> <em><?= $postinfo['filesize'] ?> <?= $lang['byte'] ?></em>
</div>
</td>
<td bgcolor="<?= $THEME['altbg2'] ?>">
<input type="radio" name="attachment[<?= $postinfo['aid'] ?>][action]" value="leave" checked="checked" /><?= $lang['leaveuntouched']. ?><br />
<input type="radio" name="attachment[<?= $postinfo['aid'] ?>][action]" value="replace" /><?= $lang['uploadinstead'] ?><br />
<input type="hidden" name="MAX_FILE_SIZE" value="<?= $SETTINGS['maxattachsize'] ?>" />
<input type="file" name="replace_<?= $postinfo['aid'] ?>" style="margin-left: 2em;" width="25" size="25" /><br />
<input type="radio" name="attachment[<?= $postinfo['aid'] ?>][action]" value="rename" /><?= $lang['renamefile'] ?><br />
<input type="text" name="rename_<?= $postinfo['aid'] ?>" style="margin-left: 2em;" size="22" value="<?= $postinfo['filename'] ?>"/><br />
<input type="radio" name="attachment[<?= $postinfo['aid'] ?>][action]" value="delete" /><?= $lang['deletecurrent'] ?><br />
</td>
</tr>
