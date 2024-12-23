<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td colspan="2" class="category"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['textprofor'] ?> <?= $member ?></strong></font></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textusername'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $memberinfo['username'] ?><?= $memberlinks ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textregistered'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $memberinfo['regdate'] ?> (<?= $ppd ?> <?= $lang['textmesperday']) ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textposts'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $memberinfo['postnum'] ?> (<?= $percent% ?> <?= $lang['textoftotposts']) ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" valign="top"><?= $lang['textstatus'] ?><br /><?= $newsitelink ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $showtitle ?><?= $customstatus ?><br /><?= $stars ?><br /><br /><?= $rank['avatarrank'] ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" valign="top"><?= $lang['lastactive'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $lastmembervisittext ?></td>
</tr>
</table>
</td>
</tr>
</table>
<br />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>"><table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td colspan="2" class="category"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['memcp_otherinfo'] ?></strong></font></td>
</tr>
<?= $emailblock ?>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textsite'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><a href="<?= $site ?>" onclick="window.open(this.href); return false;"><?= $site ?></a></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textaim'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><a href="aim:goim?screenname=<?= $memberinfo['aimrecode'] ?>&amp;message=Hi+<?= $memberinfo['aimrecode'] ?>.+Are+you+there+I+got+your+aim+name+from+a+message+board."><?= $memberinfo['aim'] ?></a></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['texticq'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><a href="icq:message?uin=<?= $memberinfo['icq'] ?>" onclick="window.open(this.href); return false;"><?= $memberinfo['icq'] ?></a></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textyahoo'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><a href="ymsgr:sendim?<?= $memberinfo['yahoorecode'] ?>" onclick="window.open(this.href); return false;"><?= $memberinfo['yahoo'] ?></a></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textmsn'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><a href="msnim:chat?<?= $memberinfo['msnrecode'] ?>" onclick="window.open(this.href); return false;"><?= $memberinfo['msn'] ?></a></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textlocation'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $memberinfo['location'] ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textbday'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $memberinfo['bday'] ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" valign="top"><?= $lang['textbio'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $memberinfo['bio'] ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" valign="top"><?= $lang['userprofilemood'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $memberinfo['mood'] ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" valign="top"><?= $lang['textprofforumma'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $topforum ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" valign="top"><?= $lang['textproflastpost'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $lastpost ?></td>
</tr>
</table>
</td>
</tr>
</table>
<br />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td colspan="2" class="category"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['memcp_otheroptions'] ?></strong></font></td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg1'] ?>" colspan="2" class="tablerow"><strong><?= $lang['searchusermsg'] ?></strong> <?= $admin_edit ?></td>
</tr>
</table>
</td>
</tr>
</table>
