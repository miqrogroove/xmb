<form action="<?= $formURL ?>" method="post">
<input type="hidden" name="token" value="<?= $token ?>" />
<tr bgcolor="<?= $THEME['altbg1'] ?>" class="ctrtablerow"><td><input type="text" name="export_tid" size="4"/>&nbsp;<?= $lang['export_tid_expl'] ?></td></tr>
<tr bgcolor="<?= $THEME['altbg2'] ?>" class="ctrtablerow"><td><input class="submit" type="submit" name="orphsubmit" value="<?= $lang['textsubmitchanges'] ?>" /></td></tr>
</form>
