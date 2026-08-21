   <div class="row">
    <div class="label span">
     <p>
      <a href="#" onclick="setCheckboxes('theme_main', 'theme_delete[]', true); return false;">
         <?= $lang['checkall'] ?>
      </a>
      -
      <a href="#" onclick="setCheckboxes('theme_main', 'theme_delete[]', false); return false;">
         <?= $lang['uncheckall'] ?>
      </a>
      -
      <a href="#" onclick="invertSelection('theme_main', 'theme_delete[]'); return false;">
         <?= $lang['invertselection'] ?>
      </a>
     </p>
     <p>
      <a href="<?= $full_url ?>admin/themes.php?single=anewtheme1">
         <strong><?= $lang['textnewtheme'] ?></strong>
      </a>
      -
      <a href="<?= $full_url ?>admin/themes.php?single=bump">
         <strong><?= $lang['themes_bump'] ?></strong>
      </a>
     </p>
    </div>
   </div>
   <div class="row">
    <div class="field span submit"><input type="submit" name="themesubmit" value="<?= $lang['textsubmitchanges'] ?>" class="submit" /></div>
   </div>
  </div>
 </div>
</form>

<br />
<form method="post" action="<?= $full_url ?>admin/themes.php" enctype="multipart/form-data">
 <input type="hidden" name="token" value="<?= $themenonce ?>" />
 <div class="xmb-block-wrap admin-themes-wrap">
  <div class="xmb-grid admin-themes-import">
   <div class="row">
    <div class="category-head span"><?= $lang['textimporttheme'] ?></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textthemefile'] ?></div>
    <div class="field"><input name="themefile" type="file" /></div>
   </div>
   <div class="row">
    <div class="field span submit"><input type="submit" class="submit" name="importsubmit" value="<?= $lang['textimporttheme']; ?>" /></div>
   </div>
  </div>
 </div>
</form>
