<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="<?= $lang['iso639'] ?>" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?= $lang['charset'] ?>" />
<meta name="viewport" content="width=500, initial-scale=1" />
<style type="text/css">
p {
font-size: 14px;
font-family: arial, verdana;
}
.16px {
font-size: 16px;
font-family: arial, verdana;
font-weight: bold;
}
.14px {
font-size: 14px;
font-family: arial, verdana;
font-weight: bold;
}
.13px {
font-size: 14px;
font-family: arial, verdana;
}
code {
white-space: pre;
}
</style>
<title><?= $SETTINGS['bbname'] ?> - <?= $lang['textpowered'] ?></title>
</head>
<body>
<?= $THEME['logo'] ?><br /><br />
<span class="16px"><?= $lang['textsubject'] ?> <?= $u2usubject ?><br />
<?= $lang['textfrom'] ?> <?= $u2ufrom ?><br />
<?= $lang['textto'] ?> <?= $u2uto ?><br />
<?= $lang['textu2ufolder'] ?> <?= $u2ufolder ?><br />
<?= $lang['textsent'] ?> <?= $u2udateline ?></span>
<br />
<p><?= $u2umessage ?></p>
</body>
</html>
