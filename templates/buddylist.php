<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="<?= $lang['iso639'] ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?= $lang['charset'] ?>" />
<meta name="viewport" content="width=500, initial-scale=1" />
<?= $css ?>
<title><?= $threadSubject ?><?= $SETTINGS['bbname'] ?> - <?= $lang['textpowered'] ?></title>
<script type="text/javascript" src="<?= $full_url ?>js/popup.js"></script>
</head>
<body text="<?= $THEME['text'] ?>">

<div class="xmb-block-wrap buddylist-wrap">
 <div class="xmb-block-simple buddylist">
  <div class="row">
   <div class="category-head"><?= $lang['textbuddylist'] ?></div>
  </div>
  <div class="row">
   <div class="field book-block">

<table width="98%">
<tr>
<td class="tablerow" bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><strong><?= $lang['textonline'] ?></strong></td>
</tr>
<?= $buddys['online'] ?>
<tr>
<td class="tablerow" bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><strong><?= $lang['textoffline'] ?></strong></td>
</tr>
<?= $buddys['offline'] ?>
</table>

   </div>
  </div>
  <div class="row">
   <div class="field"><strong><a href="<?= $full_url ?>buddy.php"><?= $lang['refreshbuddylist'] ?></a></strong></div>
  </div>
 </div>
</div>

<div class="xmb-block-wrap buddy-link-wrap">
 <div class="xmb-block-simple buddy-link">
  <div class="row">
   <div class="field"><a href="<?= $full_url ?>buddy.php?action=edit"><?= $lang['editbuddylist'] ?></a></div>
  </div>
 </div>
</div>

</body>
</html>
