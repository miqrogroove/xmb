<tr bgcolor="<?= $THEME['altbg1'] ?>" class="ctrtablerow">
<td>
<br />
<table cellspacing="0" cellpadding="0" border="0" width="98%" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr class="ctrcategory">
<td valign="top" width="20%"><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['general'] ?></font></strong></td>
<td valign="top" width="20%"><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['textforums'] ?></font></strong></td>
<td valign="top" width="20%"><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['textmembers'] ?></font></strong></td>
<td valign="top" width="20%"><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['look_feel'] ?></font></strong></td>
</tr>
<tr>
<td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?= $THEME['altbg2'] ?>">
&raquo;&nbsp;<a href="<?= $full_url ?>cp2.php?action=attachments"><?= $lang['textattachman'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>cp2.php?action=censor"><?= $lang['textcensors'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>cp2.php?action=newsletter"><?= $lang['textnewsletter'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>admin/search.php"><?= $lang['cpsearch'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>admin/settings.php"><?= $lang['textsettings'] ?></a><br />
</td>
<td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?= $THEME['altbg2'] ?>">
&raquo;&nbsp;<a href="<?= $full_url ?>admin/forums.php"><?= $lang['textforums'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>admin/moderators.php"><?= $lang['textmods'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>cp2.php?action=prune"><?= $lang['textprune'] ?></a><br />
</td>
<td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?= $THEME['altbg2'] ?>">
&raquo;&nbsp;<a href="<?= $full_url ?>admin/ipban.php"><?= $lang['textipban'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>admin/members.php"><?= $lang['textmembers'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>cp2.php?action=ranks"><?= $lang['textuserranks'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>admin/restrictions.php"><?= $lang['cprestricted'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>admin/rename.php"><?= $lang['admin_rename_txt'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>quarantine.php"><?= $lang['moderation_meta_name'] ?></a><br />
</td>
<td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?= $THEME['altbg2'] ?>">
&raquo;&nbsp;<a href="<?= $full_url ?>cp2.php?action=smilies"><?= $lang['smilies'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>cp2.php?action=templates"><?= $lang['templates'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>admin/themes.php"><?= $lang['themes'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>cp2.php?action=lang"><?= $lang['translations'] ?></a><br />
</td>
</tr>
<tr class="ctrcategory">
<td valign="top" width="20%"><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['logs'] ?></font></strong></td>
<td valign="top" width="20%"><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['tools'] ?></font></strong></td>
<td valign="top" width="20%"><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['mysql_tools'] ?></font></strong></td>
<td valign="top" width="20%"><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['textfaqextra'] ?></font></strong></td>
</tr>
<tr>
<td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?= $THEME['altbg2'] ?>">
&raquo;&nbsp;<a href="<?= $full_url ?>cp2.php?action=modlog"><?= $lang['textmodlogs'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>cp2.php?action=cplog"><?= $lang['textcplogs'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>tools.php?action=logsdump"><?= $lang['textlogsdump'] ?></a><br />
</td>
<td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?= $THEME['altbg2'] ?>">
&raquo;&nbsp;<a href="<?= $full_url ?>tools.php?action=fixftotals"><?= $lang['textfixposts'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>tools.php?action=fixlastposts&amp;scope=forumsonly"><?= $lang['textfixlastposts'].' - '.$lang['textforums']; ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>tools.php?action=fixlastposts"><?= $lang['textfixlastposts'].' - '.$lang['threads']; ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>tools.php?action=fixmposts"><?= $lang['textfixmemposts'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>tools.php?action=fixttotals"><?= $lang['textfixthread'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>tools.php?action=fixorphanedthreads"><?= $lang['textfixothreads'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>tools.php?action=fixorphanedattachments"><?= $lang['textfixoattachments'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>tools.php?action=fixorphanedpolls"><?= $lang['textfixopolls'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>tools.php?action=fixorphanedposts"><?= $lang['textfixoposts'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>tools.php?action=updatemoods"><?= $lang['textfixmoods'] ?></a><br />
</td>
<td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?= $THEME['altbg2'] ?>">
&raquo;&nbsp;<a href="<?= $full_url ?>admin/sql.php"><?= $lang['raw_mysql'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>tools.php?action=analyzetables"><?= $lang['analyze'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>tools.php?action=checktables"><?= $lang['textcheck'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>tools.php?action=optimizetables"><?= $lang['optimize'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>tools.php?action=repairtables"><?= $lang['repair'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>tools.php?action=u2udump"><?= $lang['u2udump'] ?></a><br />
&raquo;&nbsp;<a href="<?= $full_url ?>tools.php?action=whosonlinedump"><?= $lang['cpwodump'] ?></a><br />
</td>
<td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?= $THEME['altbg2'] ?>">
</td>
</tr>
</table>
</td>
</tr>
</table>
<br />
</td>
</tr>
