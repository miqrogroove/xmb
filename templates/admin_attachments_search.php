<form method="post" action="<?= $full_url ?>admin/attachments.php">
 <div class="xmb-block-flex xmb-block-narrow-550">
  <div class="xmb-category-head-row-flex"><?= $lang['textsearch'] ?></div>

  <div class="xmb-flex-form-label"><?= $lang['attachmanwherename'] ?></div>
  <div class="xmb-flex-form-field"><input type="text" name="filename" size="30" /></div>

  <div class="xmb-flex-form-label"><?= $lang['attachmanwhereauthor'] ?></div>
  <div class="xmb-flex-form-field"><input type="text" name="author" size="30" /></div>

  <div class="xmb-flex-form-label"><?= $lang['attachmanwhereforum'] ?></div>
  <div class="xmb-flex-form-field"><?= $forumselect?></div>

  <div class="xmb-flex-form-label"><?= $lang['attachmanwheresizesmaller'] ?></div>
  <div class="xmb-flex-form-field"><input type="text" name="sizeless" size="10" /></div>

  <div class="xmb-flex-form-label"><?= $lang['attachmanwheresizegreater'] ?></div>
  <div class="xmb-flex-form-field"><input type="text" name="sizemore" size="10" /></div>

  <div class="xmb-flex-form-label"><?= $lang['attachmanwheredlcountsmaller'] ?></div>
  <div class="xmb-flex-form-field"><input type="text" name="dlcountless" size="10" /></div>

  <div class="xmb-flex-form-label"><?= $lang['attachmanwheredlcountgreater'] ?></div>
  <div class="xmb-flex-form-field"><input type="text" name="dlcountmore" size="10" /></div>

  <div class="xmb-flex-form-label"><?= $lang['attachmanwheredaysold'] ?></div>
  <div class="xmb-flex-form-field"><input type="text" name="daysold" size="10" /></div>

  <div class="xmb-flex-form-field-span"><input type="submit" name="searchsubmit" class="submit" value="<?= $lang['textsearch'] ?>" /></div>
 </div>
</form>
