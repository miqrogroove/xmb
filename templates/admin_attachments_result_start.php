<tr bgcolor="<?= $THEME['altbg2'] ?>">
<td align="center">
<form method="post" action="<?= $full_url ?>admin/attachments.php">
<input type="hidden" name="token" value="<?= $token ?>" />
<table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category" colspan="6"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['textattachsearchresults'] ?></strong></font></td>
</tr>
<tr>
<td class="header" width="25%"><?= $lang['textfilename'] ?></td>
<td class="header" width="19%"><?= $lang['textauthor'] ?></td>
<td class="header" width="27%"><?= $lang['textinthread'] ?></td>
<td class="header" width="10%"><?= $lang['textlocation'] ?></td>
<td class="header" width="10%"><?= $lang['textfilesize'] ?></td>
<td class="header" width="5%"><?= $lang['textdownloads'] ?></td>
</tr>
