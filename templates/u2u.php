<?= $u2uheader ?>
<table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
<tr>
<td width="19%" valign="top" align="center">
<table cellspacing="0" cellpadding="0" border="0" width="100%" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category" colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['textfolders'] ?></font></strong></td>
</tr>
<?= $folderlist ?>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><strong><a href="<?= $full_url ?>u2u.php?action=folders"><?= $lang['textmanagefolders'] ?></a></strong></td>
</tr>
</table>
</td>
</tr>
</table>
</td>
<td width="1%">&nbsp;&nbsp;</td>
<td width="79%" valign="top"><?= $leftpane ?></td>
</tr>
</table>
<?= $u2uquotabar ?>
<?= $u2ufooter ?>
