<?= $subforums ?>
<table width="<?= $THEME['tablewidth'] ?>" cellspacing="0" cellpadding="0" align="center">
<tr>
<td class="post" align="right">&nbsp;<?= $newtopiclink$newpolllink ?></td>
</tr>
</table>
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center" bgcolor="<?= $THEME['bordercolor'] ?>">
<tr>
<td>
<form method="post" name="geatneet" action="topicadmin.php?fid=<?= $forum['fid'] ?>">
<input type="hidden" name="token" value="" />
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%" class="tablelinks">
<?= $multipage ?>
<tr class="header" align="center">
<td width="4%"><?= $fadminlink ?></td>
<td width="4%"><?= $lang['texticon'] ?></td>
<td width="45%"><?= $lang['textsubject'] ?></td>
<td width="6%"><?= $lang['topuntop'] ?></td>
<td width="12%"><?= $lang['textauthor'] ?></td>
<td width="6%"><?= $lang['textreplies'] ?></td>
<td width="6%"><?= $lang['textviews'] ?></td>
<td width="10%"><?= $lang['textlastpost'] ?></td>
<td width="4%"><?= $lang['deletecolon'] ?></td>
<td><input type="checkbox" onclick="invertSelection('geatneet', 'tid[]'); return false;" /></td>
</tr>
<?= $threadlist ?>
<tr>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow" colspan="10">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr>
<td class="tablerow" align="right">
<input type="hidden" name="fid" value="<?= $fid ?>" />
<input type="hidden" name="token" value="" />
<label class="mediumtxt"><strong><?= $lang['textadminoptions'] ?></strong>
<select name="action">
<option value="" selected="selected"></option>
<option value="bump"><?= $lang['textbumpthread'] ?></option>
<option value="f_close"><?= $lang['textclosethread'] ?></option>
<option value="copy"><?= $lang['copythread'] ?></option>
<option value="delete"><?= $lang['textdeletethread'] ?></option>
<option value="empty"><?= $lang['textemptythread'] ?></option>
<option value="move"><?= $lang['textmovemethod1'] ?></option>
<option value="f_open"><?= $lang['textopenthread'] ?></option>
<option value="top"><?= $lang['topuntop'] ?></option>
</select>
</label>
&nbsp;&nbsp;
<input type="submit" class="submit" name="submit" value="<?= $lang['textsubmitchanges'] ?>" />
</td>
</tr>
</table>
</td>
</tr>
</table>
</form>
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="ctrtablerow" bgcolor="<?= $THEME['altbg2'] ?>">
<?= $sortby ?>
</td>
</tr>
<?= $multipage3 ?>
</table>
</td>
</tr>
</table>
<table width="<?= $THEME['tablewidth'] ?>" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" align="center" style="margin-top: 3px">
<tr>
<td class="post" align="right">&nbsp;<?= $newtopiclink$newpolllink ?></td>
</tr>
</table>
<br />
<table width="<?= $THEME['tablewidth'] ?>" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" align="center">
<tr>
<td class="tablerow">
<img src="<?= $THEME['imgdir'] ?>/red_folder.gif" alt="<?= $lang['altredfolder'] ?>" />&nbsp;<?= $lang['opennew'] ?> (&nbsp;
<img src="<?= $THEME['imgdir'] ?>/hot_red_folder.gif" alt="<?= $lang['althotredfolder'] ?>" />&nbsp;<?= $hottopic&nbsp;) ?><br />
<img src="./images/pixel.gif" width="1" height="4" alt="*" /><br/>
<img src="<?= $THEME['imgdir'] ?>/folder.gif" alt="<?= $lang['altfolder'] ?>" />&nbsp;<?= $lang['opentopic'] ?> (&nbsp;
<img src="<?= $THEME['imgdir'] ?>/hot_folder.gif" alt="<?= $lang['althotfolder'] ?>" />&nbsp;<?= $hottopic&nbsp;) ?><br />
<img src="./images/pixel.gif" width="1" height="4" alt="*" /><br/>
<img src="<?= $THEME['imgdir'] ?>/lock_folder.gif" alt="<?= $lang['altclosedtopic'] ?>" />&nbsp;<?= $lang['locktopic'] ?></td>
</tr>
</table>
