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
<table border="0" cellpadding="0" cellspacing="0" width="<?= $THEME['tablewidth'] ?>" align="center">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr>
<td class="tablerow" colspan="2" width="100%">
<table cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" border="0" width="100%" align="center">
<tr>
<td class="category"><strong><font color="<?= $THEME['cattext'] ?>"><?= $lang['tickername'] ?> [<a id="tickertoggle" href="javascript:tickertoggle();">&nbsp;</a>]</font></strong></td>
</tr>
<tr>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="mediumtxt" style="height: 30px;"><div align="center" id="tickerdiv"></div></td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
<br />
