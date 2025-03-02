<form method="post" action="<?= $full_url ?>misc.php?action=login">
<input type="hidden" name="token" value="<?= $token ?>" />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category" colspan="2"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['textlogin'] ?></strong></font></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textusername'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="username" size="25" maxlength="25" />&nbsp;<span class="smalltxt"><a href="<?= $full_url ?>member.php?action=reg"><?= $lang['regques'] ?></a></span></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textpassword'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="password" name="password" size="25" />&nbsp;<span class="smalltxt"><a href="<?= $full_url ?>misc.php?action=lostpw"><?= $lang['forgotpw'] ?></a></span><br /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['login_trusted'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="checkbox" name="trust" value="yes" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textinvisible'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="checkbox" name="hide" value="1" /></td>
</tr>
<tr class="ctrtablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input type="submit" class="submit" name="loginsubmit" value="<?= $lang['textlogin'] ?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
