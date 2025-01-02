<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td class="tablerow" align="center"><?php if ($deleteable) { ?><input type="checkbox" name="delete[<?= $rank['id'] ?>]" value="<?= $rank['id'] ?>" /><?php } ?></td>
<td class="tablerow" align="left"><input type="text" name="title[<?= $rank['id'] ?>]" value="<?= $rank['title'] ?>" <?= $staff_disable ?> /></td>
<td class="tablerow"><input type="text" name="posts[<?= $rank['id'] ?>]" value="<?= $rank['posts'] ?>" <?= $staff_disable ?> size="5" /></td>
<td class="tablerow"><input type="text" name="stars[<?= $rank['id'] ?>]" value="<?= $rank['stars'] ?>" size="4" /></td>
<td class="tablerow"><select name="allowavatars[<?= $rank['id'] ?>]">
<option value="yes" <?= $avataryes ?>><?= $lang['texton'] ?></option>
<option value="no" <?= $avatarno ?>><?= $lang['textoff'] ?></option>
</select><input type="hidden" name="id[<?= $rank['id'] ?>]" value="<?= $rank['id'] ?>" /></td>
<td class="tablerow"><input type="text" name="avaurl[<?= $rank['id'] ?>]" value="<?= $rank['avatarrank'] ?>" size="20" /></td>
</tr>
