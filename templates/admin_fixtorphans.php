<div class="admin-fixtorphans">
 <form action="<?= $formURL ?>" method="post">
  <input type="hidden" name="token" value="<?= $token ?>" />
  <p><label><?= $lang['export_fid_expl'] ?> &nbsp; <?= $select ?></label></p>
  <p><input class="submit" type="submit" name="orphsubmit" value="<?= $lang['textsubmitchanges'] ?>" /></p>
 </form>
</div>
