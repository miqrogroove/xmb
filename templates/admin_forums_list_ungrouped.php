<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td class="smalltxt"><input type="checkbox" name="delete<?= $forum['fid'] ?>" value="<?= $forum['fid'] ?>" />
&nbsp;<input type="text" name="name<?= $forum['fid'] ?>" value="<?= stripslashes($forum['name']) ?>" />
&nbsp; <?= $lang['textorder'] ?> <input type="text" name="displayorder<?= $forum['fid'] ?>" size="2" value="<?= $forum['displayorder'] ?>" />
&nbsp; <select name="status<?= $forum['fid'] ?>">
<option value="on" <?= $on ?>><?= $lang['texton'] ?></option><option value="off" <?= $off ?>><?= $lang['textoff'] ?></option></select>
&nbsp; <select name="moveto<?= $forum['fid'] ?>"><option value="" selected="selected">-<?= $lang['textnone'] ?>-</option>
<?php
if (!isset($subs[$forum['fid']])) { //Ungrouped forum options.
    foreach($forums[0] as $moveforum) {
        if ($moveforum['fid'] != $forum['fid']) {
            echo "<option value=\"$moveforum[fid]\"> &nbsp; &raquo; ".stripslashes($moveforum['name'])."</option>";
        }
    }
}
foreach($groups as $moveforum) { //Groups and grouped forum options.
    echo "<option value=\"$moveforum[fid]\">".stripslashes($moveforum['name'])."</option>";
    if (isset($forums[$moveforum['fid']]) && !isset($subs[$forum['fid']])) {
        foreach($forums[$moveforum['fid']] as $moveforum) {
            echo "<option value=\"$moveforum[fid]\"> &nbsp; &raquo; ".stripslashes($moveforum['name'])."</option>";
        }
    }
}
?>
</select>
<a href="<?= $full_url ?>admin/forums.php?fdetails=<?= $forum['fid'] ?>"><?= $lang['textmoreopts'] ?></a></td>
</tr>
