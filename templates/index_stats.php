<br />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td align="left" class="category"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['textstats'] ?>:</strong></font></td>
<td align="left" class="category"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['key'] ?></strong></font></td>
</tr>
<tr class="tablerow" bgcolor="<?= $THEME['altbg2'] ?>">
<td width="50%" align="left" valign="top"><?= $indexstats ?><br /><?= $lang['stats4'] ?> <?= $memhtml ?></td>
<td width="50%" align="left" valign="top"><img src="<?= $THEME['imgdir'] ?>/red_folder.gif" alt="<?= $lang['altredfolder'] ?>" /> = <?= $lang['newposts'] ?><br /><img src="<?= $THEME['imgdir'] ?>/folder.gif" alt="<?= $lang['altnormalfolder'] ?>" /> = <?= $lang['nonewposts'] ?></td>
</tr>
</table>
</td>
</tr>
</table>
