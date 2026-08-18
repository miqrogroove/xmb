<?php

declare(strict_types=1);

namespace XMB;

?>
   <div class="row result">
    <div class="field delete"><input type="checkbox" name="delete<?= $uid ?>" onclick="addUserDel(<?= $uid ?>, '<?= $username ?>', this)" value="<?= $uid ?>"<?= $disabledelete; ?> /></div>
    <div class="field username">
     <div>
      <a href="<?= $full_url ?>member.php?action=viewpro&amp;member=<?= $userLink ?>"><?= $username ?></a>
<?php if (X_SADMIN) { ?>
      <br /><a href="<?= $full_url ?>editprofile.php?user=<?= $userLink ?>"><strong><?= $lang['admin_edituseraccount'] ?></strong></a>
<?php } ?>
      <br /><a href="<?= $full_url ?>admin/members-prune.php?member=<?= $userLink ?>"><strong><?= $lang['cp_deleteposts'] ?></strong></a><?= $pending ?>
     </div>
    </div>
    <div class="field"><input type="text" size="12" name="pw<?= $uid ?>"></div>
    <div class="field"><?= $postnum ?></div>
    <div class="field"><?= $userStatus ?></div>
    <div class="field"><input type="text" size="16" name="cusstatus<?= $uid ?>" value="<?= $statusAttr ?>" /></div>
    <div class="field">
     <select name="banstatus<?= $uid ?>">
      <option value="" <?= $noban ?>><?= $lang['noban'] ?></option>
      <option value="u2u" <?= $u2uban ?>><?= $lang['banu2u'] ?></option>
      <option value="posts" <?= $postban ?>><?= $lang['banpost'] ?></option>
      <option value="both" <?= $bothban ?>><?= $lang['banboth'] ?></option>
     </select>
    </div>
   </div>
