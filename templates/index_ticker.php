<script type="text/javascript" src="<?= $full_url ?>js/ticker.js"></script>
<script type="text/javascript">
<!--//--><![CDATA[//><!--
var stopticker = "<?= $lang['stopticker'] ?>";
var startticker = "<?= $lang['startticker'] ?>";
var delay = <?= $SETTINGS['tickerdelay'] ?>;
var node = '';
var current = 0;
var running = false;
var contents = new Array();
<?= $contents ?>
setTickerEvent();
//--><!]]>
</script>
<div class="xmb-block-wrap index-ticker-wrap">
 <div class="xmb-block-simple index-ticker">
  <div class="row">
   <div class="category-head"><?= $lang['tickername'] ?> [<a id="tickertoggle" href="javascript:tickertoggle();">&nbsp;</a>]</div>
  </div>
  <div class="row">
   <div class="field">
    <div align="center" id="tickerdiv"></div>
   </div>
  </div>
 </div>
</div>
