<?php

declare(strict_types=1);

namespace XMB;

?>
</table></td>
</tr>

<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textuserlist'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><textarea rows="4" cols="30" name="userlistnew">
<?php // Linefeed required here - Do not edit!
echo $forum['userlist'];
?></textarea></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['forumpw'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="passwordnew" value="<?= attrOut($forum['password'], 'javascript') ?>" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textdeleteques'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="checkbox" name="delete" value="<?= $forum['fid'] ?>" /></td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="ctrtablerow" colspan="2"><input type="submit" name="forumsubmit" value="<?= $lang['textsubmitchanges'] ?>" class="submit" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
</td>
</tr>
