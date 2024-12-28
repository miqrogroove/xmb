<tr bgcolor="<?= $THEME['altbg2'] ?>">
<td align="center">
<form method="post" action="admin/forums.php&amp;fdetails=<?= $fdetails ?>">
<input type="hidden" name="token" value="<?= $token ?>" />
<table cellspacing="0" cellpadding="0" border="0" width="100%" align="center">
<tr>
<td bgcolor="<?= $THEME['$bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
<tr>
<td class="category" colspan="2"><font color="<?= $THEME['cattext'] ?>"><strong><?= $lang['textforumopts'] ?></strong></font></td>
</tr>

<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textforumname'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="text" name="namenew" value="<?= $forum['name']; ?>" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textdesc'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><textarea rows="4" cols="30" name="descnew">
<?php // Linefeed required here - Do not edit!
echo $forum['description'];
?></textarea></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>" valign="top"><?= $lang['textallow'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>" class="smalltxt">
<input type="checkbox" name="allowsmiliesnew" value="yes" <?= $checked3 ?> /><?= $lang['textsmilies'] ?><br />
<input type="checkbox" name="allowbbcodenew" value="yes" <?= $checked4 ?> /><?= $lang['textbbcode'] ?><br />
<input type="checkbox" name="allowimgcodenew" value="yes" <?= $checked5 ?> /><?= $lang['textimgcode'] ?><br />
<input type="checkbox" name="attachstatusnew" value="on" <?= $checked6 ?> /><?= $lang['attachments'] ?><br />
</td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['texttheme'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $themelist ?></td>
</tr>

<tr class="tablerow">
<td style="background-color: <?= $THEME['altbg1'] ?>"><?= $lang['forumpermissions'] ?></td>
<td style="background-color: <?= $THEME['altbg2'] ?>"><table style="width: 100%; text-align: center;">
<tr>
    <td class="tablerow" style="width: 25ex;">&nbsp;</td>
    <td class="category" style="color: <?= $THEME['cattext'] ?>; font-weight: bold; text-align: center;"><?= $lang['polls'];   ?></td>
    <td class="category" style="color: <?= $THEME['cattext'] ?>; font-weight: bold; text-align: center;"><?= $lang['threads']; ?></td>
    <td class="category" style="color: <?= $THEME['cattext'] ?>; font-weight: bold; text-align: center;"><?= $lang['replies']; ?></td>
    <td class="category" style="color: <?= $THEME['cattext'] ?>; font-weight: bold; text-align: center;"><?= $lang['view'];    ?></td>
</tr>
