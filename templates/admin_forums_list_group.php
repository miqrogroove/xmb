<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td>&nbsp;</td>
</tr>
<tr bgcolor="<?= $THEME['altbg1'] ?>" class="tablerow">
<td class="smalltxt"><input type="checkbox" name="delete<?= $group['fid'] ?>" value="<?= $group['fid'] ?>" />
<input type="text" name="name<?= $group['fid'] ?>" value="<?= $group['name'] ?>" />
&nbsp; <?= $lang['textorder'] ?> <input type="text" name="displayorder<?= $group['fid'] ?>" size="2" value="<?= $group['displayorder'] ?>" />
&nbsp; <select name="status<?= $group['fid'] ?>">
<option value="on" <?= $on ?>><?= $lang['texton'] ?></option><option value="off" <?= $off ?>><?= $lang['textoff'] ?></option></select>
</td>
</tr>
