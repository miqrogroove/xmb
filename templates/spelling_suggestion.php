<br />
<form action="post.php?fid=<?= $fid ?>&amp;tid=<?= $tid ?>&amp;action=<?= $action ?>&amp;pid=<?= $pid ?>" method="post">
<input type="hidden" name="token" value="" />
<table border="0" cellpadding="0" cellspacing="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" border="0" width="100%" align="center">
<tr>
<td colspan="6" class="category"><font class="mediumtxt" color="<?= $THEME['cattext'] ?>"><strong><?= $lang['spellingchecker'] ?></strong></font></td>
</tr>
<tr class="ctrtablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="50%"><?= $lang['found'] ?>:</td>
<td bgcolor="<?= $THEME['altbg1'] ?>" width="50%"><?= $lang['replacedby'] ?>:</td>
</tr>
<?= $suggestions ?>
<tr>
<td colspan="2" bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
</td>
</tr>
</table>
</td>
</tr>
</table>
</form>
