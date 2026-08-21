<form action="<?= $full_url ?>admin/rename.php" method="post">
 <input type="hidden" name="token" value="<?= $token ?>" />
 <div class="xmb-block-wrap admin-rename-wrap">
  <div class="xmb-grid admin-rename">
   <div class="row">
    <div class="category-head span"><?= $lang['admin_rename_txt']?></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['admin_rename_userfrom']?></div>
    <div class="field"><input type="text" name="frmUserFrom" size="25" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['admin_rename_userto']?></div>
    <div class="field"><input type="text" name="frmUserTo" size="25" /></div>
   </div>
   <div class="row">
    <div class="field span submit"><input type="submit" class="submit" name="renamesubmit" value="<?= $lang['admin_rename_txt']?>" /></div>
   </div>
  </div>
 </div>
</form>
