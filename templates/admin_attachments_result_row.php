  <div class="row">
   <div class="cell"><input type="text" name="filename<?= $attachment['aid'] ?>" value="<?= $attachment['filename'] ?>">
    <br /><span class="smalltxt"><?= $downloadlink ?> - <?= $movelink ?> - <?= $newthumblink ?> - <?= $deletelink ?></span></div>
   <div class="cell"><?= $attachment['author'] ?></div>
<?php if ('0' === $attachment['pid']) { ?>
   <div class="cell"></div>
<?php } else { ?>
   <div class="cell"><a href="<?= $full_url ?>viewthread.php?tid=<?= $attachment['tid'] ?>"><?= $attachment['tsubject'] ?></a><br /><span class="smalltxt"><?= $lang['textinforum'] ?> <a href="<?= $full_url ?>forumdisplay.php?fid=<?= $attachment['fid'] ?>"><?= $attachment['fname'] ?></a></span></div>
<?php } ?>
   <div class="cell"><?= $attachment['subdir'] ?></div>
   <div class="cell"><?= $attachsize ?></div>
   <div class="cell"><?= $attachment['downloads'] ?></div>
  </div>
