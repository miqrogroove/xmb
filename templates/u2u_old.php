<?= $u2uheader ?>
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="header" align="center">
<a href="u2u.php"><?= $lang['textu2uinbox'] ?></a> -
<a href="u2u.php?folder=Outbox"><?= $lang['textu2uoutbox'] ?></a> -
<a href="u2u.php?folder=Drafts"><?= $lang['textu2udrafts'] ?></a> -
<a href="u2u.php?folder=Trash"><?= $lang['textu2utrash'] ?></a>  (<a href="u2u.php?action=emptytrash"><?= $lang['textemptytrash'] ?></a>)
</td>
</tr>
</table>
</td>
</tr>
</table>
<br />
<?= $leftpane ?>
<?= $u2uquotabar ?>
<?= $u2ufooter ?>
