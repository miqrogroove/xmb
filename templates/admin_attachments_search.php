<form method="post" action="<?= $full_url ?>admin/attachments.php">
 <div class="xmb-block-wrap admin-attachment-wrap"><div class="xmb-grid admin-attachment-search">
  <div class="row">
   <div class="category-head"><?= $lang['textsearch'] ?></div>
  </div>
  <div class="row">
   <div class="label"><label for="field1"><?= $lang['attachmanwherename'] ?></label></div>
   <div class="field"><input id="field1" type="text" name="filename" size="30" /></div>
  </div>
  <div class="row">
   <div class="label"><label for="field2"><?= $lang['attachmanwhereauthor'] ?></label></div>
   <div class="field"><input id="field2" type="text" name="author" size="30" /></div>
  </div>
  <div class="row">
   <div class="label"><?= $lang['attachmanwhereforum'] ?></div>
   <div class="field"><?= $forumselect?></div>
  </div>
  <div class="row">
   <div class="label"><label for="field4"><?= $lang['attachmanwheresizesmaller'] ?></label></div>
   <div class="field"><input id="field4" type="text" name="sizeless" size="10" /></div>
  </div>
  <div class="row">
   <div class="label"><label for="field5"><?= $lang['attachmanwheresizegreater'] ?></label></div>
   <div class="field"><input id="field5" type="text" name="sizemore" size="10" /></div>
  </div>
  <div class="row">
   <div class="label"><label for="field6"><?= $lang['attachmanwheredlcountsmaller'] ?></label></div>
   <div class="field"><input id="field6" type="text" name="dlcountless" size="10" /></div>
  </div>
  <div class="row">
   <div class="label"><label for="field7"><?= $lang['attachmanwheredlcountgreater'] ?></label></div>
   <div class="field"><input id="field7" type="text" name="dlcountmore" size="10" /></div>
  </div>
  <div class="row">
   <div class="label"><label for="field8"><?= $lang['attachmanwheredaysold'] ?></label></div>
   <div class="field"><input id="field8" type="text" name="daysold" size="10" /></div>
  </div>
  <div class="row">
   <div class="field span"><input type="submit" name="searchsubmit" class="submit" value="<?= $lang['textsearch'] ?>" /></div>
  </div>
 </div></div>
</form>
