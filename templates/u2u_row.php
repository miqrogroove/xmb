<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" align="center"><input type="checkbox" name="u2u_select[]" value="<?= $u2u['u2uid'] ?>" /><input type="hidden" name="type<?= $u2u['u2uid'] ?>" value="<?= $u2u['type'] ?>" /></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $u2ureadstatus ?></td>
<td bgcolor="<?= $THEME['altbg1'] ?>"><a href="u2u.php?action=view&amp;folder=<?= $folderrecode ?>&amp;u2uid=<?= $u2u['u2uid'] ?>"><?= $u2usubject ?></a></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $u2usent ?></td>
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $u2udateline ?></td>
</tr>
