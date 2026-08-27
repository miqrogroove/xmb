<?php if ($prevpage != '' || $nextpage != '') { ?>
  <div class="row">
   <div class="header span"><?= $firstpage ?> <?= $prevpage ?> <?= $random_var ?> <?= $nextpage ?> <?= $lastpage ?></div>
  </div>
<?php } elseif ($count == 0) { ?>
  <div class="row">
   <div class="header span"><?= $lang['logs_none'] ?></div>
  </div>
<?php } ?>

 </div>
</div>
