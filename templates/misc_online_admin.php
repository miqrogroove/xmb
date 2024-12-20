<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category" colspan="4">
<font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['whosonline'] ?></strong></font> <font color="<?= $THEME['cattext'] ?>"><strong>-</strong></font> <a href="javascript:history.go(0)"><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['refreshpage'] ?></font></strong></a>
</td>
</tr>
<?= $multipage ?>
<tr class="header" align="center">
<td><?= $lang['textusername'] ?></td>
<td><?= $lang['texttime'] ?></td>
<td><?= $lang['textlocation'] ?></td>
<td><?= $lang['textipaddress'] ?></td>
</tr>
<?= $onlineusers ?>
<?= $multipage ?>
</table>
</td>
</tr>
</table>
