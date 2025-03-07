<br />
<form method="post" action="">
<input type="hidden" name="token" value="" />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $label ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="password" name="pw" size="25" /> <br /></td>
</tr>
<tr>
<td class="ctrtablerow" bgcolor="<?= $THEME['altbg1'] ?>" colspan="2"><input type="submit" class="submit" name="loginsubmit" value="<?= $lang['textlogin'] ?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
