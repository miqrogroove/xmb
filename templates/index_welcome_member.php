<table border="0" cellspacing="0" cellpadding="0" width="<?= $THEME['tablewidth'] ?>" bgcolor="<?= $THEME['bordercolor'] ?>" align="center">
<tr>
<td>
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr class="category">
<td colspan="3">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr class="tablerow">
<td align="left"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['textpersonalfeat'] ?></strong></font></td>
<td align="right"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['textloggedinas'] ?> <?= $hUsername ?></strong></font> <a href="misc.php?action=logout"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['welcomelogout'] ?></strong></font></a></td>
</tr>
</table>
</td>
</tr>
<tr class="ctrtablerow tablelinks" bgcolor="<?= $THEME['altbg2'] ?>">
<td width="33%"><a href="memcp.php"><strong><?= $lang['textusercp'] ?></strong></a></td>
<td width="33%"><a href="u2u.php" onclick="Popup(this.href,'Window', 700, 450); return false;"><strong><?= $lang['textu2umessenger'] ?></strong></a></td>
<td width="33%"><a href="buddy.php" onclick="Popup(this.href, 'Window', 450, 400); return false;"><strong><?= $lang['launchbuddylist'] ?></strong></a></td>
</tr>
</table>
</td>
</tr>
</table>
<br />
