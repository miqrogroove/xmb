<div class="admin-ays">
 <p><?= $prompt ?></p>
 <form action="<?= $formURL ?>" method="post">
  <input type="hidden" name="token" value="<?= $token ?>" />
  <input type="submit" name="yessubmit" value="<?= $lang['textyes'] ?>" /> -
  <input type="submit" name="nosubmit" value="<?= $lang['textno'] ?>" />
 </form>
</div>
