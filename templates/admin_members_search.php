<tr bgcolor="<?php echo $altbg2?>">
<td>
<form method="post" action="<?= $full_url ?>admin/members.php?members=search">
<table cellspacing="0" cellpadding="0" border="0" width="90%" align="center">
<tr>
<td bgcolor="<?php echo $bordercolor?>">
<table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
<tr>
<td class="category" colspan="2"><font color="<?php echo $cattext?>"><strong><?php echo $lang['textmembers']?></strong></font></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?php echo $altbg1?>" width="22%"><?php echo $lang['textsrchusr']?></td>
<td bgcolor="<?php echo $altbg2?>"><input type="text" name="srchmem" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?php echo $altbg1?>" width="22%"><?php echo $lang['textsrchemail']?></td>
<td bgcolor="<?php echo $altbg2?>"><input type="text" name="srchemail" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?php echo $altbg1?>" width="22%"><?php echo $lang['textsrchip']?></td>
<td bgcolor="<?php echo $altbg2?>"><input type="text" name="srchip" /></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?php echo $altbg1?>" width="22%"><?php echo $lang['textwithstatus']?></td>
<td bgcolor="<?php echo $altbg2?>">
<select name="srchstatus">
<option value=""><?php echo $lang['anystatus']?></option>
<option value="Super Administrator"><?php echo $lang['superadmin']?></option>
<option value="Administrator"><?php echo $lang['textadmin']?></option>
<option value="Super Moderator"><?php echo $lang['textsupermod']?></option>
<option value="Moderator"><?php echo $lang['textmod']?></option>
<option value="Member"><?php echo $lang['textmem']?></option>
<option value="Banned"><?php echo $lang['textbanned']?></option>
<option value="Pending"><?php echo $lang['textpendinglogin']?></option>
</select>
</td>
</tr>
<tr>
<td bgcolor="<?php echo $altbg2?>" class="ctrtablerow" colspan="2"><input type="submit" class="submit" value="<?php echo $lang['textgo']?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
</td>
</tr>
