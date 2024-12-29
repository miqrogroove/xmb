<tr bgcolor="<?= $THEME['altbg2'] ?>">
<td align="center">
<form method="post" action="<?= $full_url ?>admin/ipban.php">
<input type="hidden" name="token" value="<?= $token ?>" />
<div align="center">
<input type="submit" class="submit" name="ipbanenable" value="<?= $lang['ipbanenable'] ?>" />
</div>
</form>
</td>
</tr>
