<form method="post" action="<?= $full_url ?>forumdisplay.php?fid=<?= $fid ?>">
<input type="hidden" name="token" value="" />
<?= $lang['showtopics'] ?>
&nbsp;&nbsp;
<select name="cusdate">
<option value="86400" <?= $check1 ?>><?= $lang['day1'] ?></option>
<option value="432000" <?= $check5 ?>><?= $lang['day5'] ?></option>
<option value="1296000" <?= $check15 ?>><?= $lang['day15'] ?></option>
<option value="2592000" <?= $check30 ?>><?= $lang['day30'] ?></option>
<option value="5184000" <?= $check60 ?>><?= $lang['day60'] ?></option>
<option value="8640000" <?= $check100 ?>><?= $lang['day100'] ?></option>
<option value="31536000" <?= $checkyear ?>><?= $lang['lastyear'] ?></option>
<option value="0" <?= $checkall ?>><?= $lang['beginning'] ?></option>
</select>
&nbsp;&nbsp;
<?= $lang['sortby'] ?>
&nbsp;&nbsp;
<select name="ascdesc">
<option value="ASC"><?= $lang['asc'] ?></option>
<option value="DESC" selected="selected"><?= $lang['desc'] ?></option>
</select>
&nbsp;&nbsp;<input type="submit" class="submit" value="<?= $lang['textgo'] ?>" />
</form>
