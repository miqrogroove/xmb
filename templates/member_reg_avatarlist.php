<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textavatar'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><select name="newavatar" onchange="document.images.avatarpic.src = '<?= $full_url ?>' + this[this.selectedIndex].value;"><?= $avatars ?></select> &nbsp;
<img src="<?= $full_url ?>images/avatars/clear_avatar.gif" id="avatarpic" align="middle" border="0" alt="*" /></td>
</tr>
