<form method="post" action="<?= $full_url ?>admin/attachments.php">
 <input type="hidden" name="token" value="<?= $token ?>" />
 <div class="xmb-block-wrap admin-attachment-wrap"><div class="xmb-grid admin-attachment-result">
  <div class="row">
   <div class="category-head span"><?= $lang['textattachsearchresults'] ?></div>
  </div>
  <div class="row">
   <div class="cell header"><?= $lang['textfilename'] ?></div>
   <div class="cell header"><?= $lang['textauthor'] ?></div>
   <div class="cell header"><?= $lang['textinthread'] ?></div>
   <div class="cell header"><?= $lang['textlocation'] ?></div>
   <div class="cell header"><?= $lang['textfilesize'] ?></div>
   <div class="cell header"><?= $lang['textdownloads'] ?></div>
  </div>
