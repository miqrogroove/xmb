<form method="post" action="">
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['captchaverification'] ?></strong></font></td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg1'] ?>" class="tablerow">
 <p><?= $lang['google_captcha_directions'] ?></p>
 <div class="g-recaptcha" data-sitekey="<?= $SETTINGS['google_captcha_sitekey'] ?>"></div>
</td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="ctrtablerow">
 <input type="submit" class="submit" name="gcaptcha" value="<?= $lang['continue_button'] ?>" />
 <input type="hidden" name="step" value="<?= $stepout ?>" />
</td>
</tr>
</table>
</td>
</tr>
</table>
</form>
