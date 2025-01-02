<tr bgcolor="<?= $THEME['altbg2'] ?>">
<td align="center">
<form method="post" action="<?= $full_url ?>admin/smilies.php">
<input type="hidden" name="token" value="<?= $token ?>" />
<table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category" colspan="4" align="left"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['smilies'] ?></strong></font></td>
</tr>
<tr class="header">
<td align="center"><?= $lang['textdeleteques'] ?></td>
<td><?= $lang['textsmiliecode'] ?></td>
<td><?= $lang['textsmiliefile'] ?></td>
<td align="center"><?= $lang['smilies'] ?></td>
</tr>
