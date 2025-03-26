<table cellspacing="0" cellpadding="0" border="0" width="<?= $thewidth ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="header" colspan="2"><?= $lang['textsubject'] ?> <?= $u2usubject ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="15%"><?= $lang['textfrom'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $u2ufrom ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="15%"><?= $lang['textto'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $u2uto ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="15%"><?= $lang['textu2ufolder'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $u2ufolder ?></td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg1'] ?>" class="tablerow" width="15%"><?= $lang['textsent'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="smalltxt"><?= $u2udateline ?></td>
</tr>
<tr class="tablerow" valign="top">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="15%"><?= $lang['textmessage'] ?><?= $u2uavatar ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $u2umessage ?></td>
</tr>
</table>
</td>
</tr>
</table>
<table cellspacing="2" cellpadding="2" border="0" align="center">
<tr>
<td align="right" class="tablerow"><a href="<?= $full_url ?>u2u.php?action=printable&amp;u2uid=<?= $u2uid ?>" onclick="window.open(this.href); return false;"><?= $lang['textprintver'] ?></a></td>
</tr>
</table>
<table cellspacing="0" cellpadding="0" border="0" width="<?= $thewidth ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<form method="post" action="<?= $full_url ?>u2u.php?action=modif">
<input type="hidden" name="token" value="" />
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category"><font color="<?= $THEME['cattext'] ?>"><?= $lang['textu2uoptions'] ?></font></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>">
<input type="hidden" name="u2uid" value="<?= $u2uid ?>" />
<input type="hidden" name="folder" value="<?= $u2ufolder ?>" />
<input type="hidden" name="type" value="<?= $type ?>" />
<input type="radio" name="mod" value="delete" <?= $delchecked ?> /> <?= $lang['deletebutton'] ?><br />
<?= $sendoptions ?>
<?php if ($u2ufolder !== 'Outbox') { ?>
<input type="radio" name="mod" value="markunread" /> <?= $lang['textu2umarkunread'] ?><br />
<?php } ?>
<input type="radio" name="mod" value="sendtoemail" /> <?= $lang['textforwardu2utoemail'] ?><br />
<input type="radio" name="mod" value="move" /> <?= $lang['textu2umoveto'] ?> <?= $mtofolder ?><br /></td>
</tr>
<tr class="ctrtablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>"><input class="submit" type="submit" name="u2umodsubmit" value="<?= $lang['textsubmitchanges'] ?>" /></td>
</tr>
</table>
</form>
</td>
</tr>
</table>
