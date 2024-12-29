<tr bgcolor="<?= $THEME['altbg2'] ?>">
<td align="center">
<form method="post" action="<?= $full_url ?>admin/search.php">
<table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr class="category">
<td><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['insertdata'] ?>:</font></strong></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td valign="top"><div align="center"><br />
<?= $lang['userip'] ?><br /><input type="text" name="userip" /><br /><br />
<?= $lang['postip'] ?><br /><input type="text" name="postip" /><br /><br />
<?= $lang['profileword'] ?><br /><input type="text" name="profileword" /><br /><br />
<?= $lang['postword'] ?><br />
<?= $select ?>
<br />
<br />
</div>
<div align="center"><br /><input type="submit" class="submit" name="searchsubmit" value="<?= $lang['cpsearch']; ?>" /><br /><br /></div>
</td>
</tr>
</table>
</td></tr></table>
</form>
</td>
</tr>
