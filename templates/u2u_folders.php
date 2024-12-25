<form method="post" action="u2u.php?action=folders">
<input type="hidden" name="token" value="" />
<table cellspacing="0" cellpadding="0" border="0" width="100%" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td colspan="2" class="category"><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['folderlist'] ?></font></strong></td>
</tr>

<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" valign="top"><?= $lang['foldermsg'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><textarea rows="5" cols="30" name="u2ufolders">
<?= $hU2ufolders ?></textarea></td>
</tr>
<tr>
<td class="ctrtablerow" colspan="2" bgcolor="<?= $THEME['altbg2'] ?>"><input type="submit" name="folderssubmit" class="submit" value="<?= $lang['textsubmitchanges'] ?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
