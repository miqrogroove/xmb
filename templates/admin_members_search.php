<form method="post" action="<?= $full_url ?>admin/members.php?members=search">
 <div class="xmb-block-wrap admin-members-search-wrap">
  <div class="xmb-grid admin-members-search">
   <div class="row">
    <div class="category-head span"><?= $lang['textmembers'] ?></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textsrchusr'] ?></div>
    <div class="field"><input type="text" name="srchmem" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textsrchemail'] ?></div>
    <div class="field"><input type="text" name="srchemail" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textsrchip'] ?></div>
    <div class="field"><input type="text" name="srchip" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textwithstatus'] ?></div>
    <div class="field">
     <select name="srchstatus">
      <option value=""><?= $lang['anystatus'] ?></option>
      <option value="Super Administrator"><?= $lang['superadmin'] ?></option>
      <option value="Administrator"><?= $lang['textadmin'] ?></option>
      <option value="Super Moderator"><?= $lang['textsupermod'] ?></option>
      <option value="Moderator"><?= $lang['textmod'] ?></option>
      <option value="Member"><?= $lang['textmem'] ?></option>
      <option value="Lurking"><?= $lang['lurking'] ?></option>
      <option value="Inactive"><?= $lang['inactiveUser'] ?></option>
      <option value="Banned"><?= $lang['textbanned'] ?></option>
      <option value="Pending"><?= $lang['textpendinglogin'] ?></option>
     </select>
    </div>
   </div>
   <div class="row">
    <div class="field span submit"><input type="submit" class="submit" value="<?= $lang['textgo'] ?>" /></div>
   </div>
  </div>
 </div>
</form>
