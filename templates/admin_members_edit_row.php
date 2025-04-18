<?php

namespace XMB;

?>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td align="center"><input type="checkbox" name="delete<?= $uid ?>" onclick="addUserDel(<?= $uid ?>, '<?= $username ?>', this)" value="<?= $uid ?>"<?= $disabledelete; ?> /></td>
<td><a href="<?= $full_url ?>member.php?action=viewpro&amp;member=<?= $userLink ?>"><?= $username ?></a>
<?php if (X_SADMIN) { ?>
<br /><a href="<?= $full_url ?>editprofile.php?user=<?= $userLink ?>"><strong><?= $lang['admin_edituseraccount'] ?></strong></a>
<?php } ?>
<br /><a href="<?= $full_url ?>admin/members-prune.php?member=<?= $userLink ?>"><strong><?= $lang['cp_deleteposts'] ?></strong></a><?= $pending ?>
</td>
<td><input type="text" size="12" name="pw<?= $uid ?>"></td>
<td><?= $postnum ?></td>
<td>
<?= $userStatus ?>
</td>
<td><input type="text" size="16" name="cusstatus<?= $uid ?>" value="<?= $statusAttr ?>" /></td>
<td><select name="banstatus<?= $uid ?>">
 <option value="" <?= $noban ?>><?= $lang['noban'] ?></option>
 <option value="u2u" <?= $u2uban ?>><?= $lang['banu2u'] ?></option>
 <option value="posts" <?= $postban ?>><?= $lang['banpost'] ?></option>
 <option value="both" <?= $bothban ?>><?= $lang['banboth'] ?></option>
</select></td>
</tr>
