<form method="post" action="<?= $full_url ?>topicadmin.php?action=move">
<input type="hidden" name="token" value="<?= $token ?>" />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category" colspan="2"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['textmovemethod1'] ?></strong></font></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['loggedinuser'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" ><?= $hUsername ?> <?= $lang['textminilogout'] ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textmoveto'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $forumselect ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" valign="top"><?= $lang['textmovemethod'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>">
<input type="radio" name="type" value="normal" checked="checked" /> <?= $lang['textmovemethod1'] ?>
<br />
<input type="radio" name="type" value="redirect" /> <?= $lang['textmovemethod2'] ?>
<br />
</td>
</tr>
<tr>
<td colspan="2" class="ctrtablerow" bgcolor="<?= $THEME['altbg2'] ?>"><input type="hidden" name="fid" value="<?= $fid ?>" /><input type="hidden" name="tid" value="<?= $tid ?>" /><input type="submit" class="submit" name="movesubmit" value="<?= $lang['textmovemethod1'] ?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
