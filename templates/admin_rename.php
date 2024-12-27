<tr bgcolor="<?= $THEME['altbg2'] ?>">
<td>
<form action="<?= $full_url ?>admin/rename.php" method="post">
<input type="hidden" name="token" value="<?= $token ?>" />
<table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth']?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category" colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['admin_rename_txt']?></font></strong></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['admin_rename_userfrom']?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="frmUserFrom" size="25" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['admin_rename_userto']?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="frmUserTo" size="25" /></td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="ctrtablerow" colspan="2"><input type="submit" class="submit" name="renamesubmit" value="<?= $lang['admin_rename_txt']?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
</td>
</tr>
