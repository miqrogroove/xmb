<?= $lang['attachment'] ?> <a href="<?= $post['fileurl'] ?>" onclick="window.open(this.href); return false;"><?= $post['filename'] ?></a> (<?= $attachsize) ?>
<br />
<font class="smalltxt"><?= $lang['textdownloadcount1'] ?> <?= $downloadcount ?> <?= $lang['textdownloadcount2'] ?></font>
