   <div class="row">
    <div class="field delete"><?php if ($deleteable) { ?><input type="checkbox" name="delete[<?= $rank['id'] ?>]" value="<?= $rank['id'] ?>" /><?php } ?></div>
    <div class="field"><input type="text" name="title[<?= $rank['id'] ?>]" value="<?= $rank['title'] ?>" <?= $staff_disable ?> /></div>
    <div class="field"><input type="text" name="posts[<?= $rank['id'] ?>]" value="<?= $rank['posts'] ?>" <?= $staff_disable ?> size="5" /></div>
    <div class="field"><input type="text" name="stars[<?= $rank['id'] ?>]" value="<?= $rank['stars'] ?>" size="4" /></div>
    <div class="field">
     <select name="allowavatars[<?= $rank['id'] ?>]">
      <option value="yes" <?= $avataryes ?>><?= $lang['texton'] ?></option>
      <option value="no" <?= $avatarno ?>><?= $lang['textoff'] ?></option>
     </select><input type="hidden" name="id[<?= $rank['id'] ?>]" value="<?= $rank['id'] ?>" />
    </div>
    <div class="field"><input type="text" name="avaurl[<?= $rank['id'] ?>]" value="<?= $rank['avatarrank'] ?>" size="20" /></div>
   </div>
