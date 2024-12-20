<form method="post" action="memcp.php?action=favorites">
<input type="hidden" name="token" value="" />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%" class="tablelinks">
<tr>
<td colspan="6" class="category" align="center" <?= $THEME['catbgcode'] ?>><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['textfavorites'] ?></font></strong></td>
</tr>
<tr class="header">
<td align="center" width="4%"><?= $lang['deletebutton'] ?></td>
<td width="4%" align="center"><?= $lang['texticon'] ?></td>
<td width="43%"><?= $lang['textsubject'] ?></td>
<td width="24%"><?= $lang['textforum'] ?></td>
<td width="6%" align="center"><?= $lang['textreplies'] ?></td>
<td width="19%"><?= $lang['textlastpost'] ?></td>
</tr>
<?= $favs ?>
<?= $favsbtn ?>
</table>
</td>
</tr>
</table>
</form>
