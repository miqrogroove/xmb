<?php

declare(strict_types=1);

namespace XMB;

?>
<tr bgcolor="<?= $THEME['altbg1'] ?>" class="tablerow">
<td>&nbsp;</td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td class="smalltxt"><input type="text" name="newgname" value="<?= $lang['textnewgroup'] ?>" />
&nbsp; <?= $lang['textorder'] ?> <input type="text" name="newgorder" size="2" />
&nbsp; <select name="newgstatus">
<option value="on"><?= $lang['texton'] ?></option><option value="off"><?= $lang['textoff'] ?></option></select></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>" class="smalltxt"><input type="text" name="newfname" value="<?= $lang['textnewforum'] ?>" />
&nbsp; <?= $lang['textorder'] ?> <input type="text" name="newforder" size="2" />
&nbsp; <select name="newfstatus">
<option value="on"><?= $lang['texton'] ?></option><option value="off"><?= $lang['textoff'] ?></option></select>
&nbsp; <select name="newffup"><option value="" selected="selected">-<?= $lang['textnone'] ?>-</option>
<?php
foreach ($groups as $group) {
    echo '<option value="'.$group['fid'].'">'.fnameOut($group['name']).'</option>';
}
?>
</select>
</td>
</tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td class="smalltxt"><input type="text" name="newsubname" value="<?= $lang['textnewsubf'] ?>" />
&nbsp; <?= $lang['textorder'] ?> <input type="text" name="newsuborder" size="2" />
&nbsp; <select name="newsubstatus"><option value="on"><?= $lang['texton'] ?></option><option value="off"><?= $lang['textoff'] ?></option></select>
&nbsp; <select name="newsubfup">
<?php
foreach ($forumlist as $group) {
    echo '<option value="'.$group['fid'].'">'.fnameOut($group['name']).'</option>';
}
?>
</select>
</td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="ctrtablerow"><input type="submit" name="forumsubmit" value="<?= $lang['textsubmitchanges'] ?>" class="submit" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
</td>
</tr>
