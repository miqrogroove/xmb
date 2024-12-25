<form method="post" action="memcp.php?action=profile" name="reg">
<input type="hidden" name="token" value="" />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td colspan="2" class="category"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['texteditpro'] ?> - <?= $lang['required'] ?></strong></font></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textusername'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $hUsername ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textemail'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="newemail" size="25" value="<?= $member['email'] ?>" /></td>
</tr>
<tr>
<td colspan="2" class="category"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['pwchange'] ?> - <?= $lang['optional'] ?></strong></font></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textoldpassword'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="password" name="oldpassword" size="25" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textnewpassword'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="password" name="newpassword" size="25" /> <?= $lang['pwnote'] ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textpasswordcf'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="password" name="newpasswordcf" size="25" /></td>
</tr>
<?= $optional ?>
<tr>
<td colspan="2" class="category"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['texteditpro'] ?> - <?= $lang['textoptions'] ?></strong></font></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['texttheme'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $themelist ?> </td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textlanguage'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $langfileselect ?> </td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textbday'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><select name="month">
<option value="" <?= $sel['0'] ?>>&nbsp;</option>
<option value="1" <?= $sel['1'] ?>><?= $lang['textjan'] ?></option>
<option value="2" <?= $sel['2'] ?>><?= $lang['textfeb'] ?></option>
<option value="3" <?= $sel['3'] ?>><?= $lang['textmar'] ?></option>
<option value="4" <?= $sel['4'] ?>><?= $lang['textapr'] ?></option>
<option value="5" <?= $sel['5'] ?>><?= $lang['textmay'] ?></option>
<option value="6" <?= $sel['6'] ?>><?= $lang['textjun'] ?></option>
<option value="7" <?= $sel['7'] ?>><?= $lang['textjul'] ?></option>
<option value="8" <?= $sel['8'] ?>><?= $lang['textaug'] ?></option>
<option value="9" <?= $sel['9'] ?>><?= $lang['textsep'] ?></option>
<option value="10" <?= $sel['10'] ?>><?= $lang['textoct'] ?></option>
<option value="11" <?= $sel['11'] ?>><?= $lang['textnov'] ?></option>
<option value="12" <?= $sel['12'] ?>><?= $lang['textdec'] ?></option>
</select>
<?= $dayselect ?>
<input type="text" name="year" size="4" value="<?= $year ?>" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['texttpp'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="tppnew" size="4" value="<?= $member['tpp'] ?>" /> </td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textppp'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="pppnew" size="4" value="<?= $member['ppp'] ?>" /> </td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textshowemail'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="checkbox" name="newshowemail" value="yes" <?= $checked ?> /> </td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textinvisible'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="checkbox" name="newinv" value="1" <?= $invchecked ?> /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['subdefault'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="checkbox" name="newsubs" value="yes" <?= $subschecked ?> /> </td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textgetnews'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="checkbox" name="newnewsletter" value="yes" <?= $newschecked ?> /> </td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['u2ualert1'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>">
<select name="u2ualert">
<option value="2" <?= $u2uasel2 ?>><?= $lang['u2ualert2'] ?></option>
<option value="1" <?= $u2uasel1 ?>><?= $lang['u2ualert3'] ?></option>
<option value="0" <?= $u2uasel0 ?>><?= $lang['u2ualert4'] ?></option>
</select>
</td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textuseoldu2u'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="checkbox" name="useoldu2u" value="yes" <?= $uou2uchecked ?> />
</td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textsaveog'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="checkbox" name="saveogu2u" value="yes" <?= $ogu2uchecked ?> /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textemailonu2u'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="checkbox" name="emailonu2u" value="yes" <?= $eouchecked ?> /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['texttimeformat'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="radio" value="24" name="timeformatnew" <?= $check24 ?> />&nbsp;<?= $lang['text24hour'] ?>&nbsp;<input type="radio" value="12" name="timeformatnew" <?= $check12 ?> />&nbsp;<?= $lang['text12hour'] ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['dateformat'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="dateformatnew" size="25" value="<?= $member['dateformat'] ?>" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $textoffset ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>">
<?= $timezones ?>
</td>
</tr>
<tr>
<td class="ctrtablerow" bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input type="submit" class="submit" name="editsubmit" value="<?= $lang['texteditpro'] ?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
