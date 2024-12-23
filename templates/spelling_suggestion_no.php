<br />
<form action="post.php?fid=<?= $fid ?>&amp;tid=<?= $tid ?>&amp;action=<?= $action ?>" method="post">
<input type="hidden" name="token" value="" />
<table border="0" cellpadding="0" cellspacing="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr>
<td class="tablerow" colspan="2" width="100%">
<table cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" border="0" width="100%" align="center">
<tr>
<td colspan="6" class="category"><font class="mediumtxt" color="<?= $THEME['cattext'] ?>"><strong><?= $lang['spellingchecker'] ?></strong></font></td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg1'] ?>" class="ctrtablerow" colspan="2"><?= $lang['spellingcomplete'] ?><br /><strong><?= $lang['nothingfound'] ?></strong></td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
</form>
