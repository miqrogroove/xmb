<tr>
<td bgcolor="<?= $THEME['altbg2'] ?>" align="center" class="tablerow"><input type="checkbox" name="smdelete[<?= $smilie['id'] ?>]" value="1" /></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow"><input type="text" name="smcode[<?= $smilie['id'] ?>]" value="<?= $smilie['code'] ?>" /></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow"><input type="text" name="smurl[<?= $smilie['id'] ?>]" value="<?= $smilie['url'] ?>" /></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" align="center" class="tablerow"><img src="<?= $full_url ?><?= $THEME['smdir'] ?>/<?= $smilie['url'] ?>" alt="<?= $smilie['code'] ?>" /></td>
</tr>
