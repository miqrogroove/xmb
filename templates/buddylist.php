<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?= $lang['charset'] ?>" />
<meta name="viewport" content="width=500, initial-scale=1" />
<?= $css ?>
<title><?= $bbname ?> - <?= $lang['textpowered'] ?></title>
<script language="JavaScript" type="text/javascript" src="./js/popup.js"></script>
</head>
<body text="<?= $THEME['text'] ?>">
<table cellspacing="0" cellpadding="0" border="0" width="100%" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category"><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['textbuddylist'] ?></font></strong></td>
</tr>
<tr>
<td class="tablerow" bgcolor="<?= $THEME['altbg1'] ?>">
<table width="98%">
<tr>
<td class="tablerow" bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><strong><?= $lang['textonline'] ?></strong></td>
</tr>
<?= $buddys['online'] ?>
<tr>
<td class="tablerow" bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><strong><?= $lang['textoffline'] ?></strong></td>
</tr>
<?= $buddys['offline'] ?>
</table>
</td>
</tr>
<tr>
<td class="tablerow" bgcolor="<?= $THEME['altbg2'] ?>"><strong><a href="buddy.php"><?= $lang['refreshbuddylist'] ?></a></strong></td>
</tr>
</table>
</td>
</tr>
</table>
<br />
<table cellspacing="0" cellpadding="0" border="0" width="100%" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="ctrtablerow" bgcolor="<?= $THEME['altbg1'] ?>"><font class="mediumtxt"><a href="buddy.php?action=edit"><?= $lang['editbuddylist'] ?></a></font></td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>
