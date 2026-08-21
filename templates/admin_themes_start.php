<form method="post" action="<?= $full_url ?>admin/themes.php" name="theme_main">
 <input type="hidden" name="token" value="<?= $themenonce ?>" />
 <div class="xmb-block-wrap admin-themes-wrap">
  <div class="xmb-grid admin-themes">
   <div class="row">
    <div class="category-head"><?= $lang['textdeleteques'] ?></div>
    <div class="category-head"><?= $lang['textthemename'] ?></div>
    <div class="category-head"><?= $lang['numberusing'] ?></div>
   </div>
