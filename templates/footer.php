<br />
<table cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center" bgcolor="<?= $THEME['bordercolor'] ?>">
<tr>
<td>
<table width="100%" cellspacing="0" cellpadding="0">
<tr>
<td class="nav" style="padding-bottom: 1px" bgcolor="<?= $THEME['altbg2'] ?>">&nbsp;<a href="<?= $full_url ?>"><?= $SETTINGS['bbname'] ?></a><?= $navigation ?></td>
<td class="tablerow" align="right" bgcolor="<?= $THEME['altbg2'] ?>"><?= $quickjump ?>&nbsp;</td>
<td align="right" bgcolor="<?= $THEME['altbg2'] ?>" width="2%"><a href="#top" title="<?= $lang['gototop'] ?>"><img src="<?= $full_url ?><?= $THEME['imgdir'] ?>/arrow_up.gif" style="margin-right: 3px" border="0" alt="<?= $lang['gototop'] ?>" /></a></td>
</tr>
</table>
</td>
</tr>
</table>
<br />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr class="ctrtablerow">
<td class="smalltxt" bgcolor="<?= $THEME['altbg2'] ?>">
<?= $versionlong ?>
<br />
<a href="https://www.xmbforum2.com/" onclick="window.open(this.href); return false;"><strong><?= $lang['xmbforum'] ?></strong></a>&nbsp;&copy; <?= $copyright ?> <?= $lang['xmbgroup'] ?>
<br />
<?= $footerstuff['totaltime'] ?>
<?= $footerstuff['querynum'] ?>
<?= $footerstuff['phpsql'] ?>
<?= $footerstuff['load'] ?>
<?= $footerstuff['querydump'] ?>
</td>
</tr>
</table>
</td>
</tr>
</table>
<a id="bottom" name="bottom"></a>
</body>
</html>
