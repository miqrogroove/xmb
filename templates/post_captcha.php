<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['verificationnote'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>">
 <img src="misc.php?action=captchaimage&amp;imagehash=<?= $imghash ?>" alt="<?= $lang['captchaverification'] ?>" title="<?= $lang['captchaverification'] ?>" /><br /><br />
 <input type="text" name="imgcode" value="" /><input type="hidden" name="imghash" value="<?= $imghash ?>" /><br />
 <?php if ($SETTINGS['captcha_code_casesensitive'] == 'on') echo $lang['captchacaseon']; ?>
</td>
</tr>
