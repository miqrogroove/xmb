<?php

declare(strict_types=1);

namespace XMB;

?>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td class="smalltxt"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<input type="checkbox" name="delete<?= $subforum['fid'] ?>" value="<?= $subforum['fid'] ?>" />
&nbsp;<input type="text" name="name<?= $subforum['fid'] ?>" value="<?= $subforum['name'] ?>" />
&nbsp; <?= $lang['textorder'] ?> <input type="text" name="displayorder<?= $subforum['fid'] ?>" size="2" value="<?= $subforum['displayorder'] ?>" />
&nbsp; <select name="status<?= $subforum['fid'] ?>">
<option value="on" <?= $on ?>><?= $lang['texton'] ?></option><option value="off" <?= $off ?>><?= $lang['textoff'] ?></option></select>
&nbsp; <select name="moveto<?= $subforum['fid'] ?>"><option value="" selected="selected">-<?= $lang['textnone'] ?>-</option>
<?php
foreach ($forums[0] as $moveforum) { // Ungrouped forum options.
    echo "<option value='{$moveforum['fid']}' $curgroup> &nbsp; &raquo; " . adminStripText($moveforum['name']) . "</option>";
}
foreach ($groups as $moveforum) { // Groups and grouped forum options.
    echo "<option value='{$moveforum['fid']}'>" . adminStripText($moveforum['name']) . "</option>";
    if (isset($forums[$moveforum['fid']])) {
        foreach ($forums[$moveforum['fid']] as $moveforum) {
            if ($moveforum['fid'] == $subforum['fup']) {
                $curgroup = $selHTML;
            } else {
                $curgroup = '';
            }
            echo "<option value='{$moveforum['fid']}' $curgroup> &nbsp; &raquo; " . adminStripText($moveforum['name']) . "</option>";
        }
    }
}
?>
</select>
<a href="<?= $full_url ?>admin/forums.php?fdetails=<?= $subforum['fid'] ?>"><?= $lang['textmoreopts'] ?></a></td>
</tr>
