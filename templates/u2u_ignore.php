<form method="post" action="u2u.php?action=ignore">
<input type="hidden" name="token" value="" />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $thewidth ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category" colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['ignorelist'] ?></font></strong></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" valign="top"><?= $lang['ignoremsg'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><textarea rows="5" cols="30" name="ignorelist">
<?= $self['ignoreu2u'] ?></textarea></td>
</tr>
<tr>
<td class="ctrtablerow" bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input type="submit" name="ignoresubmit" class="submit" value="<?= $lang['textsubmitchanges'] ?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
