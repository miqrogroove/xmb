<tr class="tablerow">
    <td class="category" style="color: <?= $THEME['cattext'] ?>; font-weight: bold; text-align: right;"><?= $lang[$statusKey] ?></td>
    <td class="altbg1 ctrtablerow"><input type="checkbox" name="permsNew[<?= $rawPoll ?>][]" value="<?= $val ?>" <?= ((($perms[$rawPoll] & $val) == $val) ? 'checked="checked"' : ''); ?> <?= $disabled ?> /></td>
    <td class="altbg1 ctrtablerow"><input type="checkbox" name="permsNew[<?= $rawThread ?>][]" value="<?= $val ?>" <?= ((($perms[$rawThread] & $val) == $val) ? 'checked="checked"' : ''); ?> <?= $disabled ?> /></td>
    <td class="altbg1 ctrtablerow"><input type="checkbox" name="permsNew[<?= $rawReply ?>][]" value="<?= $val ?>" <?= ((($perms[$rawReply] & $val) == $val) ? 'checked="checked"' : ''); ?> <?= $disabled ?> /></td>
    <td class="altbg1 ctrtablerow"><input type="checkbox" name="permsNew[<?= $rawView ?>][]" value="<?= $val ?>" <?= ((($perms[$rawView] & $val) == $val) ? 'checked="checked"' : ''); ?> <?= $disabled ?> /></td>
</tr>
