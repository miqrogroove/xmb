<form method="post" action="<?= $full_url ?>memcp.php?action=subscriptions">
<input type="hidden" name="token" value="" />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%" class="tablelinks">
<tr>
<td colspan="6" class="category" align="center" <?= $THEME['catbgcode'] ?>><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['textsubscriptions'] ?></font></strong></td>
</tr>
<?= $multipage ?>
<tr class="header">
<td width="4%" align="center"><?= $lang['deletebutton'] ?></td>
<td width="4%" align="center"><?= $lang['texticon'] ?></td>
<td width="43%"><?= $lang['textsubject'] ?></td>
<td width="24%"><?= $lang['textforum'] ?></td>
<td width="6%" align="center"><?= $lang['textreplies'] ?></td>
<td width="19%"><?= $lang['textlastpost'] ?></td>
</tr>
<?= $subscriptions ?>
<?= $multipage ?>
<?= $subsbtn ?>
</table>
</td>
</tr>
</table>
</form>
