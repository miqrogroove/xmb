<form method="post" action="" onsubmit="return disableButton(this);" id='myform'>
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['captchaverification'] ?></strong></font></td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg1'] ?>" class="tablerow">
<?php if ($invisible) { ?>
 <p><?= $lang['google_captcha_directions_invisible'] ?></p>
<?php } else { ?>
 <p><?= $lang['google_captcha_directions'] ?></p>
 <div class="g-recaptcha" data-sitekey="<?= $SETTINGS['google_captcha_sitekey'] ?>" data-action="register"></div>
<?php } ?>
</td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="ctrtablerow">
<?php if ($invisible) { ?>
 <input type="submit" class="g-recaptcha submit" data-sitekey="<?= $SETTINGS['google_captcha_sitekey'] ?>" data-action="register" data-callback="recaptchaSubmit" name="gcaptcha" id="gcaptcha" value="<?= $lang['continue_button'] ?>" disabled="disabled" />
<?php } else { ?>
 <input type="submit" class="submit" name="gcaptcha" id="gcaptcha" value="<?= $lang['continue_button'] ?>" disabled="disabled" />
<?php } ?>
 <input type="hidden" name="step" value="<?= $stepout ?>" />
 <input type="hidden" name="token" value="<?= $token ?>" />
</td>
</tr>
</table>
</td>
</tr>
</table>
</form>
<script type="text/javascript">
  function recaptchaLoaded() {
    document.getElementById('gcaptcha').disabled = false;
  }
  function recaptchaSubmit(token) {
    document.getElementById("myform").submit();
  }
</script>
<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?onload=recaptchaLoaded"></script>
