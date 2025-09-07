<tr bgcolor="<?= $THEME['altbg2'] ?>">
<td>
<form method="post" action="<?= $full_url ?>admin/themes.php?single=submit">
<input type="hidden" name="token" value="<?= $token ?>" />
<table cellspacing="0" cellpadding="0" border="0" class="medium-width-box" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr class="category">
<th colspan="3" align="center"><?= $themestuff['name'] ?></th>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['texthemename'] ?></td>
<td colspan="2"><input type="text" name="namenew" value="<?= $themestuff['name'] ?>" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textbgcolor'] ?></td>
<td><input type="text" name="bgcolornew" value="<?= $themestuff['bgcolor'] ?>" /></td>
<td bgcolor="<?= $themestuff['bgcolor'] ?>">&nbsp;</td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textaltbg1'] ?></td>
<td><input type="text" name="altbg1new" value="<?= $themestuff['altbg1'] ?>" /></td>
<td bgcolor="<?= $themestuff['altbg1'] ?>">&nbsp;</td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textaltbg2'] ?></td>
<td><input type="text" name="altbg2new" value="<?= $themestuff['altbg2'] ?>" /></td>
<td bgcolor="<?= $themestuff['altbg2'] ?>">&nbsp;</td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textlink'] ?></td>
<td><input type="text" name="linknew" value="<?= $themestuff['link'] ?>" /></td>
<td bgcolor="<?= $themestuff['link'] ?>">&nbsp;</td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textborder'] ?></td>
<td><input type="text" name="bordercolornew" value="<?= $themestuff['bordercolor'] ?>" /></td>
<td bgcolor="<?= $themestuff['bordercolor'] ?>">&nbsp;</td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textheader'] ?></td>
<td><input type="text" name="headernew" value="<?= $themestuff['header'] ?>" /></td>
<td bgcolor="<?= $themestuff['header'] ?>">&nbsp;</td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textheadertext'] ?></td>
<td><input type="text" name="headertextnew" value="<?= $themestuff['headertext'] ?>" /></td>
<td bgcolor="<?= $themestuff['headertext'] ?>">&nbsp;</td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['texttop'] ?></td>
<td><input type="text" name="topnew" value="<?= $themestuff['top'] ?>" /></td>
<td bgcolor="<?= $themestuff['top'] ?>">&nbsp;</td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textcatcolor'] ?></td>
<td><input type="text" name="catcolornew" value="<?= $themestuff['catcolor'] ?>" /></td>
<td bgcolor="<?= $themestuff['catcolor'] ?>">&nbsp;</td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textcattextcolor'] ?></td>
<td><input type="text" name="cattextnew" value="<?= $themestuff['cattext'] ?>" /></td>
<td bgcolor="<?= $themestuff['cattext'] ?>">&nbsp;</td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['texttabletext'] ?></td>
<td><input type="text" name="tabletextnew" value="<?= $themestuff['tabletext'] ?>" /></td>
<td bgcolor="<?= $themestuff['tabletext'] ?>">&nbsp;</td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['texttext'] ?></td>
<td><input type="text" name="textnew" value="<?= $themestuff['text'] ?>" /></td>
<td bgcolor="<?= $themestuff['text'] ?>">&nbsp;</td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textborderwidth'] ?></td>
<td colspan="2"><input type="text" name="borderwidthnew" value="<?= $themestuff['borderwidth'] ?>" size="2" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textwidth'] ?></td>
<td colspan="2"><input type="text" name="tablewidthnew" value="<?= $themestuff['tablewidth'] ?>" size="3" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textspace'] ?></td>
<td colspan="2"><input type="text" name="tablespacenew" value="<?= $themestuff['tablespace'] ?>" size="2" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textfont'] ?></td>
<td colspan="2"><input type="text" name="fnew" value="<?= htmlspecialchars($themestuff['font']) ?>" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['font_size'] ?></td>
<td colspan="2"><input type="text" name="fsizenew" value="<?= $themestuff['fontsize'] ?>" size="4" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['textboardlogo'] ?></td>
<td colspan="2"><input type="text"  value="<?= $themestuff['boardimg'] ?>" name="boardlogonew" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['imgdir'] ?></td>
<td colspan="2"><input type="text"  value="<?= $themestuff['imgdir'] ?>" name="imgdirnew" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['imgdiradm'] ?></td>
<td colspan="2"><input type="text"  value="<?= $themestuff['admdir'] ?>" name="admdirnew" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td><?= $lang['smdir'] ?></td>
<td colspan="2"><input type="text"  value="<?= $themestuff['smdir'] ?>" name="smdirnew" /></td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="ctrtablerow" colspan="3"><input type="submit" class="submit" value="<?= $lang['textsubmitchanges'] ?>" /><input type="hidden" name="orig" value="<?= $single_int ?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
</td>
</tr>
