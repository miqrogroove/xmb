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
<form method="get" action="<?= $full_url ?>misc.php" onsubmit="return disableButton(this);">
<input type="hidden" name="action" value="login" />
<table border="0" cellpadding="0" cellspacing="0">
<tr>
<td nowrap="nowrap">
<input type="submit" class="submit" value="<?= $lang['textlogin'] ?>" />
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
