<tr bgcolor="<?= $THEME['altbg2'] ?>">
<td align="center">
<form method="post" action="<?= $full_url ?>admin/prune.php">
<input type="hidden" name="token" value="<?= $token ?>" />
<table cellspacing="0" cellpadding="0" border="0" width="550">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%" style="vertical-align: top;">
<tr>
<td class="category" colspan="2">
<strong>
<span style="color: <?= $THEME['cattext'] ?>">
<?= $lang['textprune'] ?>
</span>
</strong>
</td>
</tr>
<tr>
<td class="tablerow" style="background-color: <?= $THEME['altbg1'] ?>;">
<?= $lang['pruneby'] ?>
</td>
<td class="tablerow" style="background-color: <?= $THEME['altbg2'] ?>;">
<table>
<tr>
<td>
<input type="checkbox" name="pruneByDate[check]" value="1" checked="checked" />
</td>
<td>
<select name="pruneByDate[type]">
<option value="more"><?= $lang['prunemorethan'] ?></option>
<option value="is"><?= $lang['pruneexactly'] ?></option>
<option value="less"><?= $lang['prunelessthan'] ?></option>
</select>
<input type="text" name="pruneByDate[date]" value="100" /> <?= $lang['daysold'] ?>
</td>
</tr>
<tr>
<td>
<input type="checkbox" name="pruneByPosts[check]" value="1" />
</td>
<td>
<select name="pruneByPosts[type]">
<option value="more"><?= $lang['prunemorethan'] ?></option>
<option value="is"><?= $lang['pruneexactly'] ?></option>
<option value="less"><?= $lang['prunelessthan'] ?></option>
</select>
<input type="text" name="pruneByPosts[posts]" value="10" /> <?= $lang['memposts'] ?>
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td class="tablerow" style="background-color: <?= $THEME['altbg1'] ?>;">
<?= $lang['prunefrom'] ?>
</td>
<td class="tablerow" style="background-color: <?= $THEME['altbg2'] ?>;">
<table>
<tr>
<td>
<input type="radio" name="pruneFrom" value="all" />
</td>
<td>
<?= $lang['textallforumsandsubs'] ?>
</td>
</tr>
<tr>
<td>
<input type="radio" name="pruneFrom" value="list" />
</td>
<td>
<?= $forumselect ?>
</td>
</tr>
<tr>
<td>
<input type="radio" name="pruneFrom" value="fid" checked="checked" />
</td>
<td>
<?= $lang['prunefids'] ?> <input type="text" name="pruneFromFid" /> <span class="smalltxt">(<?= $lang['seperatebycomma'] ?>)</span>
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td class="tablerow" style="background-color: <?= $THEME['altbg1'] ?>;">
<?= $lang['pruneposttypes'] ?>
</td>
<td class="tablerow postOptions" style="background-color: <?= $THEME['altbg2'] ?>;">
 <label><input type="checkbox" name="pruneType[normal]" value="1" checked="checked" /> <?= $lang['prunenormal'] ?></label>
 <?= $lang['textor'] ?>
 <label><input type="checkbox" name="pruneType[closed]" value="1" checked="checked" /> <?= $lang['pruneclosed'] ?></label>
 <?= $lang['textor'] ?>
 <label><input type="checkbox" name="pruneType[topped]" value="1" /> <?= $lang['prunetopped'] ?></label>
</td>
</tr>
<tr>
<td class="ctrtablerow" style="background-color: <?= $THEME['altbg2'] ?>;" colspan="2"><input type="submit" name="pruneSubmit" value="<?= $lang['textprune'] ?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
</td>
</tr>
<?php
