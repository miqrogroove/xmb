<form method="post" action="topicadmin.php?action=delete">
<input type="hidden" name="token" value="" />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>"><table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category" colspan="2"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['textdeletethread'] ?></strong></font></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['loggedinuser'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $hUsername ?> <?= $lang['textminilogout'] ?></td>
</tr>
<tr class="ctrtablerow">
<td colspan="2" bgcolor="<?= $THEME['altbg2'] ?>"><input type="hidden" name="fid" value="<?= $fid ?>" /><input type="hidden" name="tid" value="<?= $tid ?>" /><input type="submit" class="submit" name="deletesubmit" value="<?= $lang['textdeletethread'] ?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>