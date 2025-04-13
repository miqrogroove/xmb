<form method="post" action="<?= $full_url ?>search.php">
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
 <td class="category" colspan="2">
  <font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['textsearch'] ?></strong></font>
  <input type="hidden" name="srchtxt" value="<?= $srchtxt ?>" />
  <input type="hidden" name="srchfield" value="<?= $srchfield ?>" />
  <input type="hidden" name="srchuname" value="<?= $srchuname ?>" />
  <input type="hidden" name="srchfrom" value="<?= $srchfrom ?>" />
  <input type="hidden" name="distinct" value="<?= $distinct ?>" />
  <input type="hidden" name="page" value="<?= $page ?>" />
  <input type="hidden" name="f" value="<?= $f ?>" />
 </td>
</tr>
<?= $nextlink ?>
<?= $searchresults ?>
<?= $nextlink ?>
</table>
</td>
</tr>
</table>
</form>
