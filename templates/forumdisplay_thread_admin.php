<tr>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="ctrtablerow"><?= $folder ?></td>
<td bgcolor="<?= $THEME['altbg1'] ?>" class="ctrtablerow"><?= $thread['icon'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow"><font class="mediumtxt"><a href="viewthread.php?tid=<?= $thread['tid'] ?>"><?= $prefix ?><?= $thread['subject'] ?></a></font></td>
<td bgcolor="<?= $THEME['altbg1'] ?>" class="ctrtablerow"><font class="mediumtxt"><a href = "topicadmin.php?tid=<?= $thread['tid'] ?>&amp;fid=<?= $fid ?>&amp;action=top"><?= $topimage ?></a></font></td>
<td bgcolor="<?= $THEME['altbg1'] ?>" class="ctrtablerow"><?= $authorlink ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="ctrtablerow"><font class="mediumtxt"><?= $thread['replies'] ?></font></td>
<td bgcolor="<?= $THEME['altbg1'] ?>" class="ctrtablerow"><font class="mediumtxt"><?= $thread['views'] ?></font></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="lastpostcell"><?= $lastpostrow ?></td>
<td bgcolor="<?= $THEME['altbg1'] ?>" class="ctrtablerow"><a href="topicadmin.php?tid=<?= $thread['realtid'] ?>&amp;fid=<?= $fid ?>&amp;action=delete"><img src="<?= $THEME['admdir'] ?>/deletetopic.gif" alt="<?= $lang['deletethread'] ?>" border="0" /></a></td>
<td bgcolor="<?= $THEME['altbg1'] ?>" class="ctrtablerow"><input type="checkbox" name="tid[]" value="<?= $thread['realtid'] ?>" /></td>
</tr>
