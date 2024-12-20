<a name="admintools"></a>
<form action="topicadmin.php?tid=<?= $tid&amp;fid=$fid ?>" method="get">
<input type="hidden" name="token" value="" />
<span class="mediumtxt"><strong><?= $lang['textadminoptions'] ?></strong>
<br />
<select name="action" id="action" onchange="if(this.options[this.selectedIndex].value != '') { window.location=('topicadmin.php?tid=<?= $tid&amp;fid=$fid&amp;action='+this.options[this.selectedIndex].value) ?> }">
<option value="" selected="selected"></option>
<option value="delete"><?= $lang['textdeletethread'] ?></option>
<option value="close"><?= $closeopen ?></option>
<option value="copy"><?= $lang['copythread'] ?></option>
<option value="move"><?= $lang['textmovemethod1'] ?></option>
<option value="top"><?= $topuntop ?></option>
<option value="split"><?= $lang['textsplitthread'] ?></option>
<option value="merge"><?= $lang['textmergethread'] ?></option>
<option value="bump"><?= $lang['textbumpthread'] ?></option>
<option value="empty"><?= $lang['textemptythread'] ?></option>
<option value="threadprune"><?= $lang['textprunethread'] ?></option>
</select>
</span>
</form>
