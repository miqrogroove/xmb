<tr bgcolor="<?= $THEME['altbg2'] ?>">
<td align="center">
<form method="post" action="<?= $full_url ?>admin/restrictions.php">
<input type="hidden" name="token" value="<?= $token ?>" />
<table align="center" border="0" cellspacing="0" cellpadding="0" width="80%">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr class="category">
<td><span class="smalltxt"><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['textdeleteques'] ?></font></strong></span></td>
<td><span class="smalltxt"><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['restrictedname'] ?></font></strong></span></td>
<td><span class="smalltxt"><strong><font color="<?= $THEME['cattext'] ?>">case-sensitive</font></strong></span></td>
<td><span class="smalltxt"><strong><font color="<?= $THEME['cattext'] ?>">partial-match</font></strong></span></td>
</tr>
