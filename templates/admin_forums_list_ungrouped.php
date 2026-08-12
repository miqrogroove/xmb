<?php

declare(strict_types=1);

namespace XMB;

?>
<div class="row">
 <div class="xmb-grid-form-field">
  <span>
   <input type="checkbox" name="delete<?= $forum['fid'] ?>" value="<?= $forum['fid'] ?>" />
  </span>
  <span>
   <input type="text" name="name<?= $forum['fid'] ?>" value="<?= $forum['name'] ?>" /> &nbsp;
  </span>
  <span>
   <label><?= $lang['textorder'] ?> <input type="text" name="displayorder<?= $forum['fid'] ?>" size="2" value="<?= $forum['displayorder'] ?>" /></label> &nbsp;
  </span>
  <span>
   <select name="status<?= $forum['fid'] ?>">
    <option value="on" <?= $on ?>><?= $lang['texton'] ?></option>
    <option value="off" <?= $off ?>><?= $lang['textoff'] ?></option>
   </select> &nbsp;
  </span>
  <span>
   <select name="moveto<?= $forum['fid'] ?>"><option value="" selected="selected">-<?= $lang['textnone'] ?>-</option>
<?php
    if (! isset($subs[$forum['fid']])) { // Ungrouped forum options.
        foreach ($forums[0] as $moveforum) {
            if ($moveforum['fid'] != $forum['fid']) {
                echo "<option value='{$moveforum['fid']}'> &nbsp; &raquo; " . adminStripText($moveforum['name']) . "</option>";
            }
        }
    }
    foreach ($groups as $moveforum) { // Groups and grouped forum options.
        echo "<option value='{$moveforum['fid']}'>" . adminStripText($moveforum['name']) . "</option>";
        if (isset($forums[$moveforum['fid']]) && ! isset($subs[$forum['fid']])) {
            foreach ($forums[$moveforum['fid']] as $moveforum) {
                echo "<option value='{$moveforum['fid']}'> &nbsp; &raquo; " . adminStripText($moveforum['name']) . "</option>";
            }
        }
    }
?>
   </select>
  </span>
  <span>
   <a href="<?= $full_url ?>admin/forums.php?fdetails=<?= $forum['fid'] ?>"><?= $lang['textmoreopts'] ?></a>
  </span>
 </div>
</div>
