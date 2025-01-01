<tr>
<td bgcolor="<?= $THEME['altbg1'] ?>" class="ctrtablerow" align="center"><?= $folder ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow"><a href="forumdisplay.php?fid=<?= $forum['fid'] ?>"><font class="mediumtxt"><strong><?= $forum['name'] ?></strong></font><br /><br /><font class="smalltxt"><?= $forum['description'] ?></font></a><span class="smalltxt plainlinks"><?= $forum['moderator'] ?></span></td>
<td bgcolor="<?= $THEME['altbg1'] ?>" class="ctrtablerow"><font class="mediumtxt"><?= $forum['threads'] ?></font></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="ctrtablerow"><font class="mediumtxt"><?= $forum['posts'] ?></font></td>
<td bgcolor="<?= $THEME['altbg1'] ?>" class="lastpostcell"><?= $lastpostrow ?></td>
</tr>
