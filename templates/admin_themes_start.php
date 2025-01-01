<tr bgcolor="<?= $THEME['altbg2'] ?>">
<td>
<form method="post" action="<?= $full_url ?>admin/themes.php" name="theme_main">
<input type="hidden" name="token" value="<?= $themenonce ?>" />
<table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr class="category">
<td align="center"><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['textdeleteques'] ?></font></strong></td>
<td><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['textthemename'] ?></font></strong></td>
<td><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['numberusing'] ?></font></strong></td>
</tr>
