<?php
$answer6 = str_replace(
    [
        '$u2uAnchor',
        '$memcpURL'
    ],
    [
        "href='{$full_url}u2u.php' onclick=\"Popup(this.href, 'Window', 700, 450); return false;\"",
        $full_url . 'memcp.php'
    ],
    $lang['textfaqans6'],
);
?>
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['textuserman'] ?></strong></font></td>
</tr>
<tr>
<td width="100%" class="tablerow" bgcolor="<?= $THEME['altbg1'] ?>">
<ul>
<li><a href="#1"><?= $lang['textfaq1'] ?></a></li>
<li><a href="#2"><?= $lang['textfaq2'] ?></a></li>
<li><a href="#3"><?= $lang['textfaq3'] ?></a></li>
<li><a href="#4"><?= $lang['textfaq4'] ?></a></li>
<li><a href="#5"><?= $lang['textfaq5'] ?></a></li>
<li><a href="#6"><?= $lang['textfaq6'] ?></a></li>
</ul>
</td>
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
<tr>
<td class="category"><font color="<?= $THEME['cattext'] ?>"><strong><a name="1"></a><?= $lang['textfaq1'] ?></strong></font></td>
</tr>
<tr>
<td width="100%" class="tablerow" bgcolor="<?= $THEME['altbg1'] ?>"><?= str_replace('$url', $full_url . 'member.php?action=reg', $lang['textfaqans1']) ?></td>
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
<tr>
<td class="category"><font color="<?= $THEME['cattext'] ?>"><strong><a name="2"></a><?= $lang['textfaq2'] ?></strong></font></td>
</tr>
<tr>
<td width="100%" class="tablerow" bgcolor="<?= $THEME['altbg1'] ?>"><?= str_replace('$url', $full_url . 'misc.php?action=logout', $lang['textfaqans2']) ?></td>
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
<tr>
<td class="category"><font color="<?= $THEME['cattext'] ?>"><strong><a name="3"></a><?= $lang['textfaq3'] ?></strong></font></td></tr>
<tr>
<td width="100%" class="tablerow" bgcolor="<?= $THEME['altbg1'] ?>"><?= str_replace('$url', $full_url . 'memcp.php', $lang['textfaqans3']) ?></td>
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
<tr>
<td class="category"><font color="<?= $THEME['cattext'] ?>"><strong><a name="4"></a><?= $lang['textfaq4'] ?></strong></font></td>
</tr>
<tr>
<td width="100%" class="tablerow" bgcolor="<?= $THEME['altbg1'] ?>"><?= str_replace('$url', $full_url . 'memcp.php', $lang['textfaqans4']) ?></td>
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
<tr>
<td class="category"><font color="<?= $THEME['cattext'] ?>"><strong><a name="5"></a><?= $lang['textfaq5'] ?></strong></font></td>
</tr>
<tr>
<td width="100%" class="tablerow" bgcolor="<?= $THEME['altbg1'] ?>"><?= str_replace('$url', $full_url . 'misc.php?action=lostpw', $lang['textfaqans5']) ?></td>
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
<tr>
<td class="category"><font color="<?= $THEME['cattext'] ?>"><strong><a name="6"></a><?= $lang['textfaq6'] ?></strong></font></td>
</tr>
<tr>
<td width="100%" class="tablerow" bgcolor="<?= $THEME['altbg1'] ?>"><?= $answer6 ?></td>
</tr>
</table>
</td>
</tr>
</table>
