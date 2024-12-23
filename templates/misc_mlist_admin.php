<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%" class="tablelinks">
<tr>
<td colspan="6" class="category"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['textsortby'] ?></strong></font></td>
</tr>
<tr class="ctrtablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>"><a href="misc.php?action=list&amp;order=username<?= $ext ?>"><strong><?= $lang['textalpha'] ?></strong></a></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><a href="misc.php?action=list&amp;order=status<?= $ext ?>"><strong><?= $lang['status'] ?></strong></a></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><a href="misc.php?action=list&amp;order=location<?= $ext ?>"><strong><?= $lang['location'] ?></strong></a></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><a href="misc.php?action=list<?= $ext ?>"><strong><?= $lang['textregdate'] ?></strong></a></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><a href="misc.php?action=list&amp;desc=desc&amp;order=postnum<?= $ext ?>"><strong><?= $lang['textpostnum'] ?></strong></a></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" width="10%"><a href="misc.php?action=list<?= $sflip ?><?= $ext ?>"><strong><?= $ascdesc ?></strong></a></td>
</tr>
</table>
</td>
</tr>
</table>
<br />
<form method="get" action="misc.php">
<input type="hidden" name="token" value="" />
<input type="hidden" name="action" value="list" />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%" class="tablelinks">
<tr>
<td colspan="7" class="category"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['textmemberlist'] ?></strong></font></td>
</tr>
<?= $multipage ?>
<tr class="header" align="center">
<td width="20%"><?= $lang['textusername'] ?></td>
<td width="16%"><?= $lang['status'] ?></td>
<td width="10%"><?= $lang['textemail']: ?></td>
<td width="10%"><?= $lang['textsite'] ?></td>
<td width="19%"><?= $lang['textlocation'] ?></td>
<td width="19%"><?= $lang['textregistered'] ?></td>
<td width="5%"><?= $lang['textposts'] ?></td>
</tr>
<?= $members ?>
<tr class="ctrtablerow" bgcolor="<?= $THEME['altbg2'] ?>">
<td colspan="7"><?= $lang['textsrchusr'] ?>&nbsp;&nbsp;<input type="text" size="15" name="srchmem" value="<?= $srchmem ?>" />&nbsp;&nbsp;<?= $lang['textsrchemail'] ?>&nbsp;&nbsp;<input type="text" size="15" name="srchemail" value="<?= $srchemail ?>" />&nbsp;&nbsp;<?= $lang['textsrchip'] ?>&nbsp;&nbsp;<input type="text" size="15" name="srchip" value="<?= $srchip ?>" />&nbsp;&nbsp;<input type="submit" class="submit" value="<?= $lang['textgo'] ?>" /></td>
</tr>
<?= $multipage ?>
</table>
</td>
</tr>
</table>
</form>
