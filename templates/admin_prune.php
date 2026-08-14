<form method="post" action="<?= $full_url ?>admin/prune.php">
 <input type="hidden" name="token" value="<?= $token ?>" />
 <div class="xmb-block-wrap admin-prune-wrap">
  <div class="xmb-grid admin-prune">
   <div class="row">
    <div class="category-head span"><?= $lang['textprune'] ?></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['pruneby'] ?></div>
   </div>
   <div class="row">
    <div class="field option-list">
     <div>
      <input type="checkbox" name="pruneByDate[check]" value="1" checked="checked" />
      <select name="pruneByDate[type]">
       <option value="more"><?= $lang['prunemorethan'] ?></option>
       <option value="is"><?= $lang['pruneexactly'] ?></option>
       <option value="less"><?= $lang['prunelessthan'] ?></option>
      </select>
      <input type="text" name="pruneByDate[date]" value="100" /> <?= $lang['daysold'] ?>
     </div>
     <div>
      <input type="checkbox" name="pruneByPosts[check]" value="1" />
      <select name="pruneByPosts[type]">
       <option value="more"><?= $lang['prunemorethan'] ?></option>
       <option value="is"><?= $lang['pruneexactly'] ?></option>
       <option value="less"><?= $lang['prunelessthan'] ?></option>
      </select>
      <input type="text" name="pruneByPosts[posts]" value="10" /> <?= $lang['memposts'] ?>
     </div>
    </div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['prunefrom'] ?></div>
   </div>
   <div class="row">
    <div class="field option-list">
     <div>
      <label><input type="radio" name="pruneFrom" value="all" /><?= $lang['textallforumsandsubs'] ?></label>
     </div>
     <div class="vertical-center">
      <input type="radio" name="pruneFrom" value="list" />
      <?= $forumselect ?>
     </div>
     <div>
      <input type="radio" name="pruneFrom" value="fid" checked="checked" />
      <?= $lang['prunefids'] ?> <input type="text" name="pruneFromFid" /> <span class="smalltxt">(<?= $lang['seperatebycomma'] ?>)</span>
     </div>
    </div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['pruneposttypes'] ?></div>
   </div>
   <div class="row">
    <div class="field">
     <div><label><input type="checkbox" name="pruneType[normal]" value="1" checked="checked" /> <?= $lang['prunenormal'] ?></label></div>
     <div><?= $lang['textor'] ?></div>
     <div><label><input type="checkbox" name="pruneType[closed]" value="1" checked="checked" /> <?= $lang['pruneclosed'] ?></label></div>
     <div><?= $lang['textor'] ?></div>
     <div><label><input type="checkbox" name="pruneType[topped]" value="1" /> <?= $lang['prunetopped'] ?></label></div>
    </div>
   </div>
   <div class="row">
    <div class="field span submit"><input type="submit" name="pruneSubmit" value="<?= $lang['textprune'] ?>" /></div>
   </div>
  </div>
 </div>
</form>
