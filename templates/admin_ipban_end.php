<tr bgcolor="<?= $THEME['altbg2'] ?>">
<td colspan="4" class="tablerow" bgcolor="<?= $THEME['altbg2'] ?>"><?= $lang['textnewip'] ?>
<input type="text" name="newip1" size="3" maxlength="3" bgcolor="<?= $THEME['altbg2'] ?>" />.<input type="text" name="newip2" size="3" maxlength="3" bgcolor="<?= $THEME['altbg2'] ?>" />.<input type="text" name="newip3" size="3" maxlength="3" bgcolor="<?= $THEME['altbg2'] ?>" />.<input type="text" name="newip4" size="3" maxlength="3" bgcolor="<?= $THEME['altbg2'] ?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
<br />
<span class="smalltxt"><?= $lang['currentip'] ?> <strong><?= $onlineip ?></strong><?= $warning ?><br /><?= $lang['multipnote'] ?></span><br />
<br /><div align="center">
<input type="submit" class="submit" name="ipbansubmit" value="<?= $lang['textsubmitchanges']; ?>" />
<input type="submit" class="submit" name="ipbandisable" value="<?= $lang['ipbandisable']; ?>" />
</div>
</form>
</td>
</tr>
