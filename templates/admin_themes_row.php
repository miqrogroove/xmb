   <div class="row">
    <div class="field delete"><input type="checkbox" name="theme_delete[]" value="<?= $themeinfo['themeid'] ?>" <?= $disable ?> /></div>
    <div class="field">
     <input type="text" name="theme_name[<?= $themeinfo['themeid'] ?>]" value="<?= $themeinfo['name'] ?>" />
     <a href="<?= $full_url ?>admin/themes.php?single=<?= $themeinfo['themeid'] ?>">
     <?= $lang['textdetails'] ?></a>
     -
     <a href="<?= $full_url ?>admin/themes.php?download=<?= $themeinfo['themeid'] ?>">
     <?= $lang['textdownload'] ?>
     </a>
    </div>
    <div class="label count"><?= $members ?></div>
   </div>
