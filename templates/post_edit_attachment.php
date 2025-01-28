<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><strong><?= $lang['attachment'] ?></strong>
<br />
<div style="padding-left: 10px;">
<?= $lang['textfilename'] ?> <em><?= $aInfo['filename'] ?></em>
<br />
<?= $lang['textfilesize'] ?> <em><?= $aInfo['filesize'] ?> <?= $lang['byte'] ?></em>
<br />
<?= $lang['textdownloads'] ?> <em><?= $aInfo['downloads'] ?></em>
<br />
<br />
<em>&raquo; <a href="<?= $aInfo['url'] ?>"><?= $lang['textdownload'] ?></a></em>
</div>
</td>
<td bgcolor="<?= $THEME['altbg2'] ?>">
<input type="radio" name="attachment[<?= $aInfo['aid'] ?>][action]" value="leave" checked="checked" /><?= $lang['leaveuntouched'] ?>.<br />
<input type="radio" name="attachment[<?= $aInfo['aid'] ?>][action]" value="replace" /><?= $lang['uploadinstead'] ?><br />
<input type="hidden" name="MAX_FILE_SIZE" value="<?= $SETTINGS['maxattachsize'] ?>" />
<input type="file" name="replace_<?= $aInfo['aid'] ?>" style="margin-left: 2em;" width="25" size="25" /><br />
<input type="radio" name="attachment[<?= $aInfo['aid'] ?>][action]" value="rename" /><?= $lang['renamefile'] ?><br />
<input type="text" name="rename_<?= $aInfo['aid'] ?>" style="margin-left: 2em;" size="22" value="<?= $aInfo['filename'] ?>"/><br />
<input type="radio" name="attachment[<?= $aInfo['aid'] ?>][action]" value="delete" /><?= $lang['deletecurrent'] ?><br />
</td>
</tr>
