<tr>
<td bgcolor="<?= $THEME['altbg2'] ?>" colspan="4"><img src="./images/pixel.gif" alt="" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>" colspan="4" align="left">
<table border="0" width="100%">
<tr class="category">
<td colspan="2"><span class="smalltxt"><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['textnewcode'] ?></font></strong></span></td>
</tr>
<tr class="tablerow">
<td colspan="2"><span class="smalltxt"><?= $lang['newrestriction'] ?></span></td>
</tr>
<tr>
<td colspan="2"><span class="smalltxt"><?= $lang['newrestrictionwhy'] ?></span></td>
</tr>
<tr>
<td colspan="2">&nbsp;</td>
</tr>
<tr>
<td><span class="smalltxt">name:</span></td>
<td><input type="text" size="30" name="newname" /></td>
</tr>
<tr>
<td><span class="smalltxt">case-sensitive:</span></td>
<td><input type="checkbox" name="newcase" value="1" /></td>
</tr>
<tr>
<td><span class="smalltxt">partial-match:</span></td>
<td><input type="checkbox" name="newpartial" value="1" checked="checked" /></td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table><br />
<div align="center"><input class="submit" type="submit" name="restrictedsubmit" value="<?= $lang['textsubmitchanges'] ?>" /></div>
</form>
</td>
</tr>
