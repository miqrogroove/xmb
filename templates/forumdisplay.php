<?= $subforums ?>
<table width="<?= $THEME['tablewidth'] ?>" cellspacing="0" cellpadding="0" align="center">
<tr>
<td class="post" align="right">&nbsp;<?= $newtopiclink ?><?= $newpolllink ?></td>
</tr>
</table>
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center" bgcolor="<?= $THEME['bordercolor'] ?>">
<tr>
<td>
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%" class="tablelinks">
<?= $multipage ?>
<tr class="header" align="center">
<td width="4%">&nbsp;</td>
<td width="4%"><?= $lang['texticon'] ?></td>
<td width="47%"><?= $lang['textsubject'] ?></td>
<td width="14%"><?= $lang['textauthor'] ?></td>
<td width="6%"><?= $lang['textreplies'] ?></td>
<td width="6%"><?= $lang['textviews'] ?></td>
<td width="19%"><?= $lang['textlastpost'] ?></td>
</tr>
<?= $threadlist ?>
<tr>
<td colspan="7" class="ctrtablerow" bgcolor="<?= $THEME['altbg2'] ?>">
<?= $sortby ?>
</td>
</tr>
<?= $multipage ?>
</table>
</td>
</tr>
</table>
<table width="<?= $THEME['tablewidth'] ?>" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" align="center" style="margin-top: 3px">
<tr>
<td class="post" align="right">&nbsp;<?= $newtopiclink ?><?= $newpolllink ?></td>
</tr>
</table>
<br />
<table width="<?= $THEME['tablewidth'] ?>" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" align="center"><tr>
<td class="tablerow"><img src="<?= $full_url ?><?= $THEME['imgdir'] ?>/red_folder.gif" alt="<?= $lang['altredfolder'] ?>" />&nbsp;<?= $lang['opennew'] ?> (&nbsp;
<img src="<?= $full_url ?><?= $THEME['imgdir'] ?>/hot_red_folder.gif" alt="<?= $lang['althotredfolder'] ?>" />&nbsp;<?= $hottopic ?>&nbsp;)<br />
<img src="<?= $full_url ?>images/pixel.gif" width="1" height="4" alt="*" /><br/>
<img src="<?= $full_url ?><?= $THEME['imgdir'] ?>/folder.gif" alt="<?= $lang['altfolder'] ?>" />&nbsp;<?= $lang['opentopic'] ?> (&nbsp;
<img src="<?= $full_url ?><?= $THEME['imgdir'] ?>/hot_folder.gif" alt="<?= $lang['althotfolder'] ?>" />&nbsp;<?= $hottopic ?>&nbsp;)<br />
<img src="<?= $full_url ?>images/pixel.gif" width="1" height="4" alt="*" /><br/>
<img src="<?= $full_url ?><?= $THEME['imgdir'] ?>/lock_folder.gif" alt="<?= $lang['altclosedtopic'] ?>" />&nbsp;<?= $lang['locktopic'] ?></td>
</tr>
</table>
