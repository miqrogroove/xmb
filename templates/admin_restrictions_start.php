<form method="post" action="<?= $full_url ?>admin/restrictions.php">
 <input type="hidden" name="token" value="<?= $token ?>" />
 <div class="xmb-block-wrap admin-restrictions-wrap">
  <div class="xmb-grid admin-restrictions">
   <div class="row">
    <div class="category-head"><?= $lang['textdeleteques'] ?></div>
    <div class="category-head"><?= $lang['restrictedname'] ?></div>
    <div class="category-head">case-sensitive</div>
    <div class="category-head">partial-match</div>
   </div>
