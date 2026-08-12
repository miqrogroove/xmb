<div class="row">
 <div class="xmb-grid-form-field">&nbsp;</div>
</div>
<div class="row">
 <div class="xmb-grid-form-field group">
  <span>
   <input type="checkbox" name="delete<?= $group['fid'] ?>" value="<?= $group['fid'] ?>" />
  </span>
  <span>
   <input type="text" name="name<?= $group['fid'] ?>" value="<?= $group['name'] ?>" /> &nbsp;
  </span>
  <span>
   <label><?= $lang['textorder'] ?> <input type="text" name="displayorder<?= $group['fid'] ?>" size="2" value="<?= $group['displayorder'] ?>" /></label> &nbsp;
  </span>
  <span>
   <select name="status<?= $group['fid'] ?>">
    <option value="on" <?= $on ?>><?= $lang['texton'] ?></option>
    <option value="off" <?= $off ?>><?= $lang['textoff'] ?></option>
   </select>
  </span>
 </div>
</div>
