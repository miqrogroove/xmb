<tr bgcolor="<?= $THEME['altbg2'] ?>">
<td align="center">
<form method="post" action="<?= $full_url ?>admin/sql.php" enctype="multipart/form-data">
<input type="hidden" name="token" value="<?= $token ?>" />
<table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="tablerow" bgcolor="<?= $THEME['altbg1'] ?>"><strong><?= $lang['textupgrade'] ?></strong></td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow"><?= $lang['upgrade'] ?></td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg1'] ?>" class="tablerow" valign="top"><textarea cols="85" rows="10" name="upgrade"></textarea></td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow"><input type="file" name="sql_file" /></td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg1'] ?>" class="tablerow"><?= $lang['upgradenote'] ?></td>
</tr>
<tr>
<td class="ctrtablerow" bgcolor="<?= $THEME['altbg2'] ?>"><input type="submit" class="submit" name="upgradesubmit" value="<?= $lang['textsubmitchanges'] ?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
</td>
</tr>
