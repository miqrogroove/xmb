<form method="post" action="<?= $full_url ?>today.php">
<input type="hidden" name="token" value="" />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%" class="tablelinks">
<tr>
<td colspan="8" class="category"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['navtodaysposts'] ?></strong></font></td>
</tr>
<?= $multipage ?>
<tr class="header" align="center">
<td width="4%">&nbsp;</td>
<td width="4%"><?= $lang['texticon'] ?></td>
<td width="43%"><?= $lang['textsubject'] ?></td>
<td width="14%"><?= $lang['textauthor'] ?></td>
<td width="14%"><?= $lang['textforum'] ?></td>
<td width="5%"><?= $lang['textreplies'] ?></td>
<td width="5%"><?= $lang['textviews'] ?></td>
<td width="23%"><?= $lang['textlastpost'] ?></td>
</tr>
<?= $rows ?>
<tr class="ctrtablerow" bgcolor="<?= $THEME['altbg2'] ?>">
<td colspan="8"><?= $lang['todayshow'] ?>&nbsp;&nbsp;<input type="text" name="daysold" size="3" value="<?= $daysold ?>" />&nbsp;&nbsp;<?= $lang['todaydays'] ?>&nbsp;&nbsp;<input class="submit" type="submit" name="searchsubmit" value="<?= $lang['todaygo'] ?>" /></td>
</tr>
<?= $multipage ?>
</table>
</td>
</tr>
</table>
</form>
