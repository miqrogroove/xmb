<tr class="tablerow">
    <td class="category" style="color: <?= $THEME['cattext']; ?>; font-weight: bold; text-align: right;"><?= $lang[$statusKey] ?></td>
    <td class="altbg1 ctrtablerow"><input type="checkbox" name="permsNew[<?= X_PERMS_RAWPOLL; ?>][]" value="<?= $val ?>" <?= ((($perms[X_PERMS_RAWPOLL] & $val) == $val) ? 'checked="checked"' : ''); ?> <?= $disabled ?> /></td>
    <td class="altbg1 ctrtablerow"><input type="checkbox" name="permsNew[<?= X_PERMS_RAWTHREAD; ?>][]" value="<?= $val ?>" <?= ((($perms[X_PERMS_RAWTHREAD] & $val) == $val) ? 'checked="checked"' : ''); ?> <?= $disabled ?> /></td>
    <td class="altbg1 ctrtablerow"><input type="checkbox" name="permsNew[<?= X_PERMS_RAWREPLY; ?>][]" value="<?= $val ?>" <?= ((($perms[X_PERMS_RAWREPLY] & $val) == $val) ? 'checked="checked"' : ''); ?> <?= $disabled ?> /></td>
    <td class="altbg1 ctrtablerow"><input type="checkbox" name="permsNew[<?= X_PERMS_RAWVIEW; ?>][]" value="<?= $val ?>" <?= ((($perms[X_PERMS_RAWVIEW] & $val) == $val) ? 'checked="checked"' : ''); ?> <?= $disabled ?> /></td>
</tr>
