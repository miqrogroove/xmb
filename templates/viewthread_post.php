<tr bgcolor="<?= $thisbg ?>">
<td rowspan="3" valign="top" class="tablerow" style="width: 18%;">
<font class="mediumtxt"><strong><?= $profilelink ?></strong></font>
<br />
<div class="smalltxt"><a name="pid<?= $post['pid'] ?>"></a>
<?= $showtitle ?>
<?= $stars ?>
<br />
<div align="center">
<?= $rank['avatar'] ?>
</div>
<hr />
<div align="center">
<?= $avatar ?>
</div>
<br />
<br />
<?= $lang['textposts'] ?> <?= $post['postnum'] ?>
<br />
<?= $lang['textregistered'] ?> <?= $tharegdate ?>
<?= $location ?>
<br />
<?= $onlinenow ?>
<br />
<br />
<?= $mood ?>
</div>
<br />
</td>
<td valign="top" class="tablerow" style="height: 30px; width: 82%;">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr>
<td class="smalltxt" valign="top"><?= $post['icon'] ?> <a href="viewthread.php?tid=<?= $post['tid'] ?>&amp;goto=search&amp;pid=<?= $post['pid'] ?>" title="<?= $linktitle ?>" rel="nofollow"><?= $poston ?></a></td>
<td class="smalltxt" align="right" valign="top"><?= $edit$repquote$reportlink ?></td>
</tr>
</table>
</td>
</tr>
<tr bgcolor="<?= $thisbg ?>">
<td class="tablerow" valign="top" style="height: 80px; width: 82%" >
<font class="subject">
<strong><?= $post['subject'] ?></strong>
</font>
<br />
<br />
<div class="mediumtxt"><?= $post['message'] ?></div>
</td>
</tr>
<tr bgcolor="<?= $thisbg ?>">
<td class="tablerow" valign="bottom" style="height: 20px; width: 82%;">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr>
<td class="smalltxt">
<?= $profile$email$site$search$u2u$aim$icq$msn$yahoo ?>
</td>
<td align="right"><?= $ip ?></td>
</tr>
</table>
</td>
</tr>
