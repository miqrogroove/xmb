<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>">
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td align="center" colspan="2" style="padding-bottom: 3px"><font class="mediumtxt"><?= $usercpwelcome ?></font></td>
</tr>
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>"><table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr class="header">
<td colspan="6" class="category"><font color="<?= $THEME['cattext'] ?>"><?= $lang['textbriefsummary'] ?> <?= $hUsername ?> - [<?= $hStatus ?>]</font></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textusername'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $member['username'] ?></td>
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textposts'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $member['postnum'] ?></td>
<td bgcolor="<?= $THEME['altbg1'] ?>" valign="top"><?= $lang['mcpuid'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" valign="top"><?= $member['uid'] ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" valign="top"><?= $lang['cp_subscription'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" valign="top"><?= $member['newsletter'] ?></td>
<td bgcolor="<?= $THEME['altbg1'] ?>" valign="top"><?= $lang['textemail'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" valign="top"><?= $member['email'] ?></td>
<td bgcolor="<?= $THEME['altbg1'] ?>" valign="top"><?= $lang['mood'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" valign="top"><?= $member['mood'] ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" valign="top"><?= $lang['textstatus'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" valign="top"><?= $member['status'] ?></td>
<td bgcolor="<?= $THEME['altbg1'] ?>" valign="top"><?= $lang['textavatar'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" align="center"><?= $member['avatar'] ?></td>
<td bgcolor="<?= $THEME['altbg1'] ?>" valign="top"><?= $lang['texttheme'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" valign="top"><?= $THEME['name'] ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" valign="top"><?= $lang['textcusstatus'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" valign="top"><?= $customstatus ?></td>
<td bgcolor="<?= $THEME['altbg1'] ?>" valign="top">&nbsp;</td>
<td bgcolor="<?= $THEME['altbg2'] ?>" valign="top">&nbsp;</td>
<td bgcolor="<?= $THEME['altbg1'] ?>" valign="top">&nbsp;</td>
<td bgcolor="<?= $THEME['altbg2'] ?>" valign="top">&nbsp;</td>
</tr>
</table>
</td>
</tr>
</table>
<br />
<br />
</td>
</tr>
</table></td>
</tr>
</table>
<br />
<table width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td valign="top">
<table cellspacing="0" cellpadding="0" border="0" width="100%" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>"><table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['textbuddylist'] ?></strong></font></td>
</tr>
<tr>
<td class="tablerow" bgcolor="<?= $THEME['altbg1'] ?>"><table width="98%">
<tr>
<td class="ctrtablerow" bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><strong><?= $lang['textonline'] ?></strong></td>
</tr>
<?= $buddys['online'] ?>
<tr>
<td class="ctrtablerow" bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><strong><?= $lang['textoffline'] ?></strong></td>
</tr>
<?= $buddys['offline'] ?>
</table>
</td>
</tr>
<tr>
<td class="ctrtablerow" bgcolor="<?= $THEME['altbg2'] ?>"><strong><a href="buddy.php" onclick="Popup(this.href, 'Window', 450, 400); return false;"><?= $lang['launchbuddylist'] ?></a></strong></td>
</tr>
</table>
</td>
</tr>
</table>
</td>
<td valign="top">
<table cellspacing="0" cellpadding="0" border="0" width="100%" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%" class="tablelinks">
<tr class="category">
<td colspan="4"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['textlatestu2us'] ?></strong></font></td>
</tr>
<tr class="header">
<td><?= $lang['textsubject'] ?></td>
<td><?= $lang['textfrom'] ?></td>
<td><?= $lang['textsent'] ?></td>
<td><?= $lang['mcpread'] ?></td>
</tr>
<?= $messages ?>
<tr>
<td colspan="4" class="header" valign="top"><a href="u2u.php" onclick="Popup(this.href,'Window', 700, 450); return false;"><?= $lang['viewcompleteinbox'] ?></a></td>
</tr>
</table>
</td>
</tr>
</table>
<br />
<table cellspacing="0" cellpadding="0" border="0" width="100%" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%" class="tablelinks">
<tr class="category">
<td colspan="5"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['textlatestfavs'] ?></strong></font></td>
</tr>
<tr class="header">
<td align="center" width="4%"><?= $lang['texticon'] ?></td>
<td width="43%"><?= $lang['textsubject'] ?></td>
<td width="24%"><?= $lang['textforum'] ?></td>
<td align="center" width="6%"><?= $lang['textreplies'] ?></td>
<td width="19%"><?= $lang['textlastpost'] ?></td>
</tr>
<?= $favs ?>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
