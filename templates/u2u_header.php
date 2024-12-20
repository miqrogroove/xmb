<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?= $lang['charset'] ?>" />
<meta name="viewport" content="width=500, initial-scale=1" />
<title><?= $bbname ?> - <?= $lang['textpowered'] ?></title>
<script language="JavaScript" type="text/javascript" src="./js/u2uheader.js"></script>
<?= $css ?>
</head>
<body text="<?= $THEME['text'] ?>">
<table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="0" width="100%">
<tr>
<td>
<table border="0" cellspacing="0" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr class="mediumtxt">
<td align="center" bgcolor="<?= $THEME['altbg1'] ?>" width="20%"><a href="u2u.php"><img src="<?= $THEME['imgdir'] ?>/inbox.gif" alt="<?= $lang['textu2uinbox'] ?>" border="0" /><br /><?= $lang['textu2uinbox'] ?></a></td>
<td align="center" bgcolor="<?= $THEME['altbg1'] ?>" width="20%"><a href="u2u.php?folder=Outbox"><img src="<?= $THEME['imgdir'] ?>/outbox.gif" alt="<?= $lang['textu2uoutbox'] ?>" border="0" /><br /><?= $lang['textu2uoutbox'] ?></a></td>
<td align="center" bgcolor="<?= $THEME['altbg1'] ?>" width="20%"><a href="u2u.php?action=send"><img src="<?= $THEME['imgdir'] ?>/newu2u.gif"  alt="<?= $lang['textsendu2u'] ?>" border="0" /><br /><?= $lang['textsendu2u'] ?></a></td>
<td align="center" bgcolor="<?= $THEME['altbg1'] ?>" width="20%"><a href="buddy.php" onclick="javascript:aBook();return false;"><img src="<?= $THEME['imgdir'] ?>/address.gif"  alt="<?= $lang['textu2uaddressbook'] ?>" border="0" /><br /><?= $lang['textu2uaddressbook'] ?></a></td>
<td align="center" bgcolor="<?= $THEME['altbg1'] ?>" width="20%"><a href="u2u.php?action=ignore"><img src="<?= $THEME['imgdir'] ?>/locku2u.gif"  alt="<?= $lang['ignorelist'] ?>" border="0" /><br /><?= $lang['ignorelist'] ?></a></td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
<br />
