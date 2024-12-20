<tr>
<td bgcolor="<?= $THEME['altbg1'] ?>" class="ctrtablerow" width="4%"><?= $folder ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow" width="54%"><a href="./forumdisplay.php?fid=<?= $forum['fid'] ?>"><font class="mediumtxt"><strong><?= $forum['name'] ?></strong></font><br /><br /><font class="smalltxt"><?= $forum['description'] ?></font></a><span class="smalltxt plainlinks"><?= $forum['moderator'] ?></span><font class="smalltxt"><?= $subforums ?></font></td>
<td bgcolor="<?= $THEME['altbg1'] ?>" class="ctrtablerow" width="6%"><font class="mediumtxt"><?= $forum['threads'] ?></font></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="ctrtablerow" width="6%"><font class="mediumtxt"><?= $forum['posts'] ?></font></td>
<td bgcolor="<?= $THEME['altbg1'] ?>" class="lastpostcell" width="19%"><?= $lastpostrow ?></td>
</tr>
