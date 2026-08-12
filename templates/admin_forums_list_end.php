<?php

declare(strict_types=1);

namespace XMB;

?>
   <div class="row">
    <div class="xmb-grid-form-label">&nbsp;</div>
   </div>
   <div class="row">
    <div class="xmb-grid-form-field">
     <input type="text" name="newgname" value="<?= $lang['textnewgroup'] ?>" /> &nbsp;
     <label><?= $lang['textorder'] ?> <input type="text" name="newgorder" size="2" /></label> &nbsp;
     <select name="newgstatus">
      <option value="on"><?= $lang['texton'] ?></option>
      <option value="off"><?= $lang['textoff'] ?></option>
     </select>
    </div>
   </div>
   <div class="row">
    <div class="xmb-grid-form-field">
     <input type="text" name="newfname" value="<?= $lang['textnewforum'] ?>" /> &nbsp;
     <label><?= $lang['textorder'] ?> <input type="text" name="newforder" size="2" /></label> &nbsp;
     <select name="newfstatus">
      <option value="on"><?= $lang['texton'] ?></option>
      <option value="off"><?= $lang['textoff'] ?></option>
     </select> &nbsp;
     <select name="newffup">
      <option value="" selected="selected"><?= $lang['textnocat'] ?></option>
<?php
    foreach ($groups as $group) {
        echo "<option value='{$group['fid']}'>" . adminStripText($group['name']) . "</option>";
    }
?>
     </select>
    </div>
   </div>
   <div class="row">
    <div class="xmb-grid-form-field">
     <input type="text" name="newsubname" value="<?= $lang['textnewsubf'] ?>" /> &nbsp;
     <label><?= $lang['textorder'] ?> <input type="text" name="newsuborder" size="2" /></label> &nbsp;
     <select name="newsubstatus">
      <option value="on"><?= $lang['texton'] ?></option>
      <option value="off"><?= $lang['textoff'] ?></option>
     </select> &nbsp;
     <select name="newsubfup">
<?php
    foreach ($forumlist as $group) {
        echo "<option value='{$group['fid']}'>" . adminStripText($group['name']) . "</option>";
    }
?>
     </select>
    </div>
   </div>
   <div class="row">
    <div class="xmb-grid-form-field submit"><input type="submit" name="forumsubmit" value="<?= $lang['textsubmitchanges'] ?>" class="submit" /></div>
   </div>
  </div>
 </div>
</form>
