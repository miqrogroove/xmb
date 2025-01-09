<form action="<?= $formURL ?>" method="post">
<input type="hidden" name="token" value="<?= $token ?>" />
<tr bgcolor="<?= $THEME['altbg1'] ?>" class="ctrtablerow"><td><?= $lang['export_fid_expl'] ?> &nbsp; <?= $select ?></td></tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="ctrtablerow"><td><input class="submit" type="submit" name="orphsubmit" value="<?= $lang['textsubmitchanges'] ?>" /></td></tr>
</form>
