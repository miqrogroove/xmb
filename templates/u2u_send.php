<script type="text/javascript" language="JavaScript">
var sendMode = true;
</script>
<form method="post" action="<?= $full_url ?>u2u.php?action=send">
<input type="hidden" name="token" value="" />
<input name="u2uid" type="hidden" value="<?= $u2uid ?>" />
<?= $u2upreview ?>
<table cellspacing="0" cellpadding="0" border="0" width="<?= $thewidth ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td width="100%" colspan="2" class="category" align="center"><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['textu2u'] ?></font></strong></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textsendto'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="msgto" id="msgto" size="20" value="<?= $username ?>" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textsubject'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="subject" size="35" value="<?= $subject ?>" /></td>
</tr>
<tr class="tablerow">
<td valign="top" bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textmessage'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><textarea rows="10" name="message" id="message" cols="50">
<?= $message ?></textarea><br /></td>
</tr>
<tr>
<td valign="top" class="ctrtablerow" bgcolor="<?= $THEME['altbg1'] ?>" colspan="2">
<input type="submit" class="submit" name="sendsubmit" value="<?= $lang['textsendu2u'] ?>" />
<input type="submit" class="submit" name="savesubmit" value="<?= $lang['textsaveu2u'] ?>" />
<input type="submit" class="submit" name="previewsubmit" value="<?= $lang['textpreviewu2u'] ?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
