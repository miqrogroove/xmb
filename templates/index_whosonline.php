<div class="xmb-block-wrap index-whosonline-wrap">
 <div class="xmb-block-simple index-whosonline">
  <div class="row">
   <div class="category-head"><a href="<?= $full_url ?>misc.php?action=online"><?= $lang['whosonline'] ?></a></div>
  </div>
  <div class="row">
   <div class="xmb-grid onnow">
    <div class="row">
     <div class="field icon"><img src="<?= $full_url ?><?= $THEME['imgdir'] ?>/online.gif" alt="<?= $lang['whosonline'] ?>" border="0" /></div>
     <div class="field list">
      <p class="now-count"><?= $memonmsg ?></p>
<?php if ($memtally != '') { ?>
      <p><?= $memtally ?></p>
<?php } ?>
     </div>
    </div>
   </div>
  </div>
<?php if ($memtally != '') { ?>
  <div class="row">
   <div class="field key">
    <?= $lang['key'] ?>
    <span class="status_Super_Administrator"><?= $lang['superadmin'] ?></span> - 
    <span class="status_Administrator"><?= $lang['textsendadmin'] ?></span> - 
    <span class="status_Super_Moderator"><?= $lang['textsendsupermod'] ?></span> - 
    <span class="status_Moderator"><?= $lang['textsendmod'] ?></span> - 
    <span class="status_Member"><?= $lang['textsendall'] ?></span><?= $hidden ?>
   </div>
  </div>
<?php } ?>
  <?= $whosonlinetoday ?>
 </div>
</div>
