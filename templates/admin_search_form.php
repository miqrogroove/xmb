<form method="post" action="<?= $full_url ?>admin/search.php">
 <div class="admin-search-wrap">
  <div class="admin-search-form">
   <div class="row">
    <div class="category-head"><?= $lang['insertdata'] ?>:</div>
   </div>
   <div class="row">
    <div class="field">
     <br />
     <?= $lang['userip'] ?><br /><input type="text" name="userip" /><br /><br />
     <?= $lang['postip'] ?><br /><input type="text" name="postip" /><br /><br />
     <?= $lang['profileword'] ?><br /><input type="text" name="profileword" /><br /><br />
     <?= $lang['postword'] ?><br />
     <?= $select ?>
     <br />
     <br />
     <br />
     <input type="submit" class="submit" name="searchsubmit" value="<?= $lang['cpsearch']; ?>" />
     <br />
     <br />
    </div>
   </div>
  </div>
 </div>
</form>
