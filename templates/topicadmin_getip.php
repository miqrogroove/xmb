<form method="post" action="<?= $full_url ?>admin/ipban.php">
 <input type="hidden" name="token" value="<?= $token ?>" />
 <table cellspacing="0" cellpadding="0" border="0" width="60%" align="center">
  <tr>
   <td bgcolor="<?= $THEME['bordercolor'] ?>">
    <table border="0" cellspacing="<?= $THEME['borderwidth']?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
     <tr><td class="header" colspan="3"><?= $lang['textgetip'] ?></td></tr>
     <tr bgcolor="<?= $THEME['altbg2'] ?>">
      <td class="tablerow">
       <?= $lang['textyesip'] ?> <strong><?= $address ?></strong> - <?= $name ?>
       <?php if ($banningEnabled) { ?>
        <?= $existingBan ?>
        <?= $ipBanInputs ?>
        </td></tr><tr bgcolor="<?= $THEME['altbg1'] ?>"><td class="ctrtablerow">
        <input type="submit" name="ipbansubmit" value="<?= $buttontext ?>" />
       <?php } ?>
      </td>
     </tr>
    </table>
   </td>
  </tr>
 </table>
</form>
