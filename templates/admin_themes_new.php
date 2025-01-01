<tr bgcolor="<?= $THEME['altbg2'] ?>">
<td align="center">
<form method="post" action="<?= $full_url ?>admin/themes.php?single=submit">
<input type="hidden" name="token" value="<?= $token ?>" />
<table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['texthemename'] ?></td>
<td><input type="text" name="namenew" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textbgcolor'] ?></td>
<td><input type="text" name="bgcolornew" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textaltbg1'] ?></td>
<td><input type="text" name="altbg1new" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textaltbg2'] ?></td>
<td><input type="text" name="altbg2new" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textlink'] ?></td>
<td><input type="text" name="linknew" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textborder'] ?></td>
<td><input type="text" name="bordercolornew" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textheader'] ?></td>
<td><input type="text" name="headernew" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textheadertext'] ?></td>
<td><input type="text" name="headertextnew" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['texttop'] ?></td>
<td><input type="text" name="topnew" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textcatcolor'] ?></td>
<td><input type="text" name="catcolornew" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textcattextcolor'] ?></td>
<td><input type="text" name="cattextnew" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['texttabletext'] ?></td>
<td><input type="text" name="tabletextnew" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['texttext'] ?></td>
<td><input type="text" name="textnew" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textborderwidth'] ?></td>
<td><input type="text" name="borderwidthnew" size="2" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textwidth'] ?></td>
<td><input type="text" name="tablewidthnew" size="3" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textspace'] ?></td>
<td><input type="text" name="tablespacenew" size="2" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textfont'] ?></td>
<td><input type="text" name="fnew" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textbigsize'] ?></td>
<td><input type="text" name="fsizenew" size="4" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textboardlogo'] ?></td>
<td><input type="text" name="boardlogonew" value="logo.gif" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['imgdir'] ?></td>
<td><input type="text" name="imgdirnew" value="images/new" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['imgdiradm'] ?></td>
<td><input type="text" name="admdirnew" value="images/admin" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['smdir'] ?></td>
<td><input type="text" name="smdirnew" value="images/smilies" /></td>
</tr>
<tr class="ctrtablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input class="submit" type="submit" value="<?= $lang['textsubmitchanges'] ?>" /><input type="hidden" name="newtheme" value="true" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
</td>
</tr>
