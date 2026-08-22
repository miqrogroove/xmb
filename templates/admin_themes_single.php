<?php

declare(strict_types=1);

namespace XMB;

?>
<form method="post" action="<?= $full_url ?>admin/themes.php?single=submit">
 <input type="hidden" name="token" value="<?= $token ?>" />
 <div class="xmb-block-wrap admin-themes-wrap">
  <div class="xmb-grid admin-themes-single">
   <div class="row">
    <div class="category-head span"><?= $themestuff['name'] ?></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['texthemename'] ?></div>
    <div class="field span"><input type="text" name="namenew" value="<?= $themestuff['name'] ?>" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textbgcolor'] ?></div>
    <div class="field"><input type="text" name="bgcolornew" value="<?= $themestuff['bgcolor'] ?>" /></div>
    <div class="field color" style="background-color: <?= $themestuff['bgcolor'] ?>">&nbsp;</div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textaltbg1'] ?></div>
    <div class="field"><input type="text" name="altbg1new" value="<?= $themestuff['altbg1'] ?>" /></div>
    <div class="field color" style="background-color: <?= $themestuff['altbg1'] ?>">&nbsp;</div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textaltbg2'] ?></div>
    <div class="field"><input type="text" name="altbg2new" value="<?= $themestuff['altbg2'] ?>" /></div>
    <div class="field color" style="background-color: <?= $themestuff['altbg2'] ?>">&nbsp;</div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textlink'] ?></div>
    <div class="field"><input type="text" name="linknew" value="<?= $themestuff['link'] ?>" /></div>
    <div class="field color" style="background-color: <?= $themestuff['link'] ?>">&nbsp;</div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textborder'] ?></div>
    <div class="field"><input type="text" name="bordercolornew" value="<?= $themestuff['bordercolor'] ?>" /></div>
    <div class="field color" style="background-color: <?= $themestuff['bordercolor'] ?>">&nbsp;</div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textheader'] ?></div>
    <div class="field"><input type="text" name="headernew" value="<?= $themestuff['header'] ?>" /></div>
    <div class="field color" style="background-color: <?= $themestuff['header'] ?>">&nbsp;</div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textheadertext'] ?></div>
    <div class="field"><input type="text" name="headertextnew" value="<?= $themestuff['headertext'] ?>" /></div>
    <div class="field color" style="background-color: <?= $themestuff['headertext'] ?>">&nbsp;</div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['texttop'] ?></div>
    <div class="field"><input type="text" name="topnew" value="<?= $themestuff['top'] ?>" /></div>
    <div class="field color" style="background-color: <?= $themestuff['top'] ?>">&nbsp;</div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textcatcolor'] ?></div>
    <div class="field"><input type="text" name="catcolornew" value="<?= $themestuff['catcolor'] ?>" /></div>
    <div class="field color" style="background-color: <?= $themestuff['catcolor'] ?>">&nbsp;</div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textcattextcolor'] ?></div>
    <div class="field"><input type="text" name="cattextnew" value="<?= $themestuff['cattext'] ?>" /></div>
    <div class="field color" style="background-color: <?= $themestuff['cattext'] ?>">&nbsp;</div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['texttabletext'] ?></div>
    <div class="field"><input type="text" name="tabletextnew" value="<?= $themestuff['tabletext'] ?>" /></div>
    <div class="field color" style="background-color: <?= $themestuff['tabletext'] ?>">&nbsp;</div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['texttext'] ?></div>
    <div class="field"><input type="text" name="textnew" value="<?= $themestuff['text'] ?>" /></div>
    <div class="field color" style="background-color: <?= $themestuff['text'] ?>">&nbsp;</div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textborderwidth'] ?></div>
    <div class="field span"><input type="text" name="borderwidthnew" value="<?= $themestuff['borderwidth'] ?>" size="2" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textwidth'] ?></div>
    <div class="field span"><input type="text" name="tablewidthnew" value="<?= $themestuff['tablewidth'] ?>" size="3" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textspace'] ?></div>
    <div class="field span"><input type="text" name="tablespacenew" value="<?= $themestuff['tablespace'] ?>" size="2" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textfont'] ?></div>
    <div class="field span"><input type="text" name="fnew" value="<?= htmlEsc($themestuff['font'], storedData: true) ?>" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['font_size'] ?></div>
    <div class="field span"><input type="text" name="fsizenew" value="<?= $themestuff['fontsize'] ?>" size="4" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['textboardlogo'] ?></div>
    <div class="field span"><input type="text"  value="<?= $themestuff['boardimg'] ?>" name="boardlogonew" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['imgdir'] ?></div>
    <div class="field span"><input type="text"  value="<?= $themestuff['imgdir'] ?>" name="imgdirnew" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['imgdiradm'] ?></div>
    <div class="field span"><input type="text"  value="<?= $themestuff['admdir'] ?>" name="admdirnew" /></div>
   </div>
   <div class="row">
    <div class="label"><?= $lang['smdir'] ?></div>
    <div class="field span"><input type="text"  value="<?= $themestuff['smdir'] ?>" name="smdirnew" /></div>
   </div>
   <div class="row">
    <div class="field span submit"><input type="submit" class="submit" value="<?= $lang['textsubmitchanges'] ?>" /><input type="hidden" name="orig" value="<?= $single_int ?>" /></div>
   </div>
  </div>
 </div>
</form>
