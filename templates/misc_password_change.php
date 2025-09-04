<form method="post" action="<?= $full_url ?>misc.php?action=pwchange">
<input type="hidden" name="token" value="<?= $token ?>" />
<input type="hidden" name="uid" value="<?= $uid ?>" />
<input type="hidden" name="comment" value="<?= $comment ?>" />
<?php if ($hide) { ?><input type="hidden" name="hide" value="1" /><?php } ?>
<?php if ($trust) { ?><input type="hidden" name="trust" value="yes" /><?php } ?>
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category" colspan="2"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['pwchange'] ?></strong></font></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" colspan="2"><?= $lang['force_new_pw_detail'] ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['textusername'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="username" size="25" readonly="readonly" value="<?= $username ?>" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textnewpassword'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="password" name="password" size="25" required="required" maxlength="<?= $pwmax ?>" minlength="<?= $pwmin ?>" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textretypepw'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="password" name="password2" size="25" required="required" maxlength="<?= $pwmax ?>" minlength="<?= $pwmin ?>" /></td>
</tr>
<tr class="ctrtablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input type="submit" class="submit" name="loginsubmit" value="<?= $lang['textlogin'] ?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
