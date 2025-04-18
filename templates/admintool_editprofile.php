<form method="post" action="<?= $full_url ?>editprofile.php?user=<?= $userrecode ?>" name="reg">
<input type="hidden" name="token" value="<?= $token ?>" />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td colspan="2" class="category"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['texteditpro'] ?> - <?= $lang['required'] ?></strong></font></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textusername'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $username ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textemail'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="newemail" size="25" value="<?= $email ?>" /><br /><a href="https://gsuite.tools/verify-email?email=<?= $emailURL ?>" onclick="window.open(this.href); return false;"><?= $lang['adminverifyemail'] ?></a></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textstatus'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>">
<?= $userStatus ?>
</td>
</tr>
<tr>
<td colspan="2" class="category"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['pwchange'] ?> - <?= $lang['optional'] ?></strong></font></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textnewpassword'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="password" name="newpassword" size="25" />&nbsp;<?= $lang['pwnote'] ?></td>
</tr>
<tr>
<td colspan="2" class="category"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['texteditpro'] ?> - <?= $lang['optional'] ?></strong></font></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textcusstatus'] ?>:</td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="cusstatus" size="25" value="<?= $custout ?>" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textsite'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="newsite" size="25" value="<?= $site ?>" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textlocation'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="newlocation" size="25" value="<?= $location ?>" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['userprofilemood'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="newmood" size="25" value="<?= $mood ?>" /></td>
</tr>
<?= $avatar ?>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textbio'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><textarea rows="5" cols="45" name="newbio">
<?= $bio ?></textarea></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textsig'] ?><br /><span class="smalltxt"><?= $lang['texthtmlis'] ?> <?= $htmlis ?><br /><?= str_replace('$url', $full_url . 'faq.php?page=messages#7', $lang['textbbcodeis']) ?> <?= $bbcodeis ?></span></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><textarea rows="5" cols="45" name="newsig">
<?= $sig ?></textarea></td>
</tr>
<tr>
<td colspan="2" class="category"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['texteditpro'] ?> - <?= $lang['textoptions'] ?></strong></font></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['texttheme'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $themelist ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textlanguage'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $langfileselect ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textbday'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>">
<select name="month">
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
<input type="text" name="year" size="4" value="<?= $year ?>" />
</td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['texttpp'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="tppnew" size="4" value="<?= $tpp ?>" /> </td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textppp'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="pppnew" size="4" value="<?= $ppp ?>" /> </td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textoptions'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>">
<input type="checkbox" name="newsubs" value="yes" <?= $subschecked ?> /> <?= $lang['subdefault'] ?><br />
<input type="checkbox" name="newsletter" value="yes" <?= $newschecked ?> /> <?= $lang['textgetnews'] ?><br />
<input type="checkbox" name="newinv" value="1" <?= $invchecked ?> /> <?= $lang['textinvisible'] ?><br />
<input type="checkbox" name="saveogu2u" value="yes" <?= $ogu2uchecked ?> /> <?= $lang['textsaveog'] ?><br />
<input type="checkbox" name="emailonu2u" value="yes" <?= $eouchecked ?> /> <?= $lang['textemailonu2u'] ?><br />
<input type="text" name="timeoffset1" size="3" value="<?= $timeOffset ?>" /> <?= $textoffset ?>
</td>
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
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['texttimeformat'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="radio" value="24" name="timeformatnew" <?= $check24 ?> />&nbsp;<?= $lang['text24hour'] ?>&nbsp;<input type="radio" value="12" name="timeformatnew" <?= $check12 ?> />&nbsp;<?= $lang['text12hour'] ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['dateformat'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="dateformatnew" size="25" value="<?= $dateformat ?>" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['editprofile_minfo'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>">
 <strong><?= $lang['editprofile_userid'] ?></strong> <?= $uid ?><br />
 <?= $lang['editprofile_lastlogin'] ?> <?= $lastlogdate ?><br />
 <?= $lang['editprofile_regdate'] ?> <?= $registerdate ?><br />
 <?= $lang['editprofile_regip'] ?> <a href="https://whois.domaintools.com/<?= $regip ?>" onclick="window.open(this.href); return false;"><?= $regip ?></a><br />
 <?= $lang['editprofile_loginfails'] ?> <?= $loginfails ?><br />
 <?= $lang['editprofile_loginfaildate'] ?> <?= $loginfaildate ?><br />
 <?= $lang['editprofile_sessfails'] ?> <?= $sessfails ?><br />
 <?= $lang['editprofile_sessfaildate'] ?> <?= $sessfaildate ?>
</td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['editprosearch'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $postSearchLink ?></td>
</tr>
<tr class="ctrtablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input type="submit" class="submit" name="editsubmit" value="<?= $lang['texteditpro'] ?>"/></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
