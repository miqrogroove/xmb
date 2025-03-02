<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<!-- <?= $versionlong ?>  -->
<!-- Build: <?= $versionbuild ?> -->
<!-- <?= $versioncompany ?> -->
<head>
<?= $baseelement ?><?= $canonical_link ?>
<meta http-equiv="Content-Type" content="text/html; charset=<?= $lang['charset'] ?>" />
<meta name="viewport" content="width=500, initial-scale=1" />
<title><?= $threadSubject ?><?= $SETTINGS['bbname'] ?> - <?= $versionlong ?></title>
<style type="text/css">
.mediumtxt {
font-size: 14px;
font-family: arial, verdana;
}

h2, h3 {
margin-bottom: 0px;
margin-top: 0px;
}

.s14px {
font-size: 14px;
font-family: arial, verdana;
font-weight: bold;
}

.s13px {
font-size: 14px;
font-family: arial, verdana;
}

code {
white-space: pre-wrap;
}
</style>
</head>
<body>
<?= $THEME['logo'] ?>
<br />
<br />
<h2><a href="<?= $threadlink ?>" rev="alternate"><?= $thread['subject'] ?></a></h2>
<?= $multipage ?>
<?= $posts ?>
<?= $multipage ?>
</body>
</html>
