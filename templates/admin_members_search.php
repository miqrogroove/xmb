<tr bgcolor="<?= $THEME['altbg2'] ?>">
<td>
<form method="post" action="<?= $full_url ?>admin/members.php?members=search">
<table cellspacing="0" cellpadding="0" border="0" width="90%" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth']?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category" colspan="2"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['textmembers']?></strong></font></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textsrchusr']?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="srchmem" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textsrchemail']?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="srchemail" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textsrchip']?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="srchip" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textwithstatus']?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>">
<select name="srchstatus">
<option value=""><?= $lang['anystatus']?></option>
<option value="Super Administrator"><?= $lang['superadmin']?></option>
<option value="Administrator"><?= $lang['textadmin']?></option>
<option value="Super Moderator"><?= $lang['textsupermod']?></option>
<option value="Moderator"><?= $lang['textmod']?></option>
<option value="Member"><?= $lang['textmem']?></option>
<option value="Inactive"><?= $lang['inactiveUser'] ?></option>
<option value="Banned"><?= $lang['textbanned']?></option>
<option value="Pending"><?= $lang['textpendinglogin']?></option>
</select>
</td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="ctrtablerow" colspan="2"><input type="submit" class="submit" value="<?= $lang['textgo']?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
</td>
</tr>
