<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="<?= $lang['iso639'] ?>" xmlns="http://www.w3.org/1999/xhtml">
<!-- <?= $versionlong ?>  -->
<!-- Build: <?= $versionbuild ?> -->
<!-- <?= $versioncompany ?> -->
<head>
<?= $canonical_link ?>
<meta http-equiv="Content-Type" content="text/html; charset=<?= $lang['charset'] ?>" />
<meta name="viewport" content="width=500, initial-scale=1" />
<title><?= $threadSubject ?><?= $SETTINGS['bbname'] ?> - <?= $versionlong ?></title>
<?= $css ?>
<script type="text/javascript" src="<?= $full_url ?>js/header.js?v=2"></script>
</head>
<body text="<?= $THEME['text'] ?>">
<?= $bbcodescript ?>
<a name="top"></a>

<div class="xmb-block-wrap page-header-wrap">
 <div class="xmb-block-simple page-header">
  <div class="row">
   <div class="header-top">
    <div class="header-top-grid">
     <div class="row">
      <div class="logo"><?= $THEME['logo'] ?></div>
      <div class="user-alerts"><?= $lastvisittext ?><br /><?= $newu2umsg ?></div>
     </div>
     <div class="row">
      <div class="login-status">
       <div><?= $notify ?></div>
      </div>
     </div>
    </div>
   </div>
  </div>
  <div class="row">
   <div class="navtd">
    <div class="links-grid">
     <div class="row">
      <div class="pluglinks"><?= $searchlink ?> <?= $links ?> <?= $pluglink ?></div>
      <div class="sitelink"><a href="<?= $SETTINGS['siteurl'] ?>" title="<?= $SETTINGS['sitename'] ?>"><?= $lang['backto'] ?> <img src="<?= $full_url ?><?= $THEME['imgdir'] ?>/top_home.gif" border="0" alt="<?= $SETTINGS['sitename'] ?>" /></a></div>
     </div>
    </div>
   </div>
  </div>
 </div>
</div>

<div class="xmb-grid breadcrumbs">
 <div class="row">
  <div class="naked-cell nav">
   <div> <a href="<?= $full_url ?>"><?= $SETTINGS['bbname'] ?></a> <?= $navigation ?></div>
  </div>
  <div class="naked-cell"><?= $quickjump ?></div>
  <div class="naked-cell"><a href="#bottom" title="<?= $lang['gotobottom'] ?>"><img src="<?= $full_url ?><?= $THEME['imgdir'] ?>/arrow_dw.gif" border="0" alt="<?= $lang['gotobottom'] ?>" /></a></div>
 </div>
</div>
<br />
