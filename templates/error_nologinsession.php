<form method="post" action="<?= $full_url ?>misc.php?action=login">
<input type="hidden" name="token" value="" />
<input type="hidden" name="hide" value="2" />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category" colspan="2"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['noadminsession'] ?></strong></font></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><strong><?= $lang['error'] ?></strong></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><span class="smalltxt"><strong><?= $lang['noadminsession'] ?></strong></span><br />
<span class="smalltxt"><strong><?= $lang['noadminsession2'] ?></strong></span><br />
<span class="smalltxt"><?= $lang['noadminsession3'] ?></span><br />
<span class="smalltxt"><?= $lang['noadminsession4'] ?></span></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textusername'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="username" size="30" maxlength="40" />  </td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textpassword'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="password" name="password" size="25" />  <br />
</td>
</tr>
<tr class="ctrtablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input type="submit" class="submit" name="loginsubmit" value="<?= $lang['textlogin'] ?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
