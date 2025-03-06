<tr class="tablerow">
<td width="4%" bgcolor="<?= $THEME['altbg1'] ?>" class="ctrtablerow"><?= $folder ?></td>
<td width="4%" bgcolor="<?= $THEME['altbg2'] ?>" class="ctrtablerow"><?= $thread['icon'] ?></td>
<td width="43%" bgcolor="<?= $THEME['altbg1'] ?>" class="tablerow"><font class="12px"><a href="<?= $full_url ?>viewthread.php?tid=<?= $thread['tid'] ?>"><?= $prefix ?><?= $thread['subject'] ?></a></font></td>
<td width="14%" bgcolor="<?= $THEME['altbg2'] ?>" class="ctrtablerow"><?= $authorlink ?></td>
<td width="14%" bgcolor="<?= $THEME['altbg1'] ?>" class="ctrtablerow"><font class="11px"><a href="<?= $full_url ?>forumdisplay.php?fid=<?= $thread['fid'] ?>"><?= $thread['name'] ?></a></font></td>
<td width="5%" bgcolor="<?= $THEME['altbg2'] ?>" class="ctrtablerow"><font class="11px"><?= $thread['replies'] ?></font></td>
<td width="5%" bgcolor="<?= $THEME['altbg1'] ?>" class="ctrtablerow"><font class="11px"><?= $thread['views'] ?></font></td>
<td width="23%" bgcolor="<?= $THEME['altbg2'] ?>" class="lastpostcell"><?= $lastpostrow ?></td>
</tr>
