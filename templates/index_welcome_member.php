<div class="xmb-block-wrap index-member-wrap">
 <div class="xmb-grid index-member">
  <div class="row">
   <div class="category-head"><?= $lang['textpersonalfeat'] ?></div>
   <div class="category-head span"><?= $lang['textloggedinas'] ?> <?= $hUsername ?> <a href="<?= $full_url ?>misc.php?action=logout"><?= $lang['welcomelogout'] ?></a></div>
  </div>
  <div class="row ctrtablerow">
   <div class="field nav tablelinks"><a href="<?= $full_url ?>memcp.php"><?= $lang['textusercp'] ?></a></div>
   <div class="field nav tablelinks"><a href="<?= $full_url ?>u2u.php" onclick="Popup(this.href, 'Window', 700, 450); return false;"><?= $lang['textu2umessenger'] ?></a></div>
   <div class="field nav tablelinks"><a href="<?= $full_url ?>buddy.php" onclick="Popup(this.href, 'Window', 450, 400); return false;"><?= $lang['launchbuddylist'] ?></a></div>
  </div>
 </div>
</div>
