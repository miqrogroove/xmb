<form method="get" action="search.php">
<input type="hidden" name="token" value="" />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td colspan="2" class="category"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['textsearch'] ?></strong></font></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textsearchfor'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>">
  <input type="text" name="srchtxt" size="30" maxlength="40" /><br />
  <input type="radio" name="srchfield" value="body" checked="checked" /><?= $lang['searchbody'] ?><br />
  <input type="radio" name="srchfield" value="subject" /><?= $lang['searchsubject'] ?><br />
</td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textsrchuname'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="srchuname" size="30" maxlength="40" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['srchbyforum'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $forumselect ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textlfrom'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>">
<select name="srchfrom">
<option value="86400"><?= $lang['day1'] ?></option>
<option value="604800"><?= $lang['aweek'] ?></option>
<option value="2592000"><?= $lang['month1'] ?></option>
<option value="7948800"><?= $lang['month3'] ?></option>
<option value="15897600"><?= $lang['month6'] ?></option>
<option value="31536000"><?= $lang['lastyear'] ?></option>
<option value="0" <?= $selHTML ?>><?= $lang['beginning'] ?></option>
</select>
</td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['srchfilter_double'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="checkbox" name="filter_distinct" value="yes" checked="checked" /></td>
</tr>
<?= $captchasearchcheck ?>
<tr class="ctrtablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input type="submit" class="submit" name="searchsubmit" value="<?= $lang['textsearch'] ?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
