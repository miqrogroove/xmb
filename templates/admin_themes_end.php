<tr bgcolor="<?= $THEME['altbg2'] ?>">
<td colspan="3"><img src="<?= $full_url ?>images/pixel.gif" alt="" /></td>
</tr>
<tr bgcolor="<?= $THEME['altbg1'] ?>" class="tablerow">
<td colspan="3">
<p>
<a href="#" onclick="setCheckboxes('theme_main', 'theme_delete[]', true); return false;">
    <?= $lang['checkall'] ?>
</a>
 -
<a href="#" onclick="setCheckboxes('theme_main', 'theme_delete[]', false); return false;">
    <?= $lang['uncheckall'] ?>
</a>
 -
<a href="#" onclick="invertSelection('theme_main', 'theme_delete[]'); return false;">
    <?= $lang['invertselection'] ?>
</a>
</p><p>
<a href="<?= $full_url ?>admin/themes.php?single=anewtheme1">
    <strong><?= $lang['textnewtheme'] ?></strong>
</a>
 -
<a href="<?= $full_url ?>admin/themes.php?single=bump">
    <strong><?= $lang['themes_bump'] ?></strong>
</a>
</p>
</td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="ctrtablerow" colspan="3"><input type="submit" name="themesubmit" value="<?= $lang['textsubmitchanges'] ?>" class="submit" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
<br />
<form method="post" action="themes.php" enctype="multipart/form-data">
<input type="hidden" name="token" value="<?= $themenonce ?>" />
<table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr class="header">
<td colspan="2"><?= $lang['textimporttheme'] ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textthemefile'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input name="themefile" type="file" /></td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow" align="center" colspan="2"><input type="submit" class="submit" name="importsubmit" value="<?= $lang['textimporttheme']; ?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
</td>
</tr>
