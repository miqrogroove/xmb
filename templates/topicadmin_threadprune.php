<form method="post" action="topicadmin.php?action=threadprune">
<input type="hidden" name="token" value="" />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category" colspan="2"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['textprunethread'] ?></strong></font></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" width="22%"><?= $lang['loggedinuser'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $self['username'] ?> <?= $lang['textminilogout'] ?></td>
</tr>
<tr class="header">
<td><?= $lang['deletebutton'] ?>?</td>
<td><?= $lang['textpost'] ?></td>
</tr>
<?= $posts ?>
<tr>
<td colspan="2" class="ctrtablerow" bgcolor="<?= $THEME['altbg2'] ?>"><input type="hidden" name="fid" value="<?= $fid ?>" /><input type="hidden" name="tid" value="<?= $tid ?>" /><input type="submit" name="threadprunesubmit" value="<?= $lang['textprunethread'] ?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>