<?xml version="1.0" encoding="<?= $lang['charset'] ?>"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!-- <?= $versionlong ?>  -->
<!-- Build: <?= $versionbuild ?> -->
<!-- <?= $versioncompany ?> -->
<head>
<?= $baseelement ?><?= $canonical_link ?>
<meta http-equiv="Content-Type" content="text/html; charset=<?= $lang['charset'] ?>" />
<meta name="viewport" content="width=500, initial-scale=1" />
<title><?= $SETTINGS['bbname'] ?> <?= $threadSubject ?> - <?= $versionlong ?></title>
<?= $css ?>
<script language="JavaScript" type="text/javascript" src="./js/header.js?v=2"></script>
</head>
<body text="<?= $THEME['text'] ?>">
<?= $bbcodescript ?>
<a name="top"></a>
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="6" width="100%">
<tr>
<td width="74%" <?= $THEME['topbgcode'] ?>>
<table border="0" width="100%" cellpadding="0" cellspacing="0">
<tr>
<td valign="top" rowspan="2"><?= $THEME['logo'] ?></td>
<td align="right" valign="top"><font class="smalltxt"><?= $lastvisittext ?><br /><?= $newu2umsg ?></font></td>
</tr>
<tr>
<td align="right" valign="bottom"><font class="smalltxt"><?= $notify ?></font></td>
</tr>
</table>
</td>
</tr>
<tr>
<td class="navtd">
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td class="navtd"><font class="navtd"><?= $searchlink ?> <?= $links ?> <?= $pluglink ?></font></td>
<td align="right"><a href="<?= $SETTINGS['siteurl'] ?>" title="<?= $SETTINGS['sitename'] ?>"><font class="navtd"><?= $lang['backto'] ?> <img src="<?= $THEME['imgdir'] ?>/top_home.gif" border="0" alt="<?= $SETTINGS['sitename'] ?>" /></font></a></td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
<table cellspacing="0" cellpadding="1" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td><table width="100%" cellspacing="0" cellpadding="<?= $THEME['tablespace'] ?>" align="center">
<tr>
<td class="nav"> <a href="./"><?= $SETTINGS['bbname'] ?></a> <?= $navigation ?></td>
<td align="right"><?= $quickjump ?></td>
<td align="right" width="1"><a href="#bottom" title="<?= $lang['gotobottom'] ?>"><img src="<?= $THEME['imgdir'] ?>/arrow_dw.gif" border="0" alt="<?= $lang['gotobottom'] ?>" /></a></td>
</tr>
</table>
</td>
</tr>
</table>
<br />
