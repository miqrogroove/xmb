<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category" colspan="2"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['welcomeunregnotify'] ?></strong></font></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><font size="1"><?= $lang['welcomeunreg'] ?></font></td>
<td align="right" width="25%">
<form method="post" action="misc.php?action=login" onsubmit="return disableButton(this);">
<input type="hidden" name="token" value="" />
<input type="hidden" name="hide" value="2" />
<table border="0" cellpadding="0" cellspacing="0">
<tr>
<td nowrap="nowrap">
<input type="text" name="username" size="7" accesskey="u" />
<input type="password" name="password" size="7" accesskey="p" />
<input type="submit" class="submit" name="loginsubmit" value="<?= $lang['textlogin'] ?>" />
</td>
</tr>
</table>
</form>
</td>
</tr>
</table>
</td>
</tr>
</table>
<br />
