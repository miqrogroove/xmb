<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td align="center"><input type="checkbox" name="theme_delete[]" value="<?= $themeinfo['themeid'] ?>" <?= $disable ?> /></td>
<td>
<input type="text" name="theme_name[<?= $themeinfo['themeid'] ?>]" value="<?= $themeinfo['name'] ?>" />
<a href="<?= $full_url ?>admin/themes.php?single=<?= $themeinfo['themeid'] ?>">
<?= $lang['textdetails'] ?></a>
-
<a href="<?= $full_url ?>admin/themes.php?download=<?= $themeinfo['themeid'] ?>">
<?= $lang['textdownload'] ?>
</a>
</td>
<td><?= $members ?></td>
</tr>
