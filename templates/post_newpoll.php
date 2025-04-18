<?= $preview ?>
<form method="post" name="input" action="<?= $full_url ?>post.php?action=newthread&amp;fid=<?= $fid ?>&amp;poll=yes" enctype="multipart/form-data" onsubmit="return disableButton(this);">
<input type="hidden" name="token" value="" />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td colspan="2" class="category"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['textpostnew'] ?></strong></font></td>
</tr>
<?= $loggedin ?>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textsubject'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="subject" size="45" value="<?= $subject ?>" /> </td>
</tr>
<tr class="tablerow"><td bgcolor="<?= $THEME['altbg1'] ?>" valign="top"><?= $lang['texticon'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $icons ?></td>
</tr>
<?= $bbcodeinsert ?>
<tr>
<td class="tablerow" bgcolor="<?= $THEME['altbg1'] ?>" valign="top"><?= $lang['textmessage'] ?><br /><span class="smalltxt">
<?= $lang['texthtmlis'] ?> <?= $allowhtml ?><br />
<?= $lang['textsmiliesare'] ?> <?= $allowsmilies ?><br />
<?= str_replace('$url', $full_url . 'faq.php?page=messages#7', $lang['textbbcodeis']) ?> <?= $allowbbcode ?><br />
<?= $lang['textimgcodeis'] ?> <?= $allowimgcode ?></span></td>
<td class="tablerow" bgcolor="<?= $THEME['altbg2'] ?>"><table width="100%">
<tr>
<td width="70%" rowspan="2"><textarea rows="12" cols="65" name="message" id="message" onselect="storeCaret(this);" onclick="storeCaret(this);" onkeyup="storeCaret(this);">
<?= $message ?></textarea></td>
<?= $smilieinsert ?>
</tr>
<tr><td class="ctrtablerow smalltxt"><?= $moresmilies ?></td></tr>
</table>
<br />
<input type="checkbox" name="smileyoff" value="yes" <?= $smileoffcheck ?> /> <?= $lang['textdissmileys'] ?><br />
<input type="checkbox" name="usesig" value="yes" <?= $usesigcheck ?> /> <?= $lang['textusesig'] ?><br />
<input type="checkbox" name="bbcodeoff" value="yes" <?= $codeoffcheck ?> /> <?= $lang['bbcodeoff'] ?><br />
<input type="checkbox" name="emailnotify" value="yes" <?= $emailnotifycheck ?> /> <?= $lang['textemailnotify'] ?> <?= $topoption ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" valign="top"><?= $lang['pollanswers'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><textarea rows="5" cols="40" name="pollanswers">
<?= $pollanswers ?></textarea></td>
</tr>
<?= $attachfile ?>
<?= $captchapostcheck ?>
<tr>
<td class="ctrtablerow" bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input type="submit" class="submit" name="topicsubmit" value="<?= $lang['textpostnew'] ?>" />&nbsp;<input type="submit" class="submit" name="previewpost" value="<?= $lang['textpreview'] ?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
