   <div class="row">
    <div class="field span">
     <?= $lang['textnewip'] ?>
     <input type="text" name="newip1" size="3" maxlength="3" bgcolor="<?= $THEME['altbg2'] ?>" />
     .
     <input type="text" name="newip2" size="3" maxlength="3" bgcolor="<?= $THEME['altbg2'] ?>" />
     .
     <input type="text" name="newip3" size="3" maxlength="3" bgcolor="<?= $THEME['altbg2'] ?>" />
     .
     <input type="text" name="newip4" size="3" maxlength="3" bgcolor="<?= $THEME['altbg2'] ?>" />
    </div>
   </div>
  </div>
 </div>
 <br />
 <div class="smalltxt center-text">
  <?= $lang['currentip'] ?> <strong><?= $onlineip ?></strong><?= $warning ?>
  <br /><?= $lang['multipnote'] ?>
 </div>
 <br />
 <div class="submit">
  <input type="submit" class="submit" name="ipbansubmit" value="<?= $lang['textsubmitchanges']; ?>" />
  <input type="submit" class="submit" name="ipbandisable" value="<?= $lang['ipbandisable']; ?>" />
 </div>
</form>
