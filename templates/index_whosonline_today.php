  <div class="row">
   <div class="category-head"><a href="<?= $full_url ?>misc.php?action=onlinetoday">[+] <?= $last50today ?></a></div>
  </div>
<?php if ($todaymembers != '') { ?>
  <div class="row">
   <div class="field list"><?= $todaymembers ?></div>
  </div>
<?php } ?>
  <div class="row">
   <div class="field today-count"><?= $memontoday ?></div>
  </div>
