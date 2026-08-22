<form method="post" action="<?= $full_url ?>admin/themes.php?single=submit">
 <input type="hidden" name="token" value="<?= $token ?>" />
 <div class="xmb-block-wrap admin-themes-wrap">
  <div class="xmb-grid admin-themes-new">
   <div class="row">
    <div class="label"><?= $lang['texthemename'] ?></div>
    <div class="field"><input type="text" name="namenew" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textbgcolor'] ?></div>
    <div class="field"><input type="text" name="bgcolornew" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textaltbg1'] ?></div>
    <div class="field"><input type="text" name="altbg1new" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textaltbg2'] ?></div>
    <div class="field"><input type="text" name="altbg2new" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textlink'] ?></div>
    <div class="field"><input type="text" name="linknew" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textborder'] ?></div>
    <div class="field"><input type="text" name="bordercolornew" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textheader'] ?></div>
    <div class="field"><input type="text" name="headernew" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textheadertext'] ?></div>
    <div class="field"><input type="text" name="headertextnew" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['texttop'] ?></div>
    <div class="field"><input type="text" name="topnew" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textcatcolor'] ?></div>
    <div class="field"><input type="text" name="catcolornew" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textcattextcolor'] ?></div>
    <div class="field"><input type="text" name="cattextnew" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['texttabletext'] ?></div>
    <div class="field"><input type="text" name="tabletextnew" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['texttext'] ?></div>
    <div class="field"><input type="text" name="textnew" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textborderwidth'] ?></div>
    <div class="field"><input type="text" name="borderwidthnew" size="2" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textwidth'] ?></div>
    <div class="field"><input type="text" name="tablewidthnew" size="3" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textspace'] ?></div>
    <div class="field"><input type="text" name="tablespacenew" size="2" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textfont'] ?></div>
    <div class="field"><input type="text" name="fnew" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textbigsize'] ?></div>
    <div class="field"><input type="text" name="fsizenew" size="4" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textboardlogo'] ?></div>
    <div class="field"><input type="text" name="boardlogonew" value="logo.gif" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['imgdir'] ?></div>
    <div class="field"><input type="text" name="imgdirnew" value="images/new" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['imgdiradm'] ?></div>
    <div class="field"><input type="text" name="admdirnew" value="images/admin" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['smdir'] ?></div>
    <div class="field"><input type="text" name="smdirnew" value="images/smilies" /></div>
   </div>
   <div class="row">
    <div class="field span submit"><input class="submit" type="submit" value="<?= $lang['textsubmitchanges'] ?>" /><input type="hidden" name="newtheme" value="true" /></div>
   </div>
  </div>
 </div>
</form>
