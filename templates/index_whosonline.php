<br />
<table border="0" cellpadding="0" cellspacing="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr>
<td class="tablerow" colspan="2" width="100%">
<table cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" border="0" width="100%" align="center">
<tr>
<td colspan="2" class="category">
<a href="misc.php?action=online"><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['whosonline'] ?></font></strong></a><font color="<?= $THEME['cattext'] ?>"> - <?= $memonmsg ?></font>
</td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg1'] ?>" colspan="2" class="smalltxt">
<?= $lang['key'] ?>
<span class="status_Super_Administrator"><?= $lang['superadmin'] ?></span> - 
<span class="status_Administrator"><?= $lang['textsendadmin'] ?></span> - 
<span class="status_Super_Moderator"><?= $lang['textsendsupermod'] ?></span> - 
<span class="status_Moderator"><?= $lang['textsendmod'] ?></span> - 
<span class="status_Member"><?= $lang['textsendall'] ?></span><?= $hidden ?>
</td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg1'] ?>" align="center" width="4%">
<img src="<?= $THEME['imgdir'] ?>/online.gif" alt="<?= $lang['whosonline'] ?>" border="0" />
</td>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="mediumtxt">
<?= $memtally ?>&nbsp;
</td>
</tr>
<?= $whosonlinetoday ?>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
