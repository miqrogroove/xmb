<form method="post" action="<?= $full_url ?>admin/sql.php" enctype="multipart/form-data">
 <input type="hidden" name="token" value="<?= $token ?>" />
 <div class="xmb-block-wrap admin-sql-wrap">
  <div class="xmb-grid admin-sql">
   <div class="row">
    <div class="category-head"><?= $lang['textupgrade'] ?></div>
   </div>
   <div class="row">
    <div class="field"><?= $lang['upgrade'] ?></div>
   </div>
   <div class="row">
    <div class="field"><textarea cols="80" rows="10" name="upgrade"></textarea></div>
   </div>
   <div class="row">
    <div class="field"><input type="file" name="sql_file" /></div>
   </div>
   <div class="row">
    <div class="field note"><?= $lang['upgradenote'] ?></div>
   </div>
   <div class="row">
    <div class="field submit"><input type="submit" class="submit" name="upgradesubmit" value="<?= $lang['textsubmitchanges'] ?>" /></div>
   </div>
  </div>
 </div>
</form>
