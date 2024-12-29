</table></td></tr></table><br />

<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth']; ?>" align="center">
 <tr>
  <td bgcolor="<?= $THEME['bordercolor']; ?>">
   <table border="0" cellspacing="<?= $THEME['borderwidth']; ?>" cellpadding="<?= $THEME['tablespace']; ?>" width="100%">

<tr bgcolor="<?= $THEME['altbg2'] ?>" class="tablerow">
<td align="left" colspan="2">
<strong><?= $found ?></strong> <?= $found == 1 ? $lang['beenfound_singular'] : $lang['beenfound'] ?>
<br />
</td>
</tr>
<?php
foreach($list as $num => $val) {
    ?>
    <tr class="tablerow" width="5%">
    <td align="left" bgcolor="<?= $THEME['altbg2'] ?>">
    <strong><?= ($num + 1) ?>.</strong>
    </td>
    <td align="left" width="95%" bgcolor="<?= $THEME['altbg1'] ?>">
    <?= $val; ?>
    </td>
    </tr>
    <?php
}
?>
   </table>
  </td>
 </tr>
</table>
