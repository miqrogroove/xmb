<tr>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow" valign="top"><input type="text" name="filename<?= $attachment['aid'] ?>" value="<?= $attachment['filename'] ?>">
    <br /><span class="smalltxt"><?= $downloadlink ?> - <?= $movelink ?> - <?= $newthumblink ?> - <?= $deletelink ?></span></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow" valign="top"><?= $attachment['author'] ?></td>
<?php if ('0' === $attachment['pid']) { ?>
    <td bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow" valign="top"></td>
<?php } else { ?>
    <td bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow" valign="top"><a href="<?= $full_url ?>viewthread.php?tid=<?= $attachment['tid'] ?>"><?= $attachment['tsubject'] ?></a><br /><span class="smalltxt"><?= $lang['textinforum'] ?> <a href="<?= $full_url ?>forumdisplay.php?fid=<?= $attachment['fid'] ?>"><?= $attachment['fname'] ?></a></span></td>
<?php } ?>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow" valign="top" align="center"><?= $attachment['subdir'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow" valign="top" align="center"><?= $attachsize ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow" valign="top" align="center"><?= $attachment['downloads'] ?></td>
</tr>
