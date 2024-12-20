<form method="post" action="u2u.php?action=mod" name="u2u_main">
<input type="hidden" name="token" value="" />
<?= $u2ulist ?>
<br />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $thewidth ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>" align="left">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>"><a href="u2u.php" onclick="setCheckboxes('u2u_main', true); return false;"><?= $lang['checkall'] ?></a> -
<a href="u2u.php" onclick="setCheckboxes('u2u_main', false); return false;"><?= $lang['uncheckall'] ?></a> -
<a href="u2u.php" onclick="invertSelection('u2u_main', 'u2u_select[]'); return false;"><?= $lang['invertselection'] ?></a></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><input type="radio" name="modaction" value="delete" /> <?= $lang['deletebutton'] ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><input type="radio" name="modaction" value="move" /> <?= $lang['textu2umoveto'] ?> <?= $mtofolder ?><br /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><input type="radio" name="modaction" value="markunread" /> <?= $lang['textu2umarkunread'] ?>
<input type="hidden" name="folder" value="<?= $folder ?>" /></td>
</tr>
<tr class="ctrtablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>"><input class="submit" type="submit" name="u2umodsubmit" value="<?= $lang['textsubmitchanges'] ?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
