<form method="post" action="" onsubmit="return disableButton(this);">
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category" colspan="2"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['coppa_title'] ?></strong></font></td>
</tr>
<tr>
 <td bgcolor="<?= $THEME['altbg1'] ?>" class="ctrtablerow">
  <p><?= $lang['coppa_directions'] ?>
   <select name="age" id="age">
    <?= $optionlist ?>
   </select>
  </p>
  <p><?= $lang['coppa_explained'] ?></p>
  <p>
   <input type="submit" class="submit" name="regsubmit" value="<?= $lang['continue_button'] ?>" />
   <input type="hidden" name="step" value="<?= $stepout ?>" />
   <input type="hidden" name="token" value="<?= $token ?>" />
  </p>
 </td>
</tr>
</table>
</td>
</tr>
</table>
</form>
