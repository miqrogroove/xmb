<tr>
<td bgcolor="<?= $THEME['altbg1'] ?>" />
<td class="rghttablerow" bgcolor="<?= $THEME['altbg2'] ?>">
 <img src="<?= $full_url ?>misc.php?action=captchaimage&amp;imagehash=<?= $imghash ?>" alt="<?= $lang['captchaverification'] ?>" title="<?= $lang['captchaverification'] ?>" /><br />
 <input type="text" name="imgcode" value="" /><input type="hidden" name="imghash" value="<?= $imghash ?>" /><br />
 <?= $lang['captchacaseon'] ?>
</td>
<td class="tablerow" bgcolor="<?= $THEME['altbg2'] ?>"><?= $lang['verificationnote'] ?></td>
</tr>
