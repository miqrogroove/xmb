<?php

declare(strict_types=1);

namespace XMB;

?>
<form method="post" action="<?= $full_url ?>admin/email.php">
 <input type="hidden" name="token" value="<?= $token ?>" />
 <div class="admin-email-wrap"><div class="admin-email">
  <div class="row">
   <div class="category-head">&raquo;&nbsp;<?= $lang['admin_email_settings'] ?></div>
  </div>
<?php
    $admin->printsetting2($lang['adminemail'], 'adminemailnew', $SETTINGS['adminemail'], 50);
?>
  <div class="row">
   <div class="field span"><input class="submit" type="submit" name="settingsubmit" value="<?= $lang['textsubmitchanges'] ?>" /></div>
  </div>
  <div class="row">
   <div class="category-head">&raquo;&nbsp;<?= $lang['admin_email_server'] ?></div>
  </div>
<?php
if ($mailerInConfig) {
    $admin->printsetting5($lang['status'], $lang['mailerInConfig']);
} else {
    $admin->printsetting5($lang['mailerType'], $lang['mailerIntro']);
?>
  <div class="row">
   <div class="label"><label><input type="radio" name="mailerType" value="default" <?= $mailerDefaultSel ?> /><?= $lang['mailerTypeDefault'] ?></label></div>
   <div class="field"><?= $lang['mailerTypeDefaultDetail'] ?></div>
  </div>
  <div class="row">
   <div class="label"><label><input type="radio" name="mailerType" value="native" <?= $mailerNativeSel ?> /><?= $lang['mailerTypeNative'] ?></label></div>
   <div class="field"><?= $lang['mailerTypeNativeDetail'] ?></div>
  </div>
  <div class="row">
   <div class="label"><label><input type="radio" name="mailerType" value="sendmail" <?= $mailerSendmailSel ?> /><?= $lang['mailerTypeSendmail'] ?></label></div>
   <div class="field"><?= $lang['mailerTypeSendmailDetail'] ?></div>
  </div>
  <div class="row">
   <div class="label"><label><input type="radio" name="mailerType" value="symfony" <?= $mailerSymfonySel ?> /><?= $lang['mailerTypeSymfony'] ?></label></div>
   <div class="field"><?= $lang['mailerTypeSymfonyDetail'] ?></div>
  </div>
<?php
    $admin->printsetting2($lang['mailerHost'], 'hostnew', $SETTINGS['mailer_host'], 50);
    $admin->printsetting2($lang['mailerPort'], 'portnew', $SETTINGS['mailer_port'], 6);
    $admin->printsetting2($lang['mailerUsername'], 'usernamenew', $SETTINGS['mailer_username'], 50);
?>
  <div class="row">
   <div class="label"><?= $lang['mailerPassword'] ?></div>
   <div class="field"><input type="text" name="passwordnew" size="50" value="<?= $passwordAttr ?>" /></div>
  </div>
<?php
    $labels = [$lang['textoff'], $lang['automatic'], $lang['texton']];
    $values = ['off', 'auto', 'on'];
    $admin->printsetting3($lang['mailerTLS'], 'tlsnew', $labels, $values, $tlsSel, multi: false);
}
?>
  <div class="row">
   <div class="field span"><input class="submit" type="submit" name="settingsubmit" value="<?= $lang['textsubmitchanges'] ?>" /></div>
  </div>
  <div class="row">
   <div class="category-head">&raquo;&nbsp;<?= $lang['admin_email_dkim'] ?></div>
  </div>
<?php
    $admin->printsetting5($lang['textdesc'], $lang['mailerDkimIntro']);
    $admin->printsetting2($lang['mailerDkimKey'], 'dkimkeynew', $SETTINGS['mailer_dkim_key_path'], 50);
    $admin->printsetting2($lang['mailerDkimDomain'], 'dkimdomainnew', $SETTINGS['mailer_dkim_domain'], 50);
    $admin->printsetting2($lang['mailerDkimSelector'], 'dkimselectornew', $SETTINGS['mailer_dkim_selector'], 50);
?>
  <div class="row">
   <div class="field span"><input class="submit" type="submit" name="settingsubmit" value="<?= $lang['textsubmitchanges'] ?>" /></div>
  </div>
 </div></div>
</form>
