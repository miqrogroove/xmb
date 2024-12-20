<tr class="ctrtablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="50%"><strong><?= $orig ?></strong></td>
<td bgcolor="<?= $THEME['altbg1'] ?>" width="50%"><input type="hidden" name="old_words[]" value="<?= $orig ?>" />
<select name="replace_<?= $orig ?>">
<option value="<?= $orig ?>">--IGNORE--</option>
<?= $mistake ?>
</select>
</td>
</tr>
