<tr bgcolor="<?= $THEME['altbg2'] ?>">
<td>
<form method="post" action="<?= $full_url ?>admin/newsletter.php">
<table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
<input type="hidden" name="token" value="<?= $token ?>" />
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr class="category">
<td colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['textnewsletter'] ?></font></strong></td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg1'] ?>" class="tablerow"><?= $lang['textsubject'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow"><input type="text" name="newssubject" size="80" bgcolor="<?= $THEME['altbg1'] ?>" /></td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg1'] ?>" class="tablerow" valign="top"><?= $lang['textmessage'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow"><textarea cols="80" rows="10" name="newsmessage" bgcolor="<?= $THEME['altbg1'] ?>" ></textarea></td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg1'] ?>" class="tablerow" valign="top"><?= $lang['textsendvia'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow"><input type="radio" value="email" name="sendvia" bgcolor="<?= $THEME['altbg1'] ?>" /> <?= $lang['textemail'] ?><br /><input type="radio" value="u2u" checked="checked" name="sendvia" bgcolor="<?= $THEME['altbg1'] ?>" /> <?= $lang['textu2u'] ?></td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg1'] ?>" class="tablerow" valign="top"><?= $lang['textsendto'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow"><input type="radio" value="all" checked="checked" name="to" /> <?= $lang['textsendall'] ?><br />
<input type="radio" value="staff" name="to" /> <?= $lang['textsendstaff'] ?><br />
<input type="radio" value="admin" name="to" /> <?= $lang['textsendadmin'] ?><br />
<input type="radio" value="supermod" name="to" /> <?= $lang['textsendsupermod'] ?><br />
<input type="radio" value="mod" name="to" /> <?= $lang['textsendmod'] ?></td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg1'] ?>" class="tablerow" valign="top"><?= $lang['textfaqextra'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<input type="checkbox" value="yes" checked="checked" name="newscopy" /> <?= $lang['newsreccopy'] ?><br />
<select name="wait" bgcolor="<?= $THEME['altbg1'] ?>">
<option value="0">0</option>
<option value="50">50</option>
<option value="100">100</option>
<option value="150">150</option>
<option value="200">200</option>
<option value="250">250</option>
<option value="500">500</option>
<option value="1000">1000</option>
</select>
<?= $lang['newswait'] ?><br />
</td>
</tr>
<tr>
<td align="center" colspan="2" class="tablerow" bgcolor="<?= $THEME['altbg2'] ?>"><input type="submit" class="submit" name="newslettersubmit" value="<?= $lang['textsubmitchanges'] ?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
</td>
</tr>
