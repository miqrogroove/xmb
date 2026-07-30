<form method="post" action="<?= $full_url ?>admin/attachments.php">
 <input type="hidden" name="token" value="<?= $token ?>" />
 <div class="admin-attachment-result">
  <div class="row">
   <div class="category-head"><?= $lang['textattachsearchresults'] ?></div>
  </div>
  <div class="row header">
   <div class="cell"><?= $lang['textfilename'] ?></div>
   <div class="cell"><?= $lang['textauthor'] ?></div>
   <div class="cell"><?= $lang['textinthread'] ?></div>
   <div class="cell"><?= $lang['textlocation'] ?></div>
   <div class="cell"><?= $lang['textfilesize'] ?></div>
   <div class="cell"><?= $lang['textdownloads'] ?></div>
  </div>
