<form method="post" action="memcp.php?action=devices">
<input type="hidden" name="token" value="" />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td colspan="6" class="category" align="center" <?= $THEME['catbgcode'] ?>><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['devices_category'] ?></font></strong></td>
</tr>
<tr class="header">
<td width="4%" align="center"><?= $lang['deletebutton'] ?></td>
<td width="6%" align="center"><?= $lang['device_id_hdr'] ?></td>
<td width="20%"><?= $lang['device_login_date'] ?></td>
<td width="70%"><?= $lang['device_agent_hdr'] ?></td>
</tr>
<?= $current ?>
<?= $other ?>
<?= $devicesbtn ?>
</table>
</td>
</tr>
</table>
</form>
