<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?= $lang['charset'] ?>" />
<meta name="viewport" content="width=500, initial-scale=1" />
<?= $css ?>
<script type="text/javascript" language="JavaScript" src="./js/buddy.js"></script>
<title><?= $bbname ?> - <?= $lang['textbuddylist'] ?> - <?= $lang['textpowered'] ?></title>
</head>
<body text="<?= $THEME['text'] ?>">
<table cellspacing="0" cellpadding="0" border="0" width="95%" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="header"><?= $lang['textbuddylist'] ?></td>
</tr>
<tr>
<td class="tablerow" bgcolor="<?= $THEME['altbg1'] ?>">
<form action="#" onsubmit="javascript:add();">
<input type="hidden" name="token" value="" />
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
<br />
<input type="submit" name="submit" value="<?= $lang['textadd2u2u'] ?>" />
</form>
</td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>
