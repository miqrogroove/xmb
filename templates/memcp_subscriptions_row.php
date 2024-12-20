<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="4%" align="center"><input type="checkbox" name="delete<?= $fav['tid'] ?>" value="<?= $fav['tid'] ?>" /></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" width="4%" align="center"><?= $fav['icon'] ?></td>
<td bgcolor="<?= $THEME['altbg1'] ?>" width="43%"><a href="viewthread.php?tid=<?= $fav['tid'] ?>"><?= $fav['subject'] ?></a></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><a href="forumdisplay.php?fid=<?= $forum['fid'] ?>"><?= $forum['name'] ?></a></td>
<td bgcolor="<?= $THEME['altbg1'] ?>" width="6%" align="center"><?= $fav['replies'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" width="19%"><a href="viewthread.php?goto=lastpost&amp;tid=<?= $fav['tid'] ?>" title="<?= $lang['altlastpost'] ?>" class="smalltxt"><?= $lastpost ?></a></td>
</tr>
