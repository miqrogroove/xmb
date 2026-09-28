<div class="xmb-block-wrap index-stats-wrap">
 <div class="xmb-grid index-stats">
  <div class="row">
   <div class="category-head"><?= $lang['textstats'] ?>:</div>
   <div class="category-head"><?= $lang['key'] ?></div>
  </div>
  <div class="row">
   <div class="field"><?= $indexstats ?><br /><?= $lang['stats4'] ?> <?= $memhtml ?></div>
   <div class="field">
    <img src="<?= $full_url ?><?= $THEME['imgdir'] ?>/red_folder.gif" alt="<?= $lang['altredfolder'] ?>" /> = <?= $lang['newposts'] ?><br />
    <img src="<?= $full_url ?><?= $THEME['imgdir'] ?>/folder.gif" alt="<?= $lang['altnormalfolder'] ?>" /> = <?= $lang['nonewposts'] ?>
   </div>
  </div>
 </div>
</div>
