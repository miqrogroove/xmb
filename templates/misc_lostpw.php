<form method="post" action="misc.php?action=lostpw" onsubmit="return disableButton(this);">
<input type="hidden" name="token" value="" />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category" colspan="2"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['textlostpw'] ?></strong></font></td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg1'] ?>" class="tablerow" colspan="2"><?= $lang['textlostpwnote'] ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textusername'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="username" size="25" maxlength="25" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textemail'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="email" size="25" /><br /></td>
</tr>
<tr class="ctrtablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input type="submit" class="submit" name="lostpwsubmit" value="<?= $lang['textsubmit'] ?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
