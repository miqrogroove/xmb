   <div class="row new">
    <div class="field span new-name"><?= $lang['textnewrank'] ?>&nbsp;&nbsp;<input type="text" name="newtitle" /></div>
    <div class="field"><input type="text" name="newposts" size="5" /></div>
    <div class="field"><input type="text" name="newstars" size="4" /></div>
    <div class="field">
     <select name="newallowavatars">
      <option value="yes"><?= $lang['texton'] ?></option>
      <option value="no"><?= $lang['textoff'] ?></option>
     </select>
    </div>
    <div class="field"><input type="text" name="newavaurl" size="20" /></div>
   </div>
   <div class="row">
    <div class="field span submit"><input type="submit" name="rankssubmit" class="submit" value="<?= $lang['textsubmitchanges'] ?>" /></div>
   </div>
  </div>
 </div>
</form>
