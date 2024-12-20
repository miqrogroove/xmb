<form method="post" action="member.php?action=reg" onsubmit="return disableButton(this);">
<input type="hidden" name="token" value="<?= $token ?>" />
<input type="hidden" name="step" value="<?= $stepout ?>" />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['registerrulestitle'] ?></strong></font></td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg1'] ?>" class="tablerow"><?= $lang['rulesoninfo'] ?></td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow"><?= $SETTINGS['bbrulestxt'] ?></td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="ctrtablerow"><input type="submit" class="submit" name="rulesubmit" value="<?= $lang['textagree'] ?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
