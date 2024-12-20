<tr>
<td colspan="2" class="category"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['texteditpro'] ?> - <?= $lang['optional'] ?></strong></font></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textsite'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="newsite" size="25" value="<?= $member['site'] ?>" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textaim'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="newaim" size="25" value="<?= $member['aim'] ?>" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['texticq'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="newicq" size="25" value="<?= $member['icq'] ?>" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textyahoo'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="newyahoo" size="25" value="<?= $member['yahoo'] ?>" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textmsn'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="newmsn" size="25" value="<?= $member['msn'] ?>"/></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textlocation'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="newlocation" size="25" value="<?= $member['location'] ?>" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['memcpmood'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="newmood" size="25" value="<?= $member['mood'] ?>" /></td>
</tr>
<?= $avatar ?>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textbio'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><textarea rows="5" cols="45" name="newbio">
<?= $member['bio'] ?></textarea></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textsig'] ?><br /><span class="smalltxt"><?= $lang['texthtmlis'] ?> <?= $htmlis ?><br /><?= $lang['textbbcodeis'] ?> <?= $bbcodeis ?></span></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><textarea rows="5" cols="45" name="newsig">
<?= $member['sig'] ?></textarea></td>
</tr>
