<form method="post" name="poll" action="vtmisc.php?action=votepoll&amp;fid=<?= $fid ?>&amp;tid=<?= $tid ?>">
<input type="hidden" name="token" value="" />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr class="category">
<td colspan="3"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['textpoll'] ?> <?= $thread['subject'] ?> <?= $results ?></strong></font></td>
</tr>
<?= $pollhtml ?>
<?= $buttoncode ?>
</table>
</td>
</tr>
</table>
</form>
<br />
