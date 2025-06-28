<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?= $lang['charset'] ?>" />
<meta name="viewport" content="width=500, initial-scale=1" />
<?= $css ?>
<title><?= $SETTINGS['bbname'] ?> - <?= $lang['textpowered'] ?></title>
<script type="text/javascript" src="<?= $full_url?>js/buddylistedit.js"></script>
</head>
<body text="<?= $THEME['text'] ?>">
<table cellspacing="0" cellpadding="0" border="0" width="95%" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<form method="post" action="<?= $full_url ?>buddy.php?action=delete">
<input type="hidden" name="token" value="" />
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category" colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['editbuddylist'] ?></font></strong></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>" width="5%" align="center"><strong><?= $lang['deletecolon'] ?></strong></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><strong><?= $lang['addressname'] ?></strong></td>
</tr>
<?= $buddys ?>
<tr>
<td class="tablerow" colspan="2" bgcolor="<?= $THEME['altbg2'] ?>"><input type="submit" class="submit" name="editsubmit" value="<?= $lang['addressupdate'] ?>" /><br /></td>
</tr>
</table>
</form>
</td>
</tr>
</table>
<br />
<table cellspacing="0" cellpadding="0" border="0" width="95%" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<form method="post" action="<?= $full_url ?>buddy.php?action=add">
<input type="hidden" name="token" value="" />
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category" colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['addtoaddresses'] ?></font></strong></td>
</tr>
<tr>
<td class="tablerow" bgcolor="<?= $THEME['altbg2'] ?>">
<div id="addresses">
<div id="address_add">
<input type="text" name="buddys[]" size="20"/>
</div>
</div>
</td>
</tr>
<tr>
<td class="tablerow" bgcolor="<?= $THEME['altbg1'] ?>"><a href="#" onclick="javascript:add();return false;">&raquo; <?= $lang['add_buddy'] ?></a></td>
</tr>
<tr>
<td class="tablerow" bgcolor="<?= $THEME['altbg2'] ?>"><input type="submit" class="submit" name="editsubmit" value="<?= $lang['addressupdate'] ?>" /><br /></td>
</tr>
</table>
</form>
</td>
</tr>
</table>
</body>
</html>
