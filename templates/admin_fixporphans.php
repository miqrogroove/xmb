<div class="admin-fixporphans">
 <form action="<?= $formURL ?>" method="post">
  <input type="hidden" name="token" value="<?= $token ?>" />
  <p><label><input type="text" name="export_tid" size="4"/>&nbsp;<?= $lang['export_tid_expl'] ?></label></p>
  <p><input class="submit" type="submit" name="orphsubmit" value="<?= $lang['textsubmitchanges'] ?>" /></p>
 </form>
</div>
