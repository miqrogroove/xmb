<table cellpadding="0" cellspacing="0" border="0" bgcolor="<?= $THEME['bordercolor'] ?>" width="<?= $THEME['tablewidth'] ?>" align="center"><tr><td>
<table cellpadding="4" cellspacing="<?= $THEME['borderwidth']?>" border="0" width="100%" class="tablelinks">
<tr align="center" class="tablerow">
<?php $color = ($action == '') ? $THEME['altbg1'] : $THEME['altbg2']; ?>
<td bgcolor='<?= $color ?>' width='15%' class='ctrtablerow'><a href='<?= $full_url ?>memcp.php'><?= $lang['textmyhome'] ?></a></td>

<?php $color = ($action == 'profile') ? $THEME['altbg1'] : $THEME['altbg2']; ?>
<td bgcolor='<?= $color ?>' width='15%' class='ctrtablerow'><a href='<?= $full_url ?>memcp.php?action=profile'><?= $lang['texteditpro'] ?></a></td>

<?php $color = ($action == 'subscriptions') ? $THEME['altbg1'] : $THEME['altbg2']; ?>
<td bgcolor='<?= $color ?>' width='15%' class='ctrtablerow'><a href='<?= $full_url ?>memcp.php?action=subscriptions'><?= $lang['textsubscriptions'] ?></a></td>

<?php $color = ($action == 'favorites') ? $THEME['altbg1'] : $THEME['altbg2']; ?>
<td bgcolor='<?= $color ?>' width='15%' class='ctrtablerow'><a href='<?= $full_url ?>memcp.php?action=favorites'><?= $lang['textfavorites'] ?></a></td>

<?php $color = ($action == 'devices') ? $THEME['altbg1'] : $THEME['altbg2']; ?>
<td bgcolor='<?= $color ?>' width='15%' class='ctrtablerow'><a href='<?= $full_url ?>memcp.php?action=devices'><?= $lang['devices'] ?></a></td>

<td bgcolor='<?= $THEME['altbg2'] ?>' width='13%' class='ctrtablerow'><a href='<?= $full_url ?>u2u.php' onclick="Popup(this.href, 'Window', 700, 450); return false;"><?= $lang['textu2umessenger'] ?></a></td>
<td bgcolor='<?= $THEME['altbg2'] ?>' width='12%' class='ctrtablerow'><a href='<?= $full_url ?>buddy.php' onclick="Popup(this.href, 'Window', 450, 400); return false;"><?= $lang['textbuddylist'] ?></a></td>
</tr>
</table>
</td>
</tr>
</table>
<br />
