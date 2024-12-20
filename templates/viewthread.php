<?= $poll ?>
<table width="<?= $THEME['tablewidth'] ?>" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" align="center">
<tr>
<td class="smalltxt"><a href="<?= $printable_link ?>" rel="alternate"><?= $lang['textprintver'] ?></a><?= $memcplink ?></td>
<td class="post" align="right" valign="bottom">&nbsp;<?= $newtopiclink$newpolllink$replylink ?></td>
</tr>
</table>
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<?= $multipage ?>
<tr class="header">
<td width="18%"><?= $lang['textauthor'] ?> </td>
<td><?= $lang['textsubject'] ?> <?= $thread['subject'] ?></td>
</tr>
<?= $posts ?>
<?= $multipage ?>
</table>
</td>
</tr>
</table>
<table width="<?= $THEME['tablewidth'] ?>" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" align="center">
<tr>
<td class="post" align="right" style="padding-top: 3px"><?= $newtopiclink$newpolllink$replylink ?></td>
</tr>
<tr>
<td colspan="2"><?= $modoptions ?></td>
</tr>
</table>
<?= $quickreply ?>
