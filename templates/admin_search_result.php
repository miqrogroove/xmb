<br />

<div class="admin-search-wrap">
 <div class="admin-search-result">
  <div class="row">
   <div class="row-head span">
    <strong><?= $userFound ?></strong> <?= $userFound == 1 ? $lang['beenfound_singular'] : $lang['beenfound'] ?><br />
   </div>
  </div>

<?php foreach ($userList as $num => $val) { ?>
  <div class="row">
   <div class="row-head">
    <strong><?= ($num + 1) ?>.</strong>
   </div>
   <div class="field">
    <?= $val ?>
   </div>
  </div>
<?php } ?>

  <div class="row">
   <div class="row-head span">
    <strong><?= $msgFound ?></strong> <?= $msgFound == 1 ? $lang['beenfound_post_singular'] : $lang['beenfound_post'] ?><br />
   </div>
  </div>

<?php foreach ($msgList as $num => $val) { ?>
  <div class="row">
   <div class="row-head">
    <strong><?= ($num + 1) ?>.</strong>
   </div>
   <div class="field">
    <?= $val ?>
   </div>
  </div>
<?php } ?>

 </div>
</div>
