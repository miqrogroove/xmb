<?php

declare(strict_types=1);

namespace XMB;

?>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td class="smalltxt"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <input type="checkbox" name="delete<?= $forum['fid'] ?>" value="<?= $forum['fid'] ?>" />
&nbsp;<input type="text" name="name<?= $forum['fid'] ?>" value="<?= $forum['name'] ?>" />
&nbsp; <?= $lang['textorder'] ?> <input type="text" name="displayorder<?= $forum['fid'] ?>" size="2" value="<?= $forum['displayorder'] ?>" />
&nbsp; <select name="status<?= $forum['fid'] ?>">
<option value="on" <?= $on ?>><?= $lang['texton'] ?></option><option value="off" <?= $off ?>><?= $lang['textoff'] ?></option></select>
&nbsp; <select name="moveto<?= $forum['fid'] ?>"><option value="">-<?= $lang['textnone'] ?>-</option>
<?php
if (! isset($subs[$forum['fid']])) { // Ungrouped forum options.
    foreach ($forums[0] as $moveforum) {
        echo "<option value='{$moveforum['fid']}'> &nbsp; &raquo; " . adminStripText($moveforum['name']) . "</option>";
    }
}
foreach ($groups as $moveforum) { // Groups and grouped forum options.
    if ($moveforum['fid'] == $forum['fup']) {
        $curgroup = $selHTML;
    } else {
        $curgroup = '';
    }
    echo "<option value='{$moveforum['fid']}' $curgroup>" . adminStripText($moveforum['name']) . "</option>";
    if (! isset($subs[$forum['fid']]) && isset($forums[$moveforum['fid']])) {
        foreach ($forums[$moveforum['fid']] as $moveforum) {
            if ($moveforum['fid'] != $forum['fid']) {
                echo "<option value='{$moveforum['fid']}'> &nbsp; &raquo; " . adminStripText($moveforum['name']) . "</option>";
            }
        }
    }
}
?>
</select>
<a href="<?= $full_url ?>admin/forums.php?fdetails=<?= $forum['fid'] ?>"><?= $lang['textmoreopts'] ?></a></td>
</tr>
