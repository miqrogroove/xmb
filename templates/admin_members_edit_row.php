<tr bgcolor="<?= THEME['altbg2'] ?>" class="tablerow">
<td align="center"><input type="checkbox" name="delete<?= $member['uid'] ?>" onclick="addUserDel(<?= $member['uid'] ?>, '<?= $member['username'] ?>', this)" value="<?= $member['uid'] ?>"<?= $disabledelete; ?> /></td>
<td><a href="<?= $full_url ?>member.php?action=viewpro&amp;member=<?= $userLink ?>"><?= $member['username'] ?></a>
<?php if (X_SADMIN) { ?>
<br /><a href="<?= $full_url ?>editprofile.php?user=<?= $userLink ?>"><strong><?= $lang['admin_edituseraccount'] ?></strong></a>
<?php } ?>
<br /><a href="<?= $full_url ?>cp.php?action=deleteposts&amp;member=<?= $userLink ?>"><strong><?= $lang['cp_deleteposts'] ?></strong></a><?= $pending ?>
</td>
<td><input type="text" size="12" name="pw<?= $member['uid'] ?>"></td>
<td><input type="text" size="3" name="postnum<?= $member['uid'] ?>" value="<?= $member['postnum'] ?>"></td>
<td><select name="status<?= $member['uid'] ?>">
<option value="Super Administrator" <?= $sadminselect ?>><?= $lang['superadmin'] ?></option>
<option value="Administrator" <?= $adminselect ?>><?= $lang['textadmin'] ?></option>
<option value="Super Moderator" <?= $smodselect ?>><?= $lang['textsupermod'] ?></option>
<option value="Moderator" <?= $modselect ?>><?= $lang['textmod'] ?></option>
<option value="Member" <?= $memselect ?>><?= $lang['textmem'] ?></option>
<option value="Banned" <?= $banselect ?>><?= $lang['textbanned'] ?></option>
</select></td>
<td><input type="text" size="16" name="cusstatus<?= $member['uid'] ?>" value="<?= statusAttr ?>" /></td>
<td><select name="banstatus<?= $member['uid'] ?>">
<option value="" <?= $noban ?>><?= $lang['noban'] ?></option>
<option value="u2u" <?= $u2uban ?>><?= $lang['banu2u'] ?></option>
<option value="posts" <?= $postban ?>><?= $lang['banpost'] ?></option>
<option value="both" <?= $bothban ?>><?= $lang['banboth'] ?></option>
</select></td>
</tr>
