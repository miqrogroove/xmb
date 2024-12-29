<tr bgcolor="<?= $THEME['altbg2'] ?>">
<td align="center">
<form method="post" action="<?= $full_url ?>admin/ipban.php">
<input type="hidden" name="token" value="<?= $token; ?>" />
<table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
<tr><td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr class="category">
<td><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['textdeleteques'] ?></font></strong></td>
<td><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['textip'] ?>:</font></strong></td>
<td><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['textadded'] ?></font></strong></td>
</tr>
