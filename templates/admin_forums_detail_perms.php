<tr class="tablerow">
    <th class="category-head" scope="row"><?= $lang[$statusKey] ?></th>
    <td class="ctrtablerow"><input type="checkbox" name="permsNew[<?= $rawPoll ?>][]" value="<?= $val ?>" <?= ((($perms[$rawPoll] & $val) == $val) ? 'checked="checked"' : ''); ?> <?= $disabled ?> /></td>
    <td class="ctrtablerow"><input type="checkbox" name="permsNew[<?= $rawThread ?>][]" value="<?= $val ?>" <?= ((($perms[$rawThread] & $val) == $val) ? 'checked="checked"' : ''); ?> <?= $disabled ?> /></td>
    <td class="ctrtablerow"><input type="checkbox" name="permsNew[<?= $rawReply ?>][]" value="<?= $val ?>" <?= ((($perms[$rawReply] & $val) == $val) ? 'checked="checked"' : ''); ?> <?= $disabled ?> /></td>
    <td class="ctrtablerow"><input type="checkbox" name="permsNew[<?= $rawView ?>][]" value="<?= $val ?>" <?= ((($perms[$rawView] & $val) == $val) ? 'checked="checked"' : ''); ?> <?= $disabled ?> /></td>
</tr>
