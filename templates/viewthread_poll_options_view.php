<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>" width="30%"><font class="smalltxt"><?= $array['name'] ?></font></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" width="60%">
<img src="<?= $THEME['imgdir'] ?>/pollbar-s.gif" alt="<?= $lang['altpollpercentage'] ?>" title="<?= $lang['altpollpercentage'] ?>" /><?= $pollbar ?><img src="<?= $THEME['imgdir'] ?>/pollbar-e.gif" alt="<?= $lang['altpollpercentage'] ?>" title="<?= $lang['altpollpercentage'] ?>" />
</td>
<td bgcolor="<?= $THEME['altbg2'] ?>" width="10%"><font class="smalltxt"><?= $array['votes'] ?> (<?= $percentage ?>)</font></td>
</tr>