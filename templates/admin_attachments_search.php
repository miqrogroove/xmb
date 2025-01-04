<tr bgcolor="<?= $THEME['altbg2'] ?>">
<td align="center">
<form method="post" action="<?= $full_url ?>admin/attachments.php">
<table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
<tr><td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category" colspan="2"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['textsearch'] ?></font></strong></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['attachmanwherename'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="filename" size="30" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['attachmanwhereauthor'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="author" size="40" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['attachmanwhereforum'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $forumselect?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['attachmanwheresizesmaller'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="sizeless" size="20" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['attachmanwheresizegreater'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="sizemore" size="20" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['attachmanwheredlcountsmaller'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="dlcountless" size="20" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['attachmanwheredlcountgreater'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="dlcountmore" size="20" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['attachmanwheredaysold'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="daysold" size="20" /></td>
</tr>
<tr class="ctrtablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input type="submit" name="searchsubmit" class="submit" value="<?= $lang['textsearch'] ?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
</td>
</tr>
