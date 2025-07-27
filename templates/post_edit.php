<?= $preview ?>
<form method="post" name="input" action="<?= $full_url ?>post.php?action=edit&amp;fid=<?= $fid ?>&amp;tid=<?= $tid ?>&amp;pid=<?= $pid ?>" enctype="multipart/form-data">
<input type="hidden" name="token" value="" />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td colspan="2" class="category"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['texteditpost'] ?></strong></font></td>
</tr>
<?= $loggedin ?>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textsubject'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="subject" size="45" value="<?= $subject ?>" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['texticon'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $icons ?></td>
</tr>
<?= $bbcodeinsert ?>
<tr>
<td class="tablerow" bgcolor="<?= $THEME['altbg1'] ?>" valign="top"><?= $lang['textmessage'] ?><br /><span class="smalltxt"><?= $lang['texthtmlis'] ?> <?= $allowhtml ?><br />
<?= $lang['textsmiliesare'] ?> <?= $allowsmilies ?><br />
<?= str_replace('$url', $full_url . 'faq.php?page=messages#7', $lang['textbbcodeis']) ?> <?= $allowbbcode ?><br />
<?= $lang['textimgcodeis'] ?> <?= $allowimgcode ?></span></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<table width="100%">
<tr>
<td width="70%" rowspan="2"><textarea rows="12" cols="65" name="message" id="message" onselect="storeCaret(this);" onclick="storeCaret(this);" onkeyup="storeCaret(this);">
<?= $message ?></textarea></td>
<?= $smilieinsert ?>
</tr>
<tr><td class="ctrtablerow smalltxt"><?= $moresmilies ?></td></tr>
</table>
<br />
<div class="postOptions">
 <label><input type="checkbox" name="smileyoff" value="yes" <?= $smileoffcheck ?> /> <?= $lang['textdissmileys'] ?></label>
 <label><input type="checkbox" name="usesig" value="yes" <?= $usesigcheck ?> /> <?= $lang['textusesig'] ?></label>
 <label><input type="checkbox" name="bbcodeoff" value="yes" <?= $codeoffcheck ?> /> <?= $lang['bbcodeoff'] ?></label>
 <label><input type="checkbox" name="delete" value="yes" /> <strong><?= $lang['textdelete'] ?></strong></label>
</div>
</td>
</tr>
<?= $attachfile ?>
<tr>
<td colspan="2" class="ctrtablerow" bgcolor="<?= $THEME['altbg2'] ?>"><input type="submit" class="submit" name="editsubmit" value="<?= $lang['texteditpost'] ?>" />&nbsp;<input type="submit" class="submit" name="previewpost" value="<?= $lang['textpreview'] ?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
<input type="hidden" name="fid" value="<?= $fid ?>" />
<input type="hidden" name="tid" value="<?= $tid ?>" />
<input type="hidden" name="pid" value="<?= $pid ?>" />
</form>
