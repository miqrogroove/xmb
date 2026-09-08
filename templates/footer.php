<div class="xmb-block-wrap bottom-breadcrumbs-wrap">
 <div class="xmb-block-simple bottom-breadcrumbs">
  <div class="field">
   <div class="xmb-grid bottom-breadcrumbs-grid">
    <div class="row">
     <div class="naked-cell nav">
      <div>&nbsp;<a href="<?= $full_url ?>"><?= $SETTINGS['bbname'] ?></a><?= $navigation ?></div>
     </div>
     <div class="naked-cell"><?= $quickjump ?></div>
     <div class="naked-cell"><a href="#top" title="<?= $lang['gototop'] ?>"><img src="<?= $full_url ?><?= $THEME['imgdir'] ?>/arrow_up.gif" alt="<?= $lang['gototop'] ?>" /></a></div>
    </div>
   </div>
  </div>
 </div>
</div>

<div class="xmb-block-wrap page-footer-wrap">
 <div class="xmb-block-simple page-footer">
  <div class="row">
   <div class="field smalltxt">
    <?= $versionlong ?>
    <br />
    <a href="https://www.xmbforum2.com/" onclick="window.open(this.href); return false;"><strong><?= $lang['xmbforum'] ?></strong></a>&nbsp;&copy; <?= $copyright ?> <?= $lang['xmbgroup'] ?>
    <br />
    <?= $footerstuff['totaltime'] ?>
    <?= $footerstuff['querynum'] ?>
    <?= $footerstuff['phpsql'] ?>
    <?= $footerstuff['load'] ?>
    <?= $footerstuff['querydump'] ?>
   </div>
  </div>
 </div>
</div>
<a id="bottom" name="bottom"></a>
</body>
</html>
