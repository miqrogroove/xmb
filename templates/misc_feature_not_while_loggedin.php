<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category" colspan="2"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['fnasorry'] ?></strong></font></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="8%" align="center"><img src="<?= $full_url ?><?= $THEME['admdir'] ?>/exclamation.gif" border="0" alt="<?= $lang['featurewarning'] ?>" /></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><span class="smalltxt"><strong><?= $lang['fnasorry2'] ?></strong></span><br /><?= str_replace('$url', $full_url . 'misc.php?action=logout', $lang['plogtuf']) ?><br /></td>
</tr>
</table>
</td>
</tr>
</table>
