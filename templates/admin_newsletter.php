<form method="post" action="<?= $full_url ?>admin/newsletter.php">
 <input type="hidden" name="token" value="<?= $token ?>" />
 <div class="admin-newsletter-wrap">
  <div class="admin-newsletter">
   <div class="row">
    <div class="category-head"><?= $lang['textnewsletter'] ?></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textsubject'] ?></div>
    <div class="field"><input type="text" name="newssubject" size="80" bgcolor="<?= $THEME['altbg1'] ?>" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textmessage'] ?></div>
    <div class="field"><textarea cols="80" rows="10" name="newsmessage" bgcolor="<?= $THEME['altbg1'] ?>" ></textarea></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textsendvia'] ?></div>
    <div class="field">
     <label><input type="radio" value="email" name="sendvia" bgcolor="<?= $THEME['altbg1'] ?>" /> <?= $lang['textemail'] ?></label><br />
     <label><input type="radio" value="u2u" checked="checked" name="sendvia" bgcolor="<?= $THEME['altbg1'] ?>" /> <?= $lang['textu2u'] ?></label>
    </div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textsendto'] ?></div>
    <div class="field">
     <label><input type="radio" value="all" checked="checked" name="to" /> <?= $lang['textsendall'] ?></label><br />
     <label><input type="radio" value="staff" name="to" /> <?= $lang['textsendstaff'] ?></label><br />
     <label><input type="radio" value="admin" name="to" /> <?= $lang['textsendadmin'] ?></label><br />
     <label><input type="radio" value="supermod" name="to" /> <?= $lang['textsendsupermod'] ?></label><br />
     <label><input type="radio" value="mod" name="to" /> <?= $lang['textsendmod'] ?></label>
    </div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textfaqextra'] ?></div>
    <div class="field">
     <label><input type="checkbox" value="yes" checked="checked" name="newscopy" /> <?= $lang['newsreccopy'] ?></label><br />
     <select name="wait" bgcolor="<?= $THEME['altbg1'] ?>">
      <option value="0">0</option>
      <option value="50">50</option>
      <option value="100">100</option>
      <option value="150">150</option>
      <option value="200">200</option>
      <option value="250">250</option>
      <option value="500">500</option>
      <option value="1000">1000</option>
     </select>
     <?= $lang['newswait'] ?><br />
    </div>
   </div>
   <div class="row">
    <div class="field span"><input class="submit" type="submit" name="newslettersubmit" value="<?= $lang['textsubmitchanges'] ?>" /></div>
   </div>
  </div>
 </div>
</form>
